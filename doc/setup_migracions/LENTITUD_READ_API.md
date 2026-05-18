# Per què els endpoints READ de Laravel van lents

Aquest document resumeix les **causes més probables** de lentitud en obtenir dades dels endpoints GET de l’API Laravel i què es pot fer per millorar-ho.

---

## 1. Falta d’índexs a la base de dades

La base de dades (`database/init.sql`) només defineix claus primàries i uniques. **No hi ha índexs** en columnes que s’usen per filtrar o ordenar. Això obliga PostgreSQL a fer **full table scans**, que es noten quan les taules creixen.

### Consultes afectades

| Endpoint / servei | Taula / columnes | Problema |
|-------------------|------------------|----------|
| `GET /api/habits` | `habits(usuari_id)` | `Habit::where('usuari_id', 1)` sense índex |
| `GET /api/game-state` | `usuaris(id)`, `ratxes(usuari_id)` | Consultes per usuari sense índex a `ratxes.usuari_id` |
| `GET /api/admin/dashboard` | `habits(plantilla_id)`, `registre_activitat(habit_id, acabado)` | GROUP BY i counts sense índexs |
| `GET /api/admin/rankings/{periodo}` | `habits(plantilla_id)`, `registre_activitat(habit_id, acabado, data)` | Agregacions i filtres per data sense índexs |
| `GET /api/admin/logs/...` | `admin_logs(created_at, administrador_id, accio)` | Ordenació i filtres sense índexs |
| `GET /api/admin/usuaris/...` | `usuaris(prohibit)` | Filtre `where('prohibit', true/false)` sense índex |

### Recomanació

Afegir índexs (per exemple en una migració o script SQL):

```sql
-- Hàbits per usuari (HabitController, Dashboard, Ranking)
CREATE INDEX idx_habits_usuari_id ON habits(usuari_id);
CREATE INDEX idx_habits_plantilla_id ON habits(plantilla_id);

-- Ratxes per usuari (GamificationService)
CREATE INDEX idx_ratxes_usuari_id ON ratxes(usuari_id);

-- Registre d’activitat (Dashboard, Ranking)
CREATE INDEX idx_registre_activitat_habit_acabado ON registre_activitat(habit_id, acabado);
CREATE INDEX idx_registre_activitat_data ON registre_activitat(data) WHERE acabado = true;

-- Logs d’admin (AdminLogController)
CREATE INDEX idx_admin_logs_created_at ON admin_logs(created_at DESC);
CREATE INDEX idx_admin_logs_administrador_id ON admin_logs(administrador_id);

-- Usuaris prohibit (AdminUsuariController)
CREATE INDEX idx_usuaris_prohibit ON usuaris(prohibit);
```

Després d’això, les consultes READ haurien de ser notablement més ràpides.

---

## 2. GamificationService: dues consultes per request

A `app/Services/GamificationService.php`, `obtenirEstatGamificacio()` fa:

1. `User::find($usuariId)`
2. `Ratxa::where('usuari_id', $usuariId)->first()`

Cada crida a `GET /api/game-state` fa **2 viatges a la base de dades**. Es pot reduir a **1** carregant la relació o fent un join.

### Recomanació

- Opció A: Un sol model amb relació  
  Per exemple, que el model `User` tingui `hasOne(Ratxa::class)` i fer:

  ```php
  $usuari = User::with('ratxa')->find($usuariId);
  ```

  i llegir `$usuari->ratxa` (una sola query amb eager load).

- Opció B: Una sola query amb join/select  
  Una consulta que retorni usuari + ratxa (per exemple amb `User::query()->leftJoin(...)->select(...)->first()`).

Això redueix la latència de `game-state` (menys round-trips a la BD).

---

## 3. Cerca als logs d’admin (AdminLogController)

A `AdminLogController::index`, quan es filtra per `cerca`, es fa:

```php
->orWhereRaw("abans::text ilike ?", ['%'.$cerca.'%'])
->orWhereRaw("despres::text ilike ?", ['%'.$cerca.'%']);
```

