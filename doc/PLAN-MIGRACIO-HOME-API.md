# Plans de migració Loopy

Aquest document conté dos plans:
1. **Home → API Laravel** (executat)
2. **Consolidació Redis Workers** (pendent)

---

# PART 1: Plan de migració Home → API Laravel (Read)

## Objectiu

Fer que la pantalla **home** de l'usuari carregui totes les dades (missió diària, hàbits, nivell, XP, monedes, logros, progrés) **exclusivament via endpoints de l'API Laravel**, eliminant qualsevol dependència de sockets per a la càrrega inicial.

---

## Decisió adoptada

**Un sol endpoint consolidat** (`GET /api/user/home`):

- **Centralitzar la lògica**: totes les dades de la home en un únic punt d'accés i un únic servei al backend.
- **Protecció** `ensure.user`: la ruta estarà dins del grup middleware que verifica JWT amb role=user i injecta `user_id` al request.
- Una sola petició al frontend en lloc de quatre, millorant rendiment i manteniment.

---

## Estat actual

### Càrrega actual de la home (frontend/pages/home.vue)

En el `mounted` es fan **4 peticions API en paral·lel**:

| # | Mètode Store | Endpoint Laravel | Dades obtingudes |
|---|--------------|------------------|------------------|
| 1 | `gameStore.obtenirHabitos()` | `GET /api/habits` | Llista d'hàbits del dia |
| 2 | `gameStore.obtenirProgresHabits()` | `GET /api/habits/progress` | Progrés diari per hàbit |
| 3 | `gameStore.obtenirEstatJoc()` | `GET /api/game-state` | XP, nivell, ratxa, monedes, missió diària, ruleta |
| 4 | `logroStore.carregarLogros()` | `GET /api/logros` | Llista de logros |

Tots els endpoints estan protegits amb el middleware `ensure.user` (JWT amb role=user).

### Sockets (ús actual)

Els sockets **NO** s'utilitzen per a la càrrega inicial. Només per:

- Actualitzacions en temps real: `update_xp`, `habit_action_confirmed`, `streak_broken`, `level_up`, `roulette_result`, `mission_completed`
- Accions CUD: `habit_progress`, `habit_complete`, `roulette_spin` (emit del frontend → Node → Redis → Laravel)

---

## Solució adoptada: un sol endpoint consolidat

Crear **1 sol endpoint** `GET /api/user/home` que retorni tot el necessari per a la home (centralització + `ensure.user`).

### Ruta proposada

```
GET /api/user/home
```

Middleware: `ensure.user` (mateix que la resta de rutes usuari).

---

## Pas a pas detallat

### FASE 1: Backend Laravel

#### 1.1 Crear el servei `HomeDataService`

**Fitxer nou:** `backend-laravel/app/Services/HomeDataService.php`

**Responsabilitat:**
- Agrupar la lògica de lectura de:
  - GamificationService (estat del joc: XP, nivell, ratxa, monedes, missió, ruleta)
  - HabitController / HabitService (hàbits del dia)
  - HabitProgressController (progrés diari)
  - LogroService (logros)
- Retornar un array associatiu amb totes les dades.

**Estructura del mètode principal:**
```php
public function obtenirDadesHome(int $usuariId): array
{
    // A. Estat de gamificació (GamificationService)
    // B. Hàbits del dia (Habit + UsuariHabit)
    // C. Progrés diari (RegistreActivitat)
    // D. Logros (LogroService)
    // E. Retornar array consolidat
}
```

**Format de sortida proposat:**
```json
{
  "game_state": {
    "xp_total": 0,
    "nivell": 1,
    "xp_actual_nivel": 0,
    "xp_objetivo_nivel": 1000,
    "ratxa_actual": 0,
    "ratxa_maxima": 0,
    "monedes": 0,
    "can_spin_roulette": true,
    "ruleta_ultima_tirada": null,
    "missio_diaria": { "id": 1, "titol": "..." },
    "missio_completada": false
  },
  "habits": [ /* array HabitResource */ ],
  "habit_progress": [ /* array { habit_id, progress, completed_today, ... } */ ],
  "logros": [ /* array LogroResource */ ]
}
```

---

#### 1.2 Crear el controlador `UserHomeController`

**Fitxer nou:** `backend-laravel/app/Http/Controllers/Api/UserHomeController.php`

**Contingut:**
- Namespace: `App\Http\Controllers\Api`
- Constructor: injectar `HomeDataService`
- Mètode `index(Request $request): JsonResponse`
  - Obtenir `$usuariId = $request->user_id` (injectat pel middleware)
  - Si no hi ha `user_id`, retornar 401
  - Cridar `$this->homeDataService->obtenirDadesHome($usuariId)`
  - Retornar `response()->json($dades, 200)`

---

#### 1.3 Registrar la ruta

**Fitxer a modificar:** `backend-laravel/routes/api.php`

**Canvi:** Dins del `Route::middleware('ensure.user')->group(function () {`, afegir:

```php
Route::get('/user/home', [UserHomeController::class, 'index']);
```

I afegir el `use` al capdamunt:

```php
use App\Http\Controllers\Api\UserHomeController;
```

**Verificació:** La ruta ha de quedar dins del grup `ensure.user` juntament amb `/habits`, `/game-state`, `/logros`, etc.

---

### FASE 2: Frontend

#### 2.1 Crear mètode `carregarDadesHome` al gameStore

**Fitxer a modificar:** `frontend/stores/gameStore.js`

**Canvis:**
1. Afegir nova action `carregarDadesHome: async function ()`
   - URL: `construirUrlApi("/api/user/home")`
   - Petició: `authFetch(url, { mode: "cors" })`
   - Si `!resposta.ok`, throw Error
   - Parsejar JSON
   - Assignar:
     - `self.xpTotal`, `self.nivell`, `self.xpActualNivel`, `self.xpObjetivoNivel` des de `dades.game_state`
     - `self.ratxa`, `self.ratxaMaxima`, `self.monedes` des de `dades.game_state`
     - `self.canSpinRoulette`, `self.ruletaUltimaTirada` des de `dades.game_state`
     - `self.missioDiaria`, `self.missioCompletada` des de `dades.game_state`
     - `self.habits` (mapejar amb la lògica existent de `obtenirHabitos`)
     - `self.habitProgress` (convertir array a mapa { habit_id: { progress, completed_today } })
   - Retornar dades

2. **Opcional (mantenir compatibilitat):** Conservar `obtenirEstatJoc`, `obtenirHabitos`, `obtenirProgresHabits` per a ús puntual (per exemple, quan arriba `update_xp` per socket i només cal refrescar estat).

---

#### 2.2 Actualitzar useLogroStore per acceptar dades externes

**Fitxer a modificar:** `frontend/stores/useLogroStore.js`

**Canvi:** Afegir action `setLogros: function (logrosArray)` que assigni `self.logros = logrosArray || []`. Això permet que el gameStore (o la home) pugui assignar els logros directament quan es carreguen des del endpoint consolidat, sense fer una segona petició.

---

#### 2.3 Modificar home.vue (mounted)

**Fitxer a modificar:** `frontend/pages/home.vue`

**Canvi al mètode `mounted`:**

**ABANS:**
```javascript
Promise.all([
  self.gameStore.obtenirHabitos(),
  self.gameStore.obtenirProgresHabits(),
  self.gameStore.obtenirEstatJoc(),
  self.logroStore.carregarLogros()
])
```

**DESPRÉS:**
```javascript
self.gameStore.carregarDadesHome()
  .then(function (dades) {
    if (dades && dades.logros && dades.logros.length >= 0) {
      self.logroStore.setLogros(dades.logros);
    }
    console.log("✅ Dades home carregades correctament");
  })
  .catch(function (error) {
    console.error("❌ Error carregant home:", error);
    self.errorMissatge = "Error al carregar la informació del servidor.";
  })
  .finally(function () {
    self.estaCarregantHabits = false;
  });
```

