## Context

Loopy disposa d'un backend Laravel (API REST + workers via Commands), un backend Node.js (Socket.io + cues Redis) i un frontend Nuxt 3. L'arquitectura segueix la regla **GET → Laravel directe** i **CUD → Node → Redis → Laravel**. Els snapshots diaris han de capturar un punt en el temps immutable per a cada usuari, de manera que siguin consultables des del frontend sense risc de mutació posterior.

Estat actual: no existeix cap mecanisme de persistència d'historial. Les taules `HABITS`, `USUARIS_HABITS`, `REGISTRE_ACTIVITAT`, `RATXES` i `USUARIS` reflecteixen només l'estat present. **No existeix cap taula `GAME_STATE` ni camps de personalització visual de la mascota** (color de plastilina, objectes equipats); aquests camps es deixen fora de l'MVP del calendari (vegeu Open Questions).

## Goals / Non-Goals

**Goals:**
- Capturar diàriament (23:59) un snapshot de l'estat complet de cada usuari i desar-lo a `DAILY_SNAPSHOTS`.
- Exposar endpoints Laravel de lectura per consultar snapshots per data i per mes.
- Renderitzar al frontend dues vistes (mensual i diària) basades en dades immutables del snapshot.
- Permetre consultar el detall d'un hàbit passat (recompensa real + metadata d'API) via modal.
- Filtrar hàbits per categoria a la vista diària.

**Non-Goals:**
- Modificar o retroactivamente completar hàbits del passat.
- Generar snapshots per dies anteriors a la implementació (el sistema parteix de zero).
- Implementar sincronització en temps real del calendari (WebSocket); el calendari és dades fredes.
- Exportació o compartició de l'historial.

## Decisions

### D1 — Estructura del snapshot: JSON denormalitzat a la BD

**Decisió:** Desar `mascota_json`, `habits_json` i `economia_json` com a columnes `jsonb` (PostgreSQL) en lloc de taules relacionals separades.

**Raonament:** L'snapshot és una fotografia immutable. Normalitzar-lo en relacions forçaria JOINs complexos i faria difícil garantir que el passat no es modifiqui si s'esborren hàbits. El `jsonb` de PostgreSQL permet queries directes sobre els camps interns si cal, i és la solució estàndard per a dades d'auditoría.

**Alternativa descartada:** Taules `SNAPSHOT_HABITS`, `SNAPSHOT_MASCOTA`, etc. → massa overhead i risc de mutació accidental via FK.

---

### D2 — Disparador del cron: Node.js → Redis → Laravel Command

**Decisió:** El job nocturn s'encua a Redis des d'un worker Node.js (via `setInterval` o `node-cron`) i és processat pel `SnapshotCommand` de Laravel.

**Raonament:** Segueix el patró CUD ja establert al projecte. Node gestiona el temporitzador i publica a la cua `queue:snapshot`; Laravel consumeix i executa la lògica de negoci (accés a BD, construcció del JSON). Centralitza la lògica de captura on ja viuen els models i les relacions Eloquent.

**Alternativa descartada:** Cron pur de Laravel (`Kernel.php schedule`) sense passar per Redis → vàlid però trenca el patró del projecte i no és monitoritzable des de Node.

---

### D3 — Generació del snapshot: un Command per a tots els usuaris no prohibits

**Decisió:** El `SnapshotCommand` itera tots els usuaris amb `prohibit = FALSE` (camp real de la taula `USUARIS`) i genera un snapshot per cadascun en una sola execució.

**Raonament:** Evita N encuaments individuals. Si el volum d'usuaris creix molt es pot migrar a chunks amb `chunk(100)` d'Eloquent sense canviar la interfície. Excloem els usuaris prohibits per evitar registres innecessaris.

---

### D4 — Endpoints d'accés: dos endpoints RESTful de Laravel sense middleware d'auth

**Decisió:**
- `GET /api/calendar/snapshot/{usuariId}/{date}` → retorna el snapshot complet d'un dia (format `YYYY-MM-DD`).
- `GET /api/calendar/month/{usuariId}/{year}/{month}` → retorna un array lleuger per al grid mensual: `[{day, has_snapshot, category_colors[]}]`.