- Es converteix el camp JSONB (`abans`, `despres`) a text i es fa `ilike`. Això **no pot usar índexs** i força un full scan de les files que passen els altres filtres.
- En taules amb molts logs, aquesta part de la cerca pot ser molt lenta.

### Recomanació

- Limitar la cerca a camps indexables (per exemple `accio`, `detall`) i evitar cercar dins de `abans`/`despres` en grans volums, o
- Fer la cerca en JSONB amb operadors de PostgreSQL (per exemple `@>` o GIN) si realment cal cercar dins del JSON, i afegir un índex GIN sobre aquests camps si cal.

Així es redueix el cost de les pàgines de logs quan s’usa el filtre de cerca.

---

## 4. Dashboard: moltes consultes i agregacions

`AdminDashboardController::index()` fa:

- Diversos `count()` (usuaris, prohibits, plantilles públiques)
- Query agregada per “top 5 plantilles” (habits per plantilla)
- Query agregada per “top 5 hàbits” (completions a `registre_activitat`)
- Query dels últims 10 logs amb relació `administrador`

Tot això són **múltiples consultes** i agregacions sense índexs. En entorns amb dades grans, es nota.

### Recomanació

- Afegir els **índexs** indicats a l’apartat 1 (especialment `habits`, `registre_activitat`, `admin_logs`).
- Si el dashboard es consulta molt i les dades no han de ser en temps real, valorar **cache** (per exemple cache de 1–5 minuts per les mètriques i els tops).
- Mantenir eager load `with('administrador:id,nom')` als logs (ja ho feu) per evitar N+1.

---

## 5. Frontend: peticions en seqüència (waterfall)

A **HomePage.vue** ja s’usa `Promise.all([obtenirHabitos(), obtenirEstatJoc()])`, és a dir, hàbits i game-state es demanen **en paral·lel**. Això està bé i no és la causa de l’endarreriment dels READ.

Si en altres pantalles (per exemple admin) es criden diversos GET un darrere l’altre (primer dashboard, després logs, després usuaris, etc.), la latència total és la **suma** de totes les respostes. Allà on sigui possible, convé fer les crides en paral·lel (Promise.all o equivalent) perquè el temps percebut sigui el del request més lent, no la suma de tots.

---

## 6. Altres factors possibles

- **Xarxa**: Si el front (Nuxt) i Laravel estan en servidors diferents o lluny, cada request paga la latència de xarxa. Revisar on s’allotja l’API i si hi ha proxy/CORS que afegueixin salts.
- **PHP / servidor**: Si OPcache no està activat, cada request compila PHP. Activar OPcache en producció. Revisar també que el servidor (PHP-FPM, etc.) no estigui sobrecarregat.
- **Codi**: No s’han vist N+1 clàssics als controladors READ revisats (HabitController, GameStateController, AdminDashboardController, AdminRankingController, AdminLogController, AdminUsuariController). El principal problema detectat és **falta d’índexs** i, en segon terme, el nombre de queries a GamificationService i la cerca als logs.

---

## Resum d’accions prioritàries

1. **Afegir índexs** a les taules i columnes indicades (màxim impacte esperat en els READ).
2. **Reduir a 1 query** l’obtenció d’estat de gamificació (User + Ratxa) a `GamificationService`.
3. **Revisar la cerca** als logs d’admin (evitar o limitar `abans::text` / `despres::text` amb `ilike` en taules grans).
4. **Valorar cache** per al dashboard i, si cal, per a rankings.
5. **Assegurar crides en paral·lel** des del frontend quan es carreguin diverses dades READ a la mateixa pantalla.

Si indiqueu quin endpoint o pantalla va més lent (per exemple “el dashboard”, “game-state”, “llista d’hàbits”), es poden prioritzar canvis més concrets (per exemple només índexs per a les taules que usa aquell endpoint).

---

## 7. Flux amb Pinia: READ vs CUD i sensació de temps real

Per separar bé la lentitud dels READ de la UX, el frontend utilitza **Pinia** per mantenir l'estat i aplicar una estratègia diferent segons el tipus d'operació.

### Regla general

- **GET (llegir dades)**: sempre via `fetch` → API Laravel. El resultat es desa o es llegeix des de Pinia per mostrar-ho a la UI.
- **CUD (insert, update, delete)**: s'actualitza **primer Pinia** (optimista) perquè la UI sembli a temps real; en paral·lel es gestiona tot el backend (Node → Redis → Laravel); els sockets serveixen per feedback o confirmació i, si cal, per revertir Pinia en cas d'error.

Així la lentitud dels READ (Laravel, BD, índexs) no bloqueja la sensació d'instantaneïtat en crear, editar o eliminar.

---

### 7.1. Flux READ (GET)

1. La vista o un composable demana dades (ex.: llista d'hàbits, game-state, dashboard).
2. Es fa `fetch` a l'endpoint corresponent de l'API Laravel (ex.: `GET /api/habits`, `GET /api/game-state`).
3. Quan arriba la resposta:
   - Es desa el resultat al **store de Pinia** (ex.: `habitStore.setHabits(data)`, `gameStore.setGameState(data)`).
   - Els components que utilitzen aquest store es reactualitzen sols (reactivitat).
4. Mentre es carrega, es pot mostrar un estat de "loading" a Pinia (ex.: `habitStore.loading = true`) i amagar-lo en rebre les dades.

**Resum**: GET → fetch Laravel → actualitzar Pinia → la UI llegeix de Pinia. La lentitud ve de Laravel/BD; els índexs i optimitzacions d'aquest document afecten només aquest flux.

---

### 7.2. Flux CUD (insert, update, delete) amb Pinia optimista

Per als inserts, updates i deletes s'utilitza Pinia perquè **sembli a temps real** mentre el backend processa en segon pla.

#### Pas a pas

1. **L'usuari fa una acció** (crear hàbit, completar missió, eliminar plantilla, etc.).
2. **Actualització optimista a Pinia (immediata)**  
   Abans d'esperar el backend, s'actualitza l'estat del store:
   - **Insert**: s'afegeix l'element nou a la llista/objecte del store (amb un id temporal si cal).
   - **Update**: es modifica l'element corresponent dins del store.
   - **Delete**: es treu l'element de la llista/objecte del store.  
   La UI es repinta al moment i l'usuari veu el canvi com si fos instantani.
3. **Enviament al backend**  
   En paral·lel es dispara la lògica CUD cap al backend (Node / Redis / Laravel segons l'arquitectura):
   - Es pot enviar per socket (ex.: emit a Node) o per una crida que acabi enviant a Redis/Laravel.
   - No es bloqueja la UI esperant la resposta per mostrar el canvi.
4. **Confirmació o error per socket (o callback)**  
   Quan el backend ha processat:
   - **Èxit**: opcionalment s'actualitza Pinia amb l'id definitiu o dades retornades pel servidor (per exemple substituir l'id temporal per l'id real). Si no es retorna res, el que ja hi ha a Pinia es considera correcte.
   - **Error**: es **reverteix** el canvi a Pinia (es treu l'element afegit, es restaura l'estat anterior en update/delete) i es mostra un missatge d'error a l'usuari.

Així, els CUD no depenen de la velocitat dels READ ni de la latència de Laravel per donar sensació de temps real; la part "lenta" queda amagada darrere de l'actualització optimista de Pinia.

---

### 7.3. Resum de responsabilitats

| Operació | Origen de les dades / acció | Pinia |
|----------|-----------------------------|--------|
| **READ** | `fetch` → API Laravel → resposta | S'omple el store amb el resultat; la UI llegeix del store. La lentitud ve de l'API/BD. |
| **Insert** | Usuari crea → actualitzar store → enviar CUD al backend | Afegir a la llista/objecte al moment; en cas d'error, revertir. |
| **Update** | Usuari edita → actualitzar store → enviar CUD al backend | Modificar l'element al moment; en cas d'error, revertir. |
| **Delete** | Usuari elimina → actualitzar store → enviar CUD al backend | Treure l'element al moment; en cas d'error, revertir. |

La lògica de "semblar temps real" en els CUD és doncs: **actualitzar Pinia primer, backend en segon pla, i revertir Pinia només si el backend falla**.