**Nota:** Si `carregarDadesHome` ja actualitza el `logroStore` internament (des del gameStore), no cal cridar `setLogros` des de la home. Això depèn de com es dissenyi: el gameStore pot rebre el logroStore i cridar `logroStore.setLogros(dades.logros)` dins de `carregarDadesHome`.

---

#### 2.4 Alternativa: gameStore actualitza logroStore

Si es vol mantenir el gameStore independent del logroStore, la home ha de fer:

```javascript
self.gameStore.carregarDadesHome()
  .then(function (dades) {
    if (dades && Array.isArray(dades.logros)) {
      self.logroStore.logros = dades.logros;
    }
    console.log("✅ Dades home carregades correctament");
  })
  ...
```

O millor: que `carregarDadesHome` retorni les dades i la home assigni `logroStore.logros = dades.logros`.

---

### FASE 3: Eliminar / simplificar (opcional)

#### 3.1 Mantenir endpoints individuals

Els endpoints `/api/habits`, `/api/habits/progress`, `/api/game-state`, `/api/logros` es mantenen per:
- Pàgines que només necessiten un subset (ex: `/habits` només hàbits)
- Refrescos parcials (ex: després de `update_xp`, cridar `obtenirEstatJoc()` si no s'inclou l'estat complet al payload del socket)

#### 3.2 Socket `update_xp`

Actualment, quan arriba `update_xp`, la home fa:

```javascript
await self.gameStore.obtenirEstatJoc();
```

Es pot mantenir aquesta lògica (refrescar via API) o, en un futur, fer que Laravel inclogui l'estat actualitzat dins del payload de feedback i evitar la segona petició.

---

## Resum de fitxers (solució consolidada)

| Acció | Fitxer |
|-------|--------|
| **Crear** | `backend-laravel/app/Services/HomeDataService.php` |
| **Crear** | `backend-laravel/app/Http/Controllers/Api/UserHomeController.php` |
| **Modificar** | `backend-laravel/routes/api.php` (afegir ruta + use, dins `ensure.user`) |
| **Modificar** | `frontend/stores/gameStore.js` (afegir `carregarDadesHome`) |
| **Modificar** | `frontend/stores/useLogroStore.js` (afegir `setLogros` o similar) |
| **Modificar** | `frontend/pages/home.vue` (mounted: una sola crida a `carregarDadesHome`) |

---

## Comprovacions finals

1. **Autenticació:** La ruta `/api/user/home` requereix JWT amb `role=user`. El middleware `ensure.user` (`EnsureUserToken`) injecta `user_id` al request.
2. **authFetch:** El frontend fa servir `authFetch` que envia el token (headers) i `credentials: 'include'` per cookies. Verificar que el backend accepti el token (Bearer o cookie segons configució).
3. **404/401:** Si el token és invàlid o caducat, el middleware retorna 401. El frontend ha de redirigir a login.
4. **Layout:** La pàgina home ha d'estar dins d'un layout que requereixi usuari autenticat (ex: middleware de pàgina o layout `default` amb verificació).

---

## Ordre d'execució recomanat

1. Crear `HomeDataService.php` i implementar `obtenirDadesHome`.
2. Crear `UserHomeController.php` i registrar la ruta.
3. Provar l'endpoint amb Postman/curl (token JWT d'usuari).
4. Implementar `carregarDadesHome` al gameStore.
5. Afegir `setLogros` al logroStore.
6. Modificar `home.vue` per utilitzar `carregarDadesHome`.
7. Provar el flux complet a la home.
8. Eliminar o comentar les crides antigues si tot funciona correctament.

---

---

# PART 2: Plan de consolidació Redis Workers (1 sol worker)

## Objectiu

Unificar els **4 workers Redis** actuals (habits, plantilla, admin, ruleta) en **1 sol worker** que escolti totes les cues i enviï cada missatge al servei corresponent. Això simplifica el desplegament, redueix processos i centralitza la lògica.

---

## Estat actual

### Workers Redis existents (Laravel)

| # | Comandament | Cua Redis | Servei | Descripció |
|---|-------------|-----------|--------|------------|
| 1 | `habits:redis-worker` | `habits_queue` | HabitService::processarAccioHabit() | CRUD i progrés d'hàbits |
| 2 | `plantilla:redis-worker` | `plantilles_queue` | PlantillaService::processarAccioPlantilla() | CRUD de plantilles |
| 3 | `admin:redis-worker` | `admin_queue` | AdminActionService::processarAccio() | Accions CUD d'admin |
| 4 | `roulette:redis-worker` | `roulette_queue` | RouletteService::processarTirada() | Tirades ruleta diària |

### Cues i productors (Node.js)

| Cua | Fitxer Node | Mètode | Quan s'envia |
|-----|-------------|--------|--------------|
| `habits_queue` | habitQueue.js | pushToLaravel(action, userId, data) | habit_action, habit_progress, habit_complete |
| `plantilles_queue` | plantillaQueue.js | pushToLaravel(action, userId, data) | plantilla_action |
| `admin_queue` | adminQueue.js | pushToLaravel(action, adminId, entityType, data) | admin_action |
| `roulette_queue` | rouletteQueue.js | enviarALaravel(usuariId, data) | roulette_spin |

### Estructura actual Docker

```
backend-laravel-worker         → php artisan habits:redis-worker
backend-laravel-admin-worker   → php artisan admin:redis-worker
backend-laravel-plantilla-worker → php artisan plantilla:redis-worker
backend-laravel-roulette-worker  → php artisan roulette:redis-worker
```

---

## Estratègia: BRPOP multillista

Redis permet `BRPOP key1 key2 key3 ... timeout`: bloqueja fins que **qualsevol** de les cues tingui dades. Retorna `[nom_cua, valor]`. D'aquesta manera:

- **No cal modificar Node.js**: les cues es mantenen (`habits_queue`, `plantilles_queue`, etc.).
- **1 sol worker** fa BRPOP sobre totes les cues.
- Segons `nom_cua`, es crida el servei corresponent.

---

## Pas a pas detallat

### FASE 1: Crear el worker unificat

#### 1.1 Crear `UnifiedRedisWorker.php`

**Fitxer nou:** `backend-laravel/app/Console/Commands/UnifiedRedisWorker.php`

**Contingut estructural:**

```php
<?php

namespace App\Console\Commands;

use App\Services\AdminActionService;
use App\Services\HabitService;
use App\Services\PlantillaService;
use App\Services\RouletteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class UnifiedRedisWorker extends Command
{
    protected $signature = 'redis:unified-worker';
    protected $description = 'Worker únic que processa totes les cues Redis (habits, plantilles, admin, ruleta)';

    private const CUES = ['habits_queue', 'plantilles_queue', 'admin_queue', 'roulette_queue'];
    private const TIMEOUT_BRPOP = 30;

    public function __construct(
        private HabitService $habitService,
        private PlantillaService $plantillaService,
        private AdminActionService $adminActionService,
        private RouletteService $rouletteService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Unified Redis Worker iniciat. Escoltant: ' . implode(', ', self::CUES));

        while (true) {
            try {
                // BRPOP amb múltiples cues: retorna [nom_cua, valor] quan n'hi ha una amb dades
                $resultat = Redis::command('brpop', array_merge(self::CUES, [self::TIMEOUT_BRPOP]));
            } catch (\Throwable $e) {
                Log::warning('UnifiedRedisWorker: error Redis', ['error' => $e->getMessage()]);
                sleep(2);
                continue;
            }

            if (empty($resultat) || !is_array($resultat)) {
                continue;
            }

            $nomCua = $resultat[0] ?? null;
            $missatge = $resultat[1] ?? null;

            if ($nomCua === null || $missatge === null) {
                continue;
            }

            $dades = json_decode($missatge, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($dades)) {
                Log::warning('UnifiedRedisWorker: JSON invàlid', ['cua' => $nomCua, 'raw' => $missatge]);
                continue;
            }

            try {
                $this->despatxarSegonsCua($nomCua, $dades);
            } catch (\Throwable $e) {
                Log::error('UnifiedRedisWorker: error processant', [
                    'cua' => $nomCua,
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }

    private function despatxarSegonsCua(string $nomCua, array $dades): void
    {
        if ($nomCua === 'habits_queue') {
            $this->habitService->processarAccioHabit($dades);
        } elseif ($nomCua === 'plantilles_queue') {
            $this->plantillaService->processarAccioPlantilla($dades);
        } elseif ($nomCua === 'admin_queue') {
            $this->adminActionService->processarAccio($dades);
        } elseif ($nomCua === 'roulette_queue') {
            $this->rouletteService->processarTirada($dades);
        } else {
            Log::warning('UnifiedRedisWorker: cua desconeguda', ['cua' => $nomCua]);
        }
    }
}
```

**Regles de codi:**
- Seguir convencions Laravel (camelCase, català, sense ternaris).
- Estructura: `//================================ IMPORTS`, `//================================ PROPIETATS`, `//================================ MÈTODES`.

---

#### 1.2 Registrar el comandament (opcional)

Laravel descobreix automàticament els commands a `app/Console/Commands/`. No cal registrar-lo a `Kernel` si la signatura és correcta.

---

### FASE 2: Actualitzar Docker

#### 2.1 Modificar `docker/docker-compose.yml`

**Eliminar** els 4 serveis de workers:
- `backend-laravel-worker`
- `backend-laravel-admin-worker`
- `backend-laravel-plantilla-worker`
- `backend-laravel-roulette-worker`

**Crear** 1 servei únic:

```yaml
  backend-laravel-redis-worker:
    build:
      context: ..
      dockerfile: docker/Dockerfile.laravel
    container_name: loopy-laravel-redis-worker
    restart: unless-stopped
    working_dir: /var/www
    entrypoint: []
    environment:
      APP_NAME: Loopy
      APP_ENV: local
      APP_DEBUG: "true"
      APP_KEY: ${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAA==}
      APP_URL: http://localhost:8000
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: ${POSTGRES_DB:-loopy_db}
      DB_USERNAME: ${POSTGRES_USER:-loopy}
      DB_PASSWORD: ${POSTGRES_PASSWORD:-loopy_secret}
      REDIS_HOST: redis
      REDIS_PORT: 6379
      REDIS_CLIENT: predis
      REDIS_READ_TIMEOUT: 60
      REDIS_READ_WRITE_TIMEOUT: 60
      REDIS_PREFIX: ""
      JWT_SECRET: ${JWT_SECRET:-...}
    volumes:
      - ../backend-laravel:/var/www
      - ../database:/var/database:ro
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    command: [ "php", "artisan", "redis:unified-worker" ]
```

**Fitxers a modificar:** `docker/docker-compose.yml`

**Pas concret:**
1. Reemplaçar els 4 blocs de workers per aquest 1 bloc.
2. Verificar que `depends_on` inclou `postgres` i `redis`.

---

### FASE 3: Eliminar o deprecar workers antics

#### 3.1 Opció A: Eliminar fitxers (recomanat després de provar)

| Fitxer a eliminar |
|-------------------|
| `backend-laravel/app/Console/Commands/RedisWorker.php` |
| `backend-laravel/app/Console/Commands/PlantillaRedisWorker.php` |
| `backend-laravel/app/Console/Commands/AdminRedisWorker.php` |
| `backend-laravel/app/Console/Commands/RouletteRedisWorker.php` |

#### 3.2 Opció B: Mantenir temporalment (durant migració)