**Raonament:** Separar les dues consultes evita transferir el JSON complet per a 30 dies quan l'usuari obre la vista mensual. El frontend carrega el detall únicament en fer clic a un dia concret. Seguint la regla del projecte (`laravel-backend.mdc`) — "Sense autenticació; usuari i admin id 1 per defecte" — els endpoints reben `usuariId` com a paràmetre del path en lloc d'usar `auth()`.

---

### D8 — Construcció del snapshot a partir de les taules existents

**Decisió:** El `SnapshotService.captureForUser($user, $date)` construeix els tres camps JSON consultant directament les taules reals del schema:

- **`mascota_json`**: `SELECT nivell, xp_total, xp_actual_nivel, xp_objetivo_nivel FROM USUARIS WHERE id = :usuariId`
- **`habits_json`**: JOIN entre `HABITS`, `USUARIS_HABITS` (`actiu = TRUE`) i `LEFT JOIN REGISTRE_ACTIVITAT` filtrat per `DATE(data) = :data`. Per a cada hàbit, `acabado` és `ra.acabado ?? false`.
- **`economia_json`**: La XP es treu de `SUM(REGISTRE_ACTIVITAT.xp_guanyada)` del dia. Les monedes es calculen reutilitzant constants compartides de gamificació (`App\Support\GamificationConstants`) per evitar duplicació entre serveis (fàcil 100/2, mitjà 250/5, difícil 400/10).

**Raonament:** Aprofita les fonts de dades existents sense canvis al schema (excepte `CATEGORIES.color`) i evita deriva funcional entre `HabitService` i `SnapshotService` centralitzant la regla de recompenses en una sola font de veritat (`GamificationConstants`).

---

### D9 — Color als indicadors mensuals: afegir `color` a `CATEGORIES`

**Decisió:** Afegir un camp `color VARCHAR(20)` a la taula `CATEGORIES` (via `init.sql`) i seedar valors per defecte a `insert.sql`. L'endpoint mensual retorna els colors únics de les categories dels hàbits completats aquell dia.

**Raonament:** El spec original parla de "categories dels hàbits completats", no de "colors d'hàbits individuals". Tenir el color a `CATEGORIES` és semànticament correcte i centralitza el sistema de colors del producte.

**Alternativa descartada:** Reutilitzar `HABITS.color` directament → els indicadors representarien hàbits, no categories, i pot haver-hi colisions cromàtiques.

---

### D5 — Frontend: dues pàgines Nuxt + un store Pinia centralitzat

**Decisió:** `pages/calendar/index.vue` (mensual) i `pages/calendar/day.vue` (diària). Un únic store `useCalendarStore` gestiona: mes/dia seleccionat, cache de snapshots carregats, filtre de categoria actiu.

**Raonament:** El store centralitza la navegació temporal i evita re-fetches quan l'usuari va i torna entre vistes. La cache de snapshots (keyed per `YYYY-MM-DD`) és un Map simple; no necessita persistència entre sessions.

---

### D6 — Vista diària: reutilitzar components existents de la Home en mode read-only

**Decisió:** El component de mascota i les `HabitCard` existents acceptaran una prop `readonly: true` i `snapshotData` per renderitzar dades del passat sense lògica d'interacció.

**Raonament:** Evita duplicar components. La prop `readonly` desactiva botons i emissió d'events. Si la Home canvia de disseny en el futur, el calendari hereta els canvis visualment.

**Alternativa descartada:** Components nous dedicats al calendari → més manteniment i risc de desincronització visual.

---

### D10 — Etiqueta temporal relativa al header diari

**Decisió:** El títol de `pages/calendar/day.vue` mostrarà una etiqueta relativa i localitzada: "Avui/Hoy/Today" per a la data actual, "Ahir/Ayer/Yesterday" per al dia anterior, i `DiaSetmana DD/MM` per a dies més antics.

**Raonament:** Redueix càrrega cognitiva i millora orientació temporal en navegar per dies consecutius amb gestos. Manté consistència entre idiomes i evita headers llargs amb nom de mes complet quan el context és de consulta ràpida.

---

### D7 — Modal de detalls: SweetAlert2 (ja present)

**Decisió:** Reutilitzar SweetAlert2 per al modal `HabitHistoryDetailModal` amb contingut HTML custom.

**Raonament:** SweetAlert2 ja és dependència del projecte. Evita afegir una nova llibreria de modals.

## Risks / Trade-offs

- **[Risc] El cron no s'executa (Node down a les 23:59)** → Mitigació: el `SnapshotCommand` pot executar-se manualment per un dia concret (`php artisan snapshot:run --date=YYYY-MM-DD`) per recuperar dies perduts. Considerar afegir un log d'execucions.

- **[Risc] Snapshots voluminosos si l'usuari té molts hàbits i objectes equipats** → Mitigació: el `jsonb` de PostgreSQL comprimeix bé. Si el volum és un problema, es pot limitar la retenció a 365 dies i arxivar la resta.

- **[Risc] Race condition: l'usuari modifica l'outfit a les 23:58** → Mitigació: el snapshot es genera a les 23:59 i captura l'últim estat conegut. Comportament documentat i acceptat per disseny.

- **[Trade-off] Cache en memòria del store Pinia vs re-fetch** → La cache de snapshots no es persisteix entre sessions. Si l'usuari torna al calendari dies després, es re-fetchen. Acceptable per MVP; es pot afegir `localStorage` en iteracions futures.

- **[Risc] Pàgines buides per dates anteriors a la implementació** → Mitigació: si `has_snapshot = false`, la cel·la del dia es renderitza buida (sense error) i la vista diària mostra un missatge "Encara no hi havia dades aquest dia".

## Migration Plan

1. **Schema (`database/init.sql`)**:
   - Afegir la taula `DAILY_SNAPSHOTS (id SERIAL PK, usuari_id FK USUARIS, data DATE, mascota_json JSONB, habits_json JSONB, economia_json JSONB, created_at TIMESTAMP, UNIQUE(usuari_id, data))`.
   - Afegir el camp `color VARCHAR(20)` a `CATEGORIES`.
   - Afegir el camp `metadata JSONB` a `HABITS` (actualment només existeix al model Eloquent, no al SQL).
2. **Seeds (`database/insert.sql`)**: assignar un `color` per defecte a cada categoria existent.
3. **Backend Laravel**: crear model `DailySnapshot`, `SnapshotService`, `SnapshotRunCommand` (`snapshot:run`), `CalendarController` i registrar les rutes a `routes/api.php` (sense middleware d'auth).
4. **Backend Node.js**: afegir scheduler que publica a `queue:snapshot` a les 23:59 i subscriber que executa `php artisan snapshot:run`.
5. **Verificació manual**: executar `php artisan snapshot:run` i comprovar registres a `DAILY_SNAPSHOTS`.
6. **Frontend Nuxt**: desplegar pàgines, components i store.
7. **Icona Home**: afegir-la com a últim pas (punt d'entrada visible per l'usuari).

**Rollback:** Si cal revertir, eliminar la icona de la Home i desactivar el subscriber Node. La taula `DAILY_SNAPSHOTS` pot quedar inert sense afectar la resta del sistema.

## Open Questions

- **Personalització visual de la mascota (`GAME_STATE`)**: A la spec original es parlava de capturar `color_plastilina` i `objectes_equipats[]`. Aquests camps **no existeixen al schema actual**. Proposta MVP: ometre'ls del snapshot i deixar-ho com a iteració futura quan es defineixi la taula `GAME_STATE` o equivalent.
- **Retenció de dades**: Quants dies/mesos de snapshots volem conservar? (Ara: il·limitat. Proposta: últims 365 dies per MVP.)
- **Estat emocional de la mascota**: El llindar de "feliç / trist" a la vista diària: és % de completació ≥ 50%? Cal confirmar el valor exacte.
- **Auth dels endpoints**: La regla del projecte indica que no hi ha auth (id 1 per defecte). Si en el futur s'introdueix JWT, caldrà afegir middleware `auth:api` i derivar `usuariId` del token en lloc del path.
