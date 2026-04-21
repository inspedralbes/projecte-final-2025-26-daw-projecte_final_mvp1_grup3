# Loopy: El Teu Habit Loop Gamificat - Informació del Projecte

## 1. Visión General y Propósito

### 1.1 Descripción de la Aplicación

**Loopy** és una aplicació de seguiment d'hàbits revolucionària dissenyada per transformar la teva rutina diària en una aventura captivadora. Basada en el concepte científic del **'Habit Loop'** (Senyal, Rutina, Recompensa), Loopy utilitza una estètica **'soft design'** (efecte plastilina/claymorphism) per oferir una experiència visual relaxant i tàctil. L'aplicació compta amb una **mascota interactiva** que evoluciona amb tu, convertint la disciplina en un joc on el creixement personal és tangible.

### 1.2 Propuesta de Valor

1. **🤖 Onboarding intel·ligent amb IA (Gemini):** No comencis de zero. La nostra IA analitza el teu estil de vida i objectius per suggerir-te un 'Habit Loop' personalitzat des del primer minut.

2. **🐾 Feedback Emocional (La teva Mascota):** La teva mascota reacciona en temps real als teus progressos. Si compleixes els teus hàbits, estarà feliç i plena d'energia; si no, necessitarà la teva atenció.

3. **💎 Recompensa Immediata (XP i Monedes):** Cada acció suma. Guanya experiència (XP) per pujar de nivell i monedes per personalitzar l'entorn de la teva mascota o desbloquejar objectes exclusius.

### 1.3 Targetas

- **Usuaris finals:** Persones que vulguin millorar els seus hàbits diaris amb una experiència gamificada.
- **Administradors:** Personal encarregat de gestionar la plataforma, crear continguts i monitoritzar l'activitat dels usuaris.

### 1.4 Problema que Resuelve

- Falta de motivació per mantenir Rutines d'hàbits
- Dificultat per visualitzar el progrés personal
- Absència de feedback immediat en Rutines tradicionals
- Experiència d'usuari tradicionalment tediosa en apps de seguiment d'hàbits

---

## 2. Stack Tecnológico

### 2.1 Visió General de Components

| Component | Tecnología | Versió |
| :------------- | :----- | :----- |
| **Frontend** | Nuxt 3 (SPA) | 3.10.3 |
| **Backend Core** | Laravel 11 (PHP) | 8.3 |
| **Real-time & IA** | Node.js + Socket.io | 20.11.1 / 4.7.4 |
| **Base de Dades** | PostgreSQL | 16 |
| **Cache & Pub/Sub** | Redis | 7.2 |
| **IA Generativa** | Google Gemini API | - |

### 2.2 Frontend (Nuxt 3)

**Dependències principals:**