- Afegir `@deprecated` als commands antics.
- Deixar-los de cridar des de Docker.
- Eliminar-los quan tot funcioni correctament.

---

### FASE 4: Documentació i referències

#### 4.1 Fitxers a actualitzar

| Fitxer | Canvi |
|--------|-------|
| `doc/01-SETUP-DESDE-CERO.md` | Canviar `habits:redis-worker` per `redis:unified-worker` |
| `doc/02-ESTRUCTURA-PROYECTO.md` | Actualitzar descripció del worker Redis |
| `README.md` | Actualitzar comanda del worker |
| `backend-laravel/routes/console.php` | Comentari: `// redis:unified-worker (en un procés apart)` |

#### 4.2 Regles Cursor

| Fitxer | Canvi |
|--------|-------|
| `.cursor/rules/redis-bridge.mdc` | Esmentar `redis:unified-worker` i cues múltiples |
| `.cursor/rules/laravel-backend.mdc` | Actualitzar referència a `UnifiedRedisWorker` |

---

### FASE 5: Node.js (cap canvi)

**No cal modificar** cap fitxer de `backend-node/`:
- `habitQueue.js` → LPUSH `habits_queue` (igual)
- `plantillaQueue.js` → LPUSH `plantilles_queue` (igual)
- `adminQueue.js` → LPUSH `admin_queue` (igual)
- `rouletteQueue.js` → LPUSH `roulette_queue` (igual)

---

## Resum de fitxers (consolidació Redis)

| Acció | Fitxer |
|-------|--------|
| **Crear** | `backend-laravel/app/Console/Commands/UnifiedRedisWorker.php` |
| **Modificar** | `docker/docker-compose.yml` (1 worker en lloc de 4) |
| **Eliminar** (post-prova) | `RedisWorker.php`, `PlantillaRedisWorker.php`, `AdminRedisWorker.php`, `RouletteRedisWorker.php` |
| **Modificar** | `doc/01-SETUP-DESDE-CERO.md` |
| **Modificar** | `doc/02-ESTRUCTURA-PROYECTO.md` |
| **Modificar** | `README.md` |
| **Modificar** | `backend-laravel/routes/console.php` |

---

## Diagrama de flux (després)

```
Node (Socket)                    Redis                           Laravel
─────────────────────────────────────────────────────────────────────────────
habitQueue     ──LPUSH──►  habits_queue     ─┐
plantillaQueue ──LPUSH──►  plantilles_queue ─┼── BRPOP (1 worker) ──► UnifiedRedisWorker
adminQueue     ──LPUSH──►  admin_queue      ─┤       │
rouletteQueue  ──LPUSH──►  roulette_queue   ─┘       │
                                                     ├──► HabitService
                                                     ├──► PlantillaService
                                                     ├──► AdminActionService
                                                     └──► RouletteService
                                                              │
                                                              └──► Redis::publish(feedback_channel)
```

---

## Ordre d'execució recomanat

1. Crear `UnifiedRedisWorker.php` i provar localment: `php artisan redis:unified-worker`.
2. Aturar els 4 workers antics (si s'executen en terminals o Docker).
3. Modificar `docker-compose.yml`: 1 sol servei `backend-laravel-redis-worker`.
4. Fer `docker compose up -d` i comprovar que el worker unificat arrenca.
5. Provar fluxos: hàbit, plantilla, admin, ruleta.
6. Actualitzar documentació i regles.
7. Eliminar (o marcar deprecats) els 4 workers antics.

---

## Comprovacions finals

1. **BRPOP multillista:** Redis BRPOP amb diverses keys retorna `[key, value]` quan qualsevol té dades.
2. **Ordre de les cues:** L'ordre a BRPOP defineix prioritat. Es pot ajustar si cal (ex: admin abans que habits).
3. **Feedback:** Tots els serveis publiquen a `feedback_channel` via `RedisFeedbackService`; Node SUBSCRIBE segueix igual.