- `nuxt`: 3.10.3
- `vue`: 3.4.0
- `vue-router`: 4.2.0
- `pinia`: 2.1.7 (gestió d'estat)
- `@pinia/nuxt`: 0.5.4
- `socket.io-client`: 4.7.4
- `sweetalert2`: 11.10.0 (modals)
- `@nuxtjs/i18n`: 8.3.1 (internacionalització)

**Desenvolupament:**

- `tailwindcss`: 3.4.1
- `autoprefixer`: 10.4.17
- `postcss`: 8.4.32
- `@playwright/test`: 1.58.2 (testing)

**Configuració principal (`nuxt.config.ts`):**

- Mòduls: `@pinia/nuxt`, `@nuxtjs/i18n`
- Idiomes suportats: Català (ca), Espanyol (es), Anglès (en)
- CSS: `~/assets/css/main.css`
- Servidor: `0.0.0.0:3000`
- Variables d'entorn:
  - `SOCKET_URL`: http://localhost:3001
  - `API_URL`: http://localhost:8000

### 2.3 Backend Core (Laravel 11)

**Característiques:**

- PHP 8.3
- Laravel 11.x
- Laravel Sanctum (autenticació)
- API RESTful com a principal

**Estructura de routes:**

- `routes/api/auth.php` - Autenticació pública
- `routes/api/user.php` - Rutes d'usuari (middleware: ensure.user)
- `routes/api/admin.php` - Rutes d'administrador (middleware: ensure.admin)

**Middleware personalitzats:**

- `EnsureUserToken` - Valida el token JWT d'usuari
- `EnsureAdminToken` - Valida el token JWT d'administrador

### 2.4 Backend Real-time (Node.js + Socket.io)

**Dependències:**

- `socket.io`: 4.7.4
- `@google/generative-ai`: 0.21.0 (IA)
- `redis`: 4.6.0
- `jsonwebtoken`: 9.0.2

**Servidor:** Port 3001

### 2.5 Base de Dades

**PostgreSQL 16:**

- Localhost: 5432
- Usuari i base de dades segons fitxer `.env`

**Scripts d'inicialització:**

- `database/init.sql` - Estructura de taules
- `database/insert.sql` - Dades inicials

### 2.6 Redis 7

- Localhost: 6379
- Ús: Cache, Pub/Sub i cues de missatges

### 2.7 Docker

**Serveis:**

- Frontend (Nuxt): http://localhost:3000
- Backend Node (Socket.io): http://localhost:3001
- Backend Laravel: http://localhost:8000
- PostgreSQL: localhost:5432
- Redis: localhost:6379

---

## 3. Arquitectura del Sistema

### 3.1 Patró de Disseny

**Backend Laravel:** MVC (Model-View-Controller) amb API REST

- **Models:** `app/Models/` - Eloquent ORM
- Controllers: `app/Http/Controllers/Api/` i `app/Http/Controllers/Api/Admin/`
- Resources: `app/Http/Resources/` - Transformació de respostes
- Services: `app/Services/` - Lògica de negoci
- Middleware: `app/Http/Middleware/`

**Backend Node:** Handler-based amb Socket.io

- **Handlers:** `src/handlers/` - Gestió d'esdeveniments
- **Queues:** `src/queues/` - Cues Redis
- **Subscribers:** `src/subscribers/` - Subscripcions Redis
- **Middleware:** `src/middleware/jwtAuth.js`

**Frontend:** Nuxt 3 amb Pinia

- **Pages:** `pages/` - Vistes
- **Composables:** `composables/` - Lògica reutilitzable
- **Layouts:** `layouts/` - Plantilles de pàgina
- **Assets:** `assets/css/` - Estils

### 3.2 Estructura de Carpetes

```
/
├── frontend/
│   ├── pages/
│   │   ├── index.vue (Home pública)
│   │   ├── home.vue (Home usuari)
│   │   ├── perfil.vue (Perfil usuari)
│   │   ├── habits.vue (Gestió hàbits)
│   │   ├── Plantilles.vue (Plantilles públiques)
│   │   ├── Login.vue / Registre.vue (Auth llegat)
│   │   ├── auth/
│   │   │   ├── login.vue
│   │   │   └── registre.vue
│   │   ├── admin/
│   │   │   ├── index.vue (Dashboard admin)
│   │   │   ├── perfil.vue
│   │   │   ├── configuracio.vue
│   │   │   ├── usuaris.vue
│   │   │   ├── habits.vue
│   │   │   ├── plantilles.vue
│   │   │   ├── missions.vue
│   │   │   ├── logros.vue
│   │   │   └── notificacions.vue
│   │   └── error/
│   │       ├── 403.vue
│   │       ├── 404.vue
│   │       └── 500.vue
│   ├── layouts/
│   │   ├── default.vue
│   │   └── admin.vue
│   ├── composables/
│   │   ├── user/
│   │   │   ├── useHabits.js
│   │   │   ├── usePlantilles.js
│   │   │   ├── useLogros.js
│   │   │   └── useGameState.js
│   │   ├── admin/
│   │   │   ├── useAdminApi.js
│   │   │   ├── useAdminList.js
│   │   │   ├── useAdminSocket.js
│   │   │   └── useAdminDashboard.js
│   │   ├── useAuthFetch.js
│   │   ├── useApi.js
│   │   └── useSocketConfig.js
│   ├── nuxt.config.ts
│   └── package.json
│
├── backend-laravel/
│   ├── app/
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   ├── Administrador.php
│   │   │   ├── Habit.php
│   │   │   ├── Plantilla.php
│   │   │   ├── Categoria.php
│   │   │   ├── RegistreActivitat.php
│   │   │   ├── Ratxa.php
│   │   │   ├── LogroMedalla.php
│   │   │   ├── UsuariHabit.php
│   │   │   ├── MissioDiaria.php
│   │   │   ├── PreguntaRegistre.php
│   │   │   ├── AdminLog.php
│   │   │   ├��─ AdminNotificacio.php
│   │   │   ├── AdminConfiguracio.php
│   │   │   └── Report.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   │   ├── UserAuthController.php
│   │   │   │   │   ├── HabitReadController.php
│   │   │   │   │   ├── PlantillaReadController.php
│   │   │   │   │   ├── GameStateReadController.php
│   │   │   │   │   ├── UserHomeReadController.php
│   │   │   │   │   ├── UserProfileReadController.php
│   │   │   │   │   ├── LogroReadController.php
│   │   │   │   │   └── Admin/
│   │   │   │   │       ├── AdminAuthController.php
│   │   │   │   │       ├── AdminDashboardController.php
│   │   │   │   │       ├── AdminUsuariController.php
│   │   │   │   │       ├── AdminHabitController.php
│   │   │   │   │       ├── AdminPlantillaController.php
│   │   │   │   │       ├── AdminMissioController.php
│   │   │   │   │       ├── AdminLogroController.php
│   │   │   │   │       ├── AdminNotificacioController.php
│   │   │   │   │       ├── AdminLogController.php
│   │   │   │   │       ├── AdminRankingController.php
│   │   │   │   │       ├── AdminReportController.php
│   │   │   │   │       ├── AdminPerfilController.php
│   │   │   │   │       └── AdminConfiguracioController.php
│   │   │   ├── Middleware/
│   │   │   │   ├── EnsureUserToken.php
│   │   │   │   └── EnsureAdminToken.php
│   │   │   └── Resources/
│   │   │       ├── UserProfileResource.php
│   │   │       ├── HabitResource.php
│   │   │       ├── PlantillaResource.php
│   │   │       ├── GameStateResource.php
│   │   │       └── Admin/ (recursos per a admin)
│   │   ├── Services/
│   │   │   ├── AuthService.php
│   │   │   ├── HabitService.php
│   │   │   ├── PlantillaService.php
│   │   │   ├── GamificationService.php
│   │   │   ├── MissionService.php
│   │   │   ├── LogroService.php
│   │   │   ├── RouletteService.php
│   │   │   ├── HomeDataService.php
│   │   │   ├── AdminActionService.php
│   │   │   ├── AdminLogService.php
│   │   │   └── RedisFeedbackService.php
│   │   └── Console/
│   │       └── Commands/
│   │           ├── UnifiedRedisWorker.php
│   │           └── QueueHandlers/ (Habit, Roulette, Plantilla, Admin)
│   ├── routes/
│   │   ├── api.php
│   │   ├── api/auth.php
│   │   ├── api/user.php
│   │   └── api/admin.php
│   ├── bootstrap/
│   └── composer.json
│
├── backend-node/
│   ├── src/
│   │   ├── index.js
│   │   ├── socketHandler.js
│   │   ├── middleware/
│   │   │   └── jwtAuth.js
│   │   ├── handlers/
│   │   │   ├── user/
│   │   │   │   ├── habitHandlers.js
│   │   │   │   ├── plantillaHandlers.js
│   │   │   │   ├── rouletteHandlers.js
│   │   │   │   └── userRegisterHandler.js
│   │   │   └── admin/
│   │   │       ├── adminHandlers.js
│   │   │       └── adminConnectedHandler.js
│   │   ├── queues/
│   │   │   ├── habitQueue.js
│   │   │   ├── plantillaQueue.js
│   │   │   ├── rouletteQueue.js
│   │   │   └── adminQueue.js
│   │   ├��─ subscribers/
│   │   │   ├── feedbackSubscriber.js
│   │   │   └── emitters/
│   │   │       ├── adminFeedbackEmitter.js
│   │   │       └── userFeedbackEmitter.js
│   │   └── shared/
│   │       └── usuarisConnectats.js
│   └── package.json
│
├── database/
│   ├── init.sql
│   ├── insert.sql
│   └── README.md
│
├── docker/
│   ├── docker-compose.yml
│   └── README.md
│
└── doc/
    ├── 01-SETUP-DESDE-CERO.md
    ├── 02-ESTRUCTURA-PROYECTO.md
    ├── BACKEND-DOCUMENTATION.md
    ├── ADMIN-BACKEND.md
    └── ...
```

### 3.3 Flux de Dades (Data Flow)

```
[Frontend Nuxt 3]
       │
       ├─► API REST (Laravel) ──► PostgreSQL
       │          │
       │          └─► Redis (Cache/Queues)
       │
       └─► Socket.io (Node.js) ──► Redis Pub/Sub
                    │
                    └─► Laravel Worker (Redis Queue)
                               │
                               └─► PostgreSQL
```

### 3.4 Autenticació

**JWT (JSON Web Tokens):**

- El backend Laravel genera tokens JWT en fer login
- El backend Node valida aquests tokens mitjançant el middleware `jwtAuth.js`
- Tokens emmagatzemats al frontend (localStorage o cookies)

---

## 4. Modelo de Datos

### 4.1 Taules de la Base de Dades

#### Taules d'Identitat i Accés

| Taula | Descripció | Camps Principals |
|-------|------------|------------------|
| **USUARIS** | Usuaris finals de l'aplicació | id, nom, email, contrasenya_hash, nivell, xp_total, xp_actual_nivel, xp_objetivo_nivel, monedes, ruleta_ultima_tirada, missio_diaria_id, missio_completada, prohibit, data_prohibicio, motiu_prohibicio, ultim_reset_missio |
| **ADMINISTRADORS** | Administradors del sistema | id, nom, email, contrasenya_hash, data_creacio |

#### Taules de Gamificació

| Taula | Descripció | Camps Principals |
|-------|------------|------------------|
| **LOGROS_MEDALLES** | Logros i medalles desbloquejables | id, nom, descripcio, tipus |
| **USUARIS_LOGROS** | Relació usuari-logro (many-to-many) | usuari_id, logro_id, data_obtencio |
| **MISSIOS_DIARIES** | Missionsdiàries | id, titol, tipus_comprovacio, parametres (JSONB) |
| **RATXES** | Rodes consecutives d'un usuari | id, usuari_id, ratxa_actual, ratxa_maxima, ultima_data |

#### Taules d'Hàbits i Plantilles

| Taula | Descripció | Camps Principals |
|-------|------------|------------------|
| **CATEGORIES** | Categories d'hàbits | id, nom |
| **PLANTILLES** | Plantilles d'hàbits (públiques o privades) | id, creador_id, titol, categoria, es_publica |
| **HABITS** | Hàbits concrets d'un usuari | id, usuari_id, plantilla_id, categoria_id, titol, dificultat, frequencia_tipus, dies_setmana (array), objectiu_vegades, unitat, icona, color |
| **PLANTILLA_HABIT** | Relació plantilla-hàbit (many-to-many) | plantilla_id, habit_id |
| **USUARIS_HABITS** | Hàbits actius d'un usuari | id, usuari_id, habit_id, data_inici, actiu, objetiu_vegades_personalitzat |

#### Taules de Registre i Seguiment

| Taula | Descripció | Camps Principals |
|-------|------------|------------------|
| **REGISTRE_ACTIVITAT** | Registre de completació d'hàbits | id, habit_id, data, valor, acabado, xp_guanyada |
| **PREGUNTES_REGISTRE** | Preguntes per al registre | id, categoria_id, pregunta, respostes_type |

#### Taules d'Administració

| Taula | Descripció | Camps Principals |
|-------|------------|------------------|
| **ADMIN_LOGS** | Logs d'accions d'administradors | id, administrador_id, accio, detall, abans (JSONB), despres (JSONB), ip, created_at |
| **ADMIN_NOTIFICACIONS** | Notificacions del sistema | id, administrador_id, tipus, titol, descripcio, data, llegida, metadata (JSONB) |
| **ADMIN_CONFIGURACIO** | Configuració del sistema | id, clau, valor, created_at, updated_at |
| **REPORTS** | Reports d'usuaris | id, usuari_id, tipus, contingut, post_id, estat, created_at |

### 4.2 Relacions entre Taules

```
USUARIS 1:N HABITS
USUARIS 1:N USUARIS_HABITS
USUARIS 1:N REGISTRE_ACTIVITAT
USUARIS 1:N RATXES
USUARIS 1:N USUARIS_LOGROS
USUARIS 1:1 MISSIOS_DIARIES (missio_diaria_id FK)
USUARIS 1:N PLANTILLES (creador_id FK)

HABITS N:1 CATEGORIES
HABITS N:1 PLANTILLES (plantilla_id FK)
HABITS N:1 USUARIS (usuari_id FK)

PLANTILLES N:N HABITS (PLANTILLA_HABIT)
PLANTILLES N:1 USUARIS (creador_id FK)

REGISTRE_ACTIVITAT N:1 HABITS

ADMIN_LOGS N:1 ADMINISTRADORS
ADMIN_NOTIFICACIONS N:1 ADMINISTRADORS
ADMIN_CONFIGURACIO -standalone

REPORTS N:1 USUARIS
```

### 4.3 Models Laravel (Eloquent)

**User.php:** Model principal - `Usuari`

- Relacions: habits(), plantilles(), registreActivitat(), ratxa(), logarros(), missioDiaria()
- Mètodes: checkAndAwardLogros(), updateXp(), addMonedes()

**Habit.php:** Model - `Habit`

- Relació: usuari(), plantilla(), categoria()
- Relació: registreActivitats()

**Plantilla.php:** Model - `Plantilla`

- Relació: creador(), hàbits()

**RegistreActivitat.php:** Model

- Relació: habit()

**Ratxa.php:** Model

- Relació: usuari()

---

## 5. Funcionalidades Detallades

### 5.1 Autenticació i Registre

#### 5.1.1 Registre d'Usuari

**Flux:**
1. L'usuari omple el formulari (nom, email, contrasenya)
2. El sistema valida que l'email no existeixi
3. Es crea l'usuari amb contrasenya hashejada (bcrypt)
4. Es generen les dades inicials de gamificació (nivell 1, XP 0, 0 monedes)
5. Es genera el token JWT i es retorna

**Endpoint:** `POST /api/auth/register`

**Controlador:** `UserAuthController::register()`

**Service:** `AuthService::register()`

#### 5.1.2 Login d'Usuari

**Flux:**
1. L'usuari introdueix email i contrasenya
2. Es busca l'usuari per email
3. Es verifica la contrasenya (bcrypt)
4. Es genera el token JWT (signat amb JWT_SECRET)
5. Es retorna el token

**Endpoint:** `POST /api/auth/login`

**Controlador:** `UserAuthController::login()`

**Service:** `AuthService::login()`

#### 5.1.3 Login d'Administrador

**Endpoint:** `POST /api/admin/auth/login`

**Controlador:** `AdminAuthController::login()`

#### 5.1.4 Refresh Token

**Endpoint:** `POST /api/auth/refresh`

**Controlador:** `UserAuthController::refresh()`

### 5.2 Gestió d'Hàbits

#### 5.2.1 Crear Hàbit

**Flux:**
1. L'usuari tria un hàbit existent d'una plantilla o crea'n un de nou
2. Es valida la disponibilitat (usuari no tingui ja l'hàbit)
3. Es crea l'hàbit a la taula HABITS
4. Es crea la relació a USUARIS_HABITS
5. Es retorna l'hàbit creat

**Endpoint:** Websocket: `habit_create`

**Handler (Node):** `habitHandlers.js`

**Queue (Redis):** `habitQueue` → Laravel

#### 5.2.2 Llistar Hàbits

**Endpoint:** `GET /api/habits`

**Controlador:** `HabitReadController::index()`

**Service:** `HabitService::getUserHabits()`

**Returns:** Array de `HabitResource`

#### 5.2.3 Completar Hàbit

**Flux:**
1. L'usuari marca un hàbit com a completat
2. Es valida que l'hàbit pertanyi a l'usuari i estigui actiu
3. Es crea el registre a REGISTRE_ACTIVITAT
4. Es calcula la XP guanyada segons la dificultat
5. Es verifica si es compleix la missió diària
6. Es verifica si s'atorguen nous logros
7. Es publica el feedback a Redis (feedback_channel)
8. El backend Node rep el feedback i ho envia via Socket al client

**Endpoint:** `POST /api/habits/complete`

**Controlador:** `HabitReadController::complete()`

**Service:** `HabitService::completeHabit()`

#### 5.2.4 Veure Progrés

**Endpoint:** `GET /api/habits/progress`

**Controlador:** `HabitReadController::progress()`

### 5.3 Plantilles

#### 5.3.1 Llistar Plantilles Públiques

**Endpoint:** `GET /api/plantilles`

**Controlador:** `PlantillaReadController::index()`

**Service:** `PlantillaService::getPublicPlantilles()`

#### 5.3.2 Veure Detalls d'una Plantilla

**Endpoint:** `GET /api/plantilles/{id}`

**Controlador:** `PlantillaReadController::show()`

### 5.4 Gamificació

#### 5.4.1 Estat del Joc (Game State)

**Endpoint:** `GET /api/game-state`

**Controlador:** `GameStateReadController::show()`

**Service:** `GamificationService::getGameState()`

**Retorna:**
- nivell, xp_total, xp_actual_nivel, xp_objetivo_nivel
- monedes
- ratxa_actual, ratxa_maxima
- missio_diaria (si n'hi ha), missio_completada

#### 5.4.2 Missions Diàries

El sistema assigna una missió diària a cada usuari (random de MISSIOS_DIARIES)
L'usuari ha de completar la missió abans de mitjanit
En completar, guanya XP extra

**Service:** `MissionService::checkAndAssignDailyMission()`

#### 5.4.3 Sistema de Rodes (Streaks)

Cada dia que l'usuari completa almenys un hàbit, s'incrementa la ratxa
Si un dia no completa res, la ratxa es reinicia a 0

**Service:** `GamificationService::updateStreak()`

#### 5.4.4 Logros

En completar certs hàbits o assolir fites, s'atorguen logros

**Service:** `LogroService::checkNewLogros()`

**Exemples de logros:**
- "Primer hàbit" (completar primer hàbit)
- "7 dies seguits" (ratxa de 7)
- "Millor persona" (tots els hàbits completats en un dia)

### 5.5 Ruleta

#### 5.5.1 Tirar la Ruleta

**Limit:** 1 tirada per dia

**Flux:**
1. L'usuari inicia la ruleta
2. Es verifica que no hagi tirat avui (ruleta_ultima_tirada)
3. Es genera un resultat aleatori (monedes, nada, XP)
4. S'actualitza la ruleta_ultima_tirada
5. S'afegeixen les monedes/XP
6. Es retorna el resultat

**Endpoint:** Websocket: `roulette_spin`

**Handler (Node):** `rouletteHandlers.js`

**Service (Laravel):** `RouletteService::spin()`

### 5.6 Home i Perfil

#### 5.6.1 Home de l'Usuari

**Endpoint:** `GET /api/user/home`

**Controlador:** `UserHomeReadController::index()`

**Service:** `HomeDataService::getHomeData()`

**Retorna:**
- Dades de l'usuari (nom, nivell, XP, monedes)
- Hàbits actius per avui
- Progrés del dia (completats/total)
- Ratxa actual
- Missió diària

#### 5.6.2 Perfil de l'Usuari

**Endpoint:** `GET /api/user/profile`

**Controlador:** `UserProfileReadController::profile()`

**Service:** `AuthService::getProfile()`

### 5.7 Funcionalitats d'Administrador

#### 5.7.1 Dashboard

**Endpoint:** `GET /api/admin/dashboard`

**Controlador:** `AdminDashboardController::index()`

**Service:** `AdminActionService::getDashboard()`

**Retorna:**
- Total usuaris (actius, prohibit, registrats avui)
- Total hàbits actius
- Total plantilles públiques
- Rànquing d'usuaris (XP)
- Activitat recent

#### 5.7.2 Gestió d'Usuaris

**Llistar:** `GET /api/admin/usuaris/{tipus}/{page}/{per_page}/{prohibit}/{cerca}`

**Prohibir:** `PATCH /api/admin/usuaris/{id}/prohibir`

**Controlador:** `AdminUsuariController`

#### 5.7.3 Gestió d'Hàbits

**Llistar:** `GET /api/admin/habits/{page}/{per_page}`

**Controlador:** `AdminHabitController`

#### 5.7.4 Gestió de Plantilles

**Llistar:** `GET /api/admin/plantilles/{page}/{per_page}`

**Controlador:** `AdminPlantillaController`

#### 5.7.5 Gestió de Missions

**Llistar:** `GET /api/admin/missions/{page}/{per_page}`

**Controlador:** `AdminMissioController`

#### 5.7.6 Gestió de Logros

**Llistar:** `GET /api/admin/logros/{page}/{per_page}`

**Controlador:** `AdminLogroController`

#### 5.7.7 Notificacions

**Llistar:** `GET /api/admin/notificacions/{page}/{per_page}/{llegida}`

**Marcar lectura:** `PATCH /api/admin/notificacions/{id}`

**Controlador:** `AdminNotificacioController`

#### 5.7.8 Logs

**Llistar:** `GET /api/admin/logs/{page}/{per_page}/{data_desde}/{data_fins}/{administrador_id}/{accio}/{cerca}`

**Controlador:** `AdminLogController`

#### 5.7.9 Configuració

**Veure:** `GET /api/admin/configuracio`

**Actualitzar:** `PUT /api/admin/configuracio`

**Controlador:** `AdminConfiguracioController`

---

## 6. Flujos de Usuario y Navegación

### 6.1 Flux d'Usuari Normal

```
[Pàgina inicial] → [Login/Registre]
       │
       ├─Si nou usuari: [Onboarding (pregunte)]
       │                    │
       │                    ▼
       │           [Tria de plantilles]
       │                    │
       │                    ▼
       │           [Home (ús habitual)]
       │
       └─Si usuari registrat: [Home]
                          │
          ┌──────────────┼──────────────┐
          ▼            ▼            ▼
    [Hàbits]    [Plantilles]  [Perfil]
          │            │            │
          │            │            └─► Editar perfil
          │            │            └─► Logros
          │            └─► Veure plantilles públiques
          └─► Completar hàbit
               │
               ▼
         [Feedback (XP, missió)]
               │
               ▼
         [Ruleta (1/dia)]
```

### 6.2 Flux de Login/Registre

```
[Login.vue] → Email + Contrasenya
      │
      ├─És vàlid → [API POST /auth/login]
      │                │
      │                ▼
      │          [Token rebut]
      │                │
      │                ▼
      │          [Redirecció a /home]
      │
      └─No té compte → [Registre.vue]
                          │
                          ▼
                     [Formulari registre]
                          │
                          ▼
                     [API POST /auth/register]
                          │
                          ▼
                     [Token rebut]
                          │
                          ▼
                     [Redirecció a onboarding]
```

### 6.3 Flux d'Onboarding

```
[Onboarding] → [API GET /onboarding/questions]
                     │
                     ▼
                [Preguntes de categorització]
                     │
                     ▼
                [Respostes enviades]
                     │
                     ▼
                [Suggeriment de plantilles]
                     │
                     ▼
                [L'usuari tria plantilles]
                     │
                     ▼
                [Crear hàbits des de plantilles]
                     │
                     ▼
                [Redirecció a /home]
```

### 6.4 Flux de Completa Hàbit

```
[Hàbits] → [Selecció d'hàbit]
                │
                ▼
           [Botó complet]
                │
                ▼
           [Socket: habit_complete] / [API POST /habits/complete]
                │
                ├─► Validació (Laravel)
                ├─► REGISTRE_ACTIVITAT creat
                ├─► XP calculada
                ├─► Ratxa actualitzada
                ├─► Missió comprovada
                ├─► Logros comprovats
                └─► Publicar a feedback_channel (Redis)
                     │
                     ▼
                [Feedback rebut (Socket)]
                     │
                     ▼
                [Actualitzar UI (XP, progrés)]
                     │
                     ▼
                [Feedback visual (animació)]
```

### 6.5 Flux d'Administrador

```
[Login Admin] → [Panel Admin]
      │
      ▼
[Dashboard]
      │
      ├─► [Usuaris] → Llistar / Buscar / Prohibir
      ├─► [Hàbits] → Llistar
      ��─► [Plantilles] → Llistar / Crear / Editar
      ├─► [Missions] → Llistar / Crear / Editar
      ├─► [Logros] → Llistar / Crear / Editar
      ├─► [Notificacions] → Llistar / Crear / Marcar llegida
      ├─► [Configuració] → Editar valors
      └─► [Perfil] → Editar dades
```

---

## 7. Inventario de Pantallas/Vistas

### 7.1 Vistes Públiques ( sense autenticació)

| Vista | Fitxer | Descripció |
|------|-------|------------|
| **Home pública** | `pages/index.vue` | Landing page de l'aplicació |
| **Login** | `pages/Login.vue` | Formulari de login (legat) |
| **Login (auth)** | `pages/auth/login.vue` | Formulari de login (nou) |
| **Registre** | `pages/Registre.vue` | Formulari de registre (legat) |
| **Registre (auth)** | `pages/auth/registre.vue` | Formulari de registre (nou) |

### 7.2 Vistes d'Usuari (autenticat)

| Vista | Fitxer | Components | Funcionalitats |
|------|-------|-----------|------------|
| **Home usuari** | `pages/home.vue` | Header, mascota, hàbits del dia, missió diària, ruteta | Veure progrés, accedir a hàbits, tirar ruleta |
| **Hàbits** | `pages/habits.vue` | Llista hàbits, botons completar, gràfic | Llistar, completar, veure progrés |
| **Plantilles** | `pages/Plantilles.vue` | Llista plantilles públiques | Explorar plantilles, agafar hàbits |
| **Perfil** | `pages/perfil.vue` | Info usuari, logros, estadístiques | Veure perfil, editar, veure logros |

### 7.3 Vistes d'Administrador

| Vista | Fitxer | Components | Funcionalitats |
|------|-------|-----------|------------|
| **Dashboard** | `pages/admin/index.vue` | KPlis, gràfiques, rànquing | Vista general del sistema |
| **Usuaris** | `pages/admin/usuaris.vue` | Taula usuaris, cerca, filtres | Llistar, cercar, prohibir |
| **Hàbits** | `pages/admin/habits.vue` | Llista hàbits | Veure tots els hàbits |
| **Plantilles** | `pages/admin/plantilles.vue` | Llista plantilles | Gestionar plantilles |
| **Missions** | `pages/admin/missions.vue` | Llista missions | Gestionar missions |
| **Logros** | `pages/admin/logros.vue` | Llista logros | Gestionar logros |
| **Notificacions** | `pages/admin/notificacions.vue` | Llista notificacions | Veure, marcar llegides |
| **Configuració** | `pages/admin/configuracio.vue` | Formulari config | Editar configuració |
| **Perfil Admin** | `pages/admin/perfil.vue` | Perfil admin | Editar perfil |

### 7.4 Vistes d'Error

| Vista | Fitxer | Descripció |
|------|-------|------------|
| **403 Prohibit** | `pages/error/403.vue` | Accés denegat |
| **404 No trobat** | `pages/error/404.vue` | Recurs no trobat |
| **500 Error servidor** | `pages/error/500.vue` | Error intern |

### 7.5 Layouts

| Layout | Fitxer | Descripció |
|--------|-------|------------|
| **Default** | `layouts/default.vue` | Layout per a usuaris normals |
| **Admin** | `layouts/admin.vue` | Layout per a administradors (sidebar, header) |

---

## 8. Endpoints y API

### 8.1 API Pública (sense autenticació)

| Mètode | Endpoint | Descripció |
|--------|---------|-----------|
| **POST** | `/api/auth/register` | Registrar nou usuari |
| **POST** | `/api/auth/login` | Login usuari |
| **POST** | `/api/admin/auth/login` | Login administrador |
| **POST** | `/api/auth/refresh` | Refrescar token |
| **POST** | `/api/auth/logout` | Logout |
| **GET** | `/api/onboarding/questions` | Obtenir preguntes onboarding |
| **GET** | `/api/preguntes-registre/{categoria_id}` | Obtenir preguntes de registre |

### 8.2 API d'Usuari (middleware: ensure.user)

| Mètode | Endpoint | Descripció |
|--------|---------|-----------|
| **GET** | `/api/habits` | Llistar hàbits de l'usuari |
| **GET** | `/api/habits/all` | Llistar tots els hàbits |
| **GET** | `/api/habits/{id}` | Veure hàbit específic |
| **GET** | `/api/habits/progress` | Veure progrés |
| **GET** | `/api/habits/logs` | Veure registre d'activitats |
| **POST** | `/api/habits/complete` | Completar hàbit |
| **GET** | `/api/plantilles` | Llistar plantilles públiques |
| **GET** | `/api/plantilles/{id}` | Veure plantilla |
| **GET** | `/api/game-state` | Estat del joc |
| **GET** | `/api/user/home` | Home de l'usuari |
| **GET** | `/api/logros` | Llista de logros |
| **GET** | `/api/user/profile` | Perfil de l'usuari |

### 8.3 API d'Administrador (middleware: ensure.admin)

| Mètode | Endpoint | Descripció |
|--------|---------|-----------|
| **GET** | `/api/admin/dashboard` | Dashboard |
| **GET** | `/api/admin/notificacions/{page}/{per_page}/{llegida}` | Notificacions |
| **PATCH** | `/api/admin/notificacions/{id}` | Marcar lectura |
| **GET** | `/api/admin/logs/...` | Logs |
| **GET** | `/api/admin/rankings/{periodo}` | Rànquings |
| **GET** | `/api/admin/usuaris/...` | Llistar usuaris |
| **PATCH** | `/api/admin/usuaris/{id}/prohibir` | Prohibir usuari |
| **GET** | `/api/admin/plantilles/...` | Plantilles |
| **GET** | `/api/admin/habits/...` | Hàbits |
| **GET** | `/api/admin/logros/...` | Logros |
| **GET** | `/api/admin/missions/...` | Missions |
| **GET** | `/api/admin/reports/...` | Reports |
| **GET** | `/api/admin/perfil` | Perfil admin |
| **PUT** | `/api/admin/perfil` | Actualitzar perfil |
| **PATCH** | `/api/admin/perfil/password` | Canviar contrasenya |
| **GET** | `/api/admin/configuracio` | Configuració |
| **PUT** | `/api/admin/configuracio` | Actualitzar configuració |

### 8.4 Websockets (Node.js)

| Event | Direcció | Descripció |
|-------|----------|-----------|
| **connection** | Client → Server | Nova connexió |
| **habit_complete** | Client → Server | Completar hàbit |
| **plantilla_adopt** | Client → Server | Agafar plantilla |
| **roulette_spin** | Client → Server | Tirar ruleta |
| **admin_connect** | Client → Server | Admin es connecta |
| **user_connected** | Server → Client | Feedback de connexió |
| **xp_updated** | Server → Client | XP actualitzada |
| **mission_completed** | Server → Client | Missió completada |
| **streak_updated** | Server → Client | Ratxa actualitzada |
| **roulette_result** | Server → Client | Resultat ruleta |
| **achievement_unlocked** | Server → Client | Logro desbloquejat |

---

## 9. Reglas de Negocio y Casos de Borde

### 9.1 Autenticació

- **Unicitat d'email:** No es permeten emails duplicats a la taula USUARIS
- **Longitud de contrasenya:** Mínim 6 caràcters
- **Format d'email:** Cal ser un email vàlid
- **JWT:** Token amb expiració (configurable)

### 9.2 Hàbits

- **Límit d'hàbits:** Màxim 20 hàbits actius per usuari
- **Nom de l'hàbit:** Màxim 100 caràcters
- **Dies de la setmana:** Array de 7 booleans (dilluns a diumenge)
- **Frequència:** "diaria", "setmanal", "mensual"
- **Dificultat:** "facil", "mitjana", "dificil" (afecta XP)

### 9.3 Gamificació

- **XP per dificultat:**
  - Fàcil: 10 XP
  - Mitjana: 25 XP
  - Difícil: 50 XP

- **Fórmula de nivell:** `nivell = floor(sqrt(xp_total / 100)) + 1`
- **XP per pujar de nivell:** `(nivell * 1000)`
- **Monedes per hàbit:** 1-5 (segons dificultat)

- **Ruleta:**
  - 1 tirada per dia (reset a mitjanit)
  - Possible resultat: 5-50 monedes, 10-100 XP, o "res"

### 9.4 Missions Diàries

- **Una missió per dia:** Es renova a mitjanit
- **Fallida:** Si no es completa, es perd (no hi ha penalty extra)

### 9.5 Rodes (Streaks)

- **Increment:** 1 dia següent si s'ha completat almenys 1 hàbit
- **Reset:** Si un dia no es completa cap, es posa a 0
- **Màxim històric:** Es guarda la ratxa màxima assolida

### 9.6 Admin

- **Prohibició d'usuari:**
  - Es pot prohibir per: "spam", "contingut inapropiat", "altres"
  - Motiu obligatori
  - Data de prohibició automàtica
  - Pot ser permanent o temporal (depen del motiu)

- **Logs:** Totes les accions administratives es registren

### 9.7 Casos de Front

- **401 (No autentificat):** Redirecció a /login
- **403 (Prohibit):** Redirecció a /error/403
- **404 (No trobat):** Redirecció a /error/404
- **500 (Error):** Redirecció a /error/500
- **Error de xarxa:** Mostrar missatge d'error
- **Timeout:** Retry automàtic (1 intent)

---

## 10. Estado Actual y Deuda Técnica

### 10.1 Funcionalitats Implementades

| Funcionalitat | Estat | Notes |
|--------------|-------|-------|
| **Autenticació ( usuari)** | ✅ Completat | Login, registre, logout, refresh |
| **Autenticació (admin)** | ✅ Completat | Login, gestió perfil |
| **Gestió d'hàbits** | ✅ Completat | CRUD, completar, llistar |
| **Plantilles públiques** | ✅ Completat | Llistar, agafar |
| **Sistema de gamificació** | ✅ Completat | XP, nivell, monedes |
| **Ruleta** | ✅ Completat | 1 tirada/dia |
| **Missions diàries** | ✅ Completat | Assignació, comprovació |
| **Rodes (streaks)** | ✅ Completat | Actualització automàtica |
| **Logros** | ✅ Completat | desbloqueig automàtic |
| **WebSockets real-time** | ✅ Completat | Feedback instantani |
| **Panel d'admin** | ✅ Completat | Dashboard, gestió usuaris, etc. |
| **Onboarding IA** | ⚠️ Parcial | Configurat, pendent integració amb Gemini |
| **i18n** | ✅ Completat | CA, ES, EN |

### 10.2 Parts Incompletes o Pendents

- **Onboarding IA:** La integració amb Google Gemini API està configurada però no completament integrada al flux d'onboarding.

- **Tests:** No hi ha tests automatitzats (ni frontend ni backend)

- **Plantilles públiques (creació):** No permet crear noves plantilles des del frontend d'usuari (només des d'admin)

- **Editar hàbits:** Funcionalitat bàsica completada, però millores pendents

- **WebSocket (admin):** Funcionalitats bàsiques, pendent expansió

### 10.3 Deuda Tècnica

1. **Codi duplicat:** Algunes funcions als handlers de Node i als services de Laravel estan duplicades o podrien refactoritzar-se.

2. **Validacions:** Algunes validacions es fan al frontend i altre cops caldria fer-les més robustes al backend.

3. **Error handling:** Millorar la gestió d'errors global.

4. **Redis caching:** Encara no s'utilitza el cache de Redis per a consultes pesades (només per a cues i pub/sub).

5. **Documentació interna:** Manquen més comentaris i documentació al codi.

6. **Performance:** Algunes consultes SQL podrien optimitzar-se (índexs, query builder).

### 10.4 Versions i Configuració

| Component | Versió Actual |
|-----------|------------|
| Node.js | 20.11.1 |
| PHP | 8.3 |
| Laravel | 11.x |
| Nuxt | 3.10.3 |
| PostgreSQL | 16 |
| Redis | 7.2 |

---

## Annex: Referències Ràpides

### Fitxers Clau

- **Docker:** `docker/docker-compose.yml`
- **Entorn:** `.env.example` → `.env`
- **Inicialització BD:** `database/init.sql`, `database/insert.sql`
- **Middleware Auth:** `backend-laravel/app/Http/Middleware/EnsureUserToken.php`, `EnsureAdminToken.php`
- **Socket Auth:** `backend-node/src/middleware/jwtAuth.js`

### URLs de Serveis

- **Frontend:** http://localhost:3000
- **Backend Node:** http://localhost:3001
- **Backend Laravel:** http://localhost:8000
- **PostgreSQL:** localhost:5432
- **Redis:** localhost:6379

---

*Document generat automàticament per a ús com a Single Source of Truth per a models d'IA.*
*Última actualització: 2026-04-15*