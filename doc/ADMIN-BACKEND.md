# Documentación Backend Admin — Estructura, Archivos y Flujo

Documentación detallada del panel de administración: carpetas, archivos, funcionalidades y flujo de datos.

---

## Índice

1. [Arquitectura general](#1-arquitectura-general)
2. [Backend Laravel — Admin](#2-backend-laravel--admin)
3. [Backend Node.js — Admin](#3-backend-nodejs--admin)
4. [Flujo completo: GET vs CUD](#4-flujo-completo-get-vs-cud)

---

## 1. Arquitectura general

### Regla GET/CUD (Loopy)

| Operación | Flujo | Tecnología |
|-----------|-------|------------|
| **GET** (leer) | Frontend → `fetch` → API Laravel | HTTP directo |
| **CUD** (crear/actualizar/eliminar) | Frontend → Socket.io → Node → Redis → Laravel Worker → Feedback → Socket → Frontend | Tiempo real vía Redis |

### Componentes implicados

```
┌─────────────┐    GET     ┌──────────────┐
│  Frontend   │ ─────────► │   Laravel    │
│   (Vue)     │ ◄───────── │   API REST   │
└──────┬──────┘   JSON     └──────────────┘
       │
       │  admin_action (Socket)
       ▼
┌─────────────┐  LPUSH   ┌─────────┐  BRPOP   ┌──────────────┐
│    Node     │ ───────► │  Redis  │ ───────► │ Laravel      │
│  (Socket.io)│          │ admin_  │          │ Worker       │
│             │ ◄─────── │ queue   │          │ AdminQueue   │
└─────────────┘  PUBLISH └────┬────┘          │ Handler      │
       ▲                     │                └──────┬───────┘
       │              feedback_channel               │
       │                     │                       ▼
       │                     └──────────────── AdminActionService
       │                                        (CUD + AdminLog)
       └──────────────────── SUBSCRIBE ────────────────────────┘
```

---

## 2. Backend Laravel — Admin

### 2.1 Rutas (`routes/api/admin.php`)

Prefijo: `/api/admin`. Todas las rutas pasan por el middleware `ensure.admin` (JWT con `role=admin`).

| Ruta | Método | Controlador | Descripción |
|------|--------|-------------|-------------|
| `/dashboard` | GET | AdminDashboardController | Métricas, top plantillas/hábitos, últimos logs |
| `/notificacions/{page}/{per_page}/{llegida}` | GET | AdminNotificacioController | Listado de notificaciones paginado |
| `/notificacions/{id}` | PATCH | AdminNotificacioController | Marcar notificación como leída |
| `/logs/{page}/{per_page}/{data_desde}/{data_fins}/{administrador_id}/{accio}/{cerca}` | GET | AdminLogController | Logs de auditoría con filtros |
| `/rankings/{periodo}` | GET | AdminRankingController | Rankings por periodo (setmana, mes, total) |
| `/usuaris/{tipus}/{page}/{per_page}/{prohibit}/{cerca}` | GET | AdminUsuariController | Usuarios o admins paginados |
| `/usuaris/{id}/prohibir` | PATCH | AdminUsuariController | Prohibir/desprohibir usuario |
| `/plantilles/{page}/{per_page}` | GET | AdminPlantillaController | Listado de plantillas |
| `/habits/{page}/{per_page}` | GET | AdminHabitController | Listado de hábitos |
| `/logros/{page}/{per_page}` | GET | AdminLogroController | Listado de logros |
| `/missions/{page}/{per_page}` | GET | AdminMissioController | Listado de misiones diarias |
| `/reports/{page}/{per_page}` | GET | AdminReportController | Listado de denuncias/reports |
| `/perfil` | GET | AdminPerfilController | Ver perfil del admin |
| `/perfil` | PUT | AdminPerfilController | Actualizar perfil (nom, email) |
| `/perfil/password` | PATCH | AdminPerfilController | Cambiar contraseña |
| `/configuracio` | GET | AdminConfiguracioController | Config global (key-value) |
| `/configuracio` | PUT | AdminConfiguracioController | Actualizar configuración |

### 2.2 Autenticación Admin

- **Ruta login:** `POST /api/admin/auth/login` (en `routes/api/auth.php`)
- **Controlador:** `AdminAuthController`
- **Middleware:** `EnsureAdminToken` — verifica JWT con `role=admin` e inyecta `admin_id` en el request

---

### 2.3 Carpetas y archivos

#### `app/Http/Controllers/Api/Admin/`

| Archivo | Función |
|---------|---------|
| **AdminAuthController** | Login del administrador (JWT con role admin) |
| **AdminConfiguracioController** | Config global: `show()` lee todas las claves, `update()` actualiza por clave-valor |
| **AdminDashboardController** | Métricas: total usuarios, prohibidos, plantillas públicas, top 5 plantillas/hábitos, últimos 10 logs |
| **AdminHabitController** | `index()` — listado paginado de hábitos con relaciones usuari/plantilla |
| **AdminLogController** | `index()` — logs de auditoría con filtros: fecha, administrador, acción, texto |
| **AdminLogroController** | `index()` — listado paginado de logros/medallas |
| **AdminMissioController** | `index()` — listado paginado de misiones diarias |
| **AdminNotificacioController** | `index()` — notificaciones por admin, filtro llegida; `marcarLlegida()` — PATCH |
| **AdminPerfilController** | `show()` perfil, `update()` nom/email, `canviarPassword()` |
| **AdminPlantillaController** | `index()` — listado paginado de plantillas |
| **AdminRankingController** | `index(periodo)` — rankings plantillas/hábitos (setmana, mes, total) |
| **AdminReportController** | `index()` — denuncias con usuario que reporta |
| **AdminUsuariController** | `index()` — usuarios/admins, filtros prohibit/cerca; `prohibir()` — PATCH |

#### `app/Http/Resources/Admin/`

| Archivo | Función |
|---------|---------|
| AdminConfiguracioResource | Formato respuesta de configuración |
| AdminDashboardResource | Formato dashboard (métricas, tops, logs) |
| AdminHabitResource | Formato hábit para admin |
| AdminLogResource | Formato log de auditoría |
| AdminLogroResource | Formato logro |
| AdminMissioResource | Formato misión diaria |
| AdminNotificacioResource | Formato notificación |
| AdminPerfilResource | Formato perfil admin |
| AdminPlantillaResource | Formato plantilla |
| AdminRankingResource | Formato rankings |
| AdminReportResource | Formato report/denuncia |
| AdminUsuariResource | Formato usuario/admin |

#### `app/Models/` (relacionados con admin)

| Archivo | Función |
|---------|---------|
| Administrador | Modelo admins (nom, email, contrasenya_hash) |
| AdminLog | Auditoría de acciones (administrador_id, accio, detall, abans, despres, ip) |
| AdminNotificacio | Notificaciones por admin (llegida, data) |
| AdminConfiguracio | Config key-value (clau, valor) |

#### `app/Services/`

| Archivo | Función |
|---------|---------|
| **AdminActionService** | Procesa acciones CUD de admin_queue: plantilla, usuari, admin, habit, logro, missio. Valida payload, ejecuta CRUD, registra en AdminLog, publica feedback vía RedisFeedbackService |
| **AdminLogService** | `registrar()` — crea registros en ADMIN_LOGS con abans/despres para auditoría |
| **RedisFeedbackService** | Publica en canal `feedback_channel` el resultado de las operaciones (admin_id, entity, action, success, data) |

#### `app/Http/Middleware/`

| Archivo | Función |
|---------|---------|
| EnsureAdminToken | Verifica JWT con `role=admin`, extrae `admin_id` y lo inyecta en el request |

#### `app/Console/Commands/QueueHandlers/`

| Archivo | Función |
|---------|---------|
| **AdminQueueHandler** | Recibe payload de `admin_queue`, delega a `AdminActionService::processarAccio()` |

#### `app/Console/Commands/`

| Archivo | Función |
|---------|---------|
| **UnifiedRedisWorker** | Comando `redis:unified-worker`. BRPOP multillista en habits_queue, plantilles_queue, admin_queue, roulette_queue. Despacha a AdminQueueHandler cuando llega mensaje de admin_queue |

---

### 2.4 Funcionalidades por entidad (vía admin_queue / AdminActionService)

Acciones CUD se envían por Socket `admin_action` y se procesan en Laravel:

| Entidad | CREATE | UPDATE | DELETE |
|---------|--------|--------|--------|
| **plantilla** | titol, categoria, es_publica, creador_id | id, titol, categoria, es_publica | id |
| **usuari** | nom, email, contrasenya | id, nom, email, contrasenya?, prohibit?, motiu? | id |
| **admin** | nom, email, contrasenya | id, nom, email, contrasenya? | id (no se puede eliminar admin 1) |
| **habit** | usuari_id, plantilla_id, titol, dificultat, frequencia_tipus, dies_setmana, objectiu_vegades | id, titol, dificultat, etc. | id |
| **logro** | nom, descripcio, tipus | id, nom, descripcio, tipus | id |
| **missio** | titol | id, titol | id |

---

## 3. Backend Node.js — Admin

### 3.1 Estructura

```
backend-node/
├── src/
│   ├── handlers/admin/
│   │   ├── adminHandlers.js      # admin_join, admin_action
│   │   └── adminConnectedHandler.js  # admin:request_connected
│   ├── queues/
│   │   └── adminQueue.js         # LPUSH a admin_queue
│   └── subscribers/
│       ├── feedbackSubscriber.js # SUBSCRIBE feedback_channel
│       └── emitters/
│           └── adminFeedbackEmitter.js  # Emite admin_action_confirmed
```

### 3.2 Archivos

| Archivo | Función |
|---------|---------|
| **handlers/admin/adminHandlers.js** | `admin_join`: valida JWT admin, une socket a sala `admin_{id}`. `admin_action`: valida token, llama a `adminQueue.pushToLaravel(action, adminId, entity, data)` |
| **handlers/admin/adminConnectedHandler.js** | `admin:request_connected`: devuelve lista de usuarios conectados (usuarisConnectats) en `admin:connected_users` |
| **queues/adminQueue.js** | `pushToLaravel(action, adminId, entityType, data)`: LPUSH JSON a Redis `admin_queue` |
| **subscribers/feedbackSubscriber.js** | SUBSCRIBE a `feedback_channel`. Si payload tiene `admin_id` → adminFeedbackEmitter; si `user_id` → userFeedbackEmitter |
| **subscribers/emitters/adminFeedbackEmitter.js** | Emite `admin_action_confirmed` a la sala `admin_{admin_id}` con entity, action, success, data |

### 3.3 Eventos Socket.io (admin)

| Evento (cliente → servidor) | Descripción |
|-----------------------------|-------------|
| `admin_join` | Entra en sala admin_X. Requiere JWT con role=admin y admin_id |
| `admin_action` | Envía acción CUD. Payload: `{ action, entity, data }` |
| `admin:request_connected` | Solicita lista de usuarios conectados |

| Evento (servidor → cliente) | Descripción |
|-----------------------------|-------------|
| `admin_action_confirmed` | Confirmación de la operación CUD (success, data, entity, action) |
| `admin:connected_users` | Lista de usuarios conectados |

---

## 4. Flujo completo: GET vs CUD

### Flujo GET (lectura)

1. Frontend hace `fetch('/api/admin/plantilles/1/20')` con Bearer JWT
2. Laravel valida JWT con EnsureAdminToken
3. AdminPlantillaController::index() consulta Plantilla y devuelve JSON
4. No intervienen Node ni Redis

### Flujo CUD (crear/actualizar/eliminar)

1. **Frontend** emite por Socket: `socket.emit('admin_action', { action: 'CREATE', entity: 'plantilla', data: { titol, categoria, ... } })`
2. **Node** (adminHandlers): valida JWT, llama a `adminQueue.pushToLaravel('CREATE', adminId, 'plantilla', data)`
3. **adminQueue.js**: hace `LPUSH admin_queue` con el payload JSON
4. **UnifiedRedisWorker** (Laravel): `BRPOP admin_queue` recibe el mensaje, invoca AdminQueueHandler
5. **AdminQueueHandler**: llama a `AdminActionService::processarAccio($dades)`
6. **AdminActionService**: procesa la entidad (ej. processarPlantilla), crea/actualiza/elimina en BD, registra en AdminLog, llama a RedisFeedbackService para publicar
7. **RedisFeedbackService**: `Redis::publish('feedback_channel', json_encode({ admin_id, entity, action, success, data }))`
8. **feedbackSubscriber** (Node): recibe mensaje, detecta admin_id, llama a adminFeedbackEmitter
9. **adminFeedbackEmitter**: emite `admin_action_confirmed` a sala `admin_{admin_id}`
10. **Frontend** recibe `admin_action_confirmed` y actualiza la UI

---

## Resumen rápido

| Componente | Ubicación | Rol |
|------------|-----------|-----|
| Rutas admin | `routes/api/admin.php` | Definición de endpoints GET/PATCH/PUT |
| Controladores | `app/Http/Controllers/Api/Admin/*` | Lógica HTTP, delegan a Services |
| Resources | `app/Http/Resources/Admin/*` | Formato JSON de respuesta |
| AdminActionService | `app/Services/AdminActionService.php` | Lógica CUD vía Redis |
| AdminLogService | `app/Services/AdminLogService.php` | Auditoría de acciones |
| AdminQueueHandler | `app/Console/Commands/QueueHandlers/AdminQueueHandler.php` | Conecta Redis → AdminActionService |
| UnifiedRedisWorker | `app/Console/Commands/UnifiedRedisWorker.php` | Worker que consume admin_queue |
| adminHandlers | `backend-node/src/handlers/admin/adminHandlers.js` | Socket admin_join, admin_action |
| adminQueue | `backend-node/src/queues/adminQueue.js` | LPUSH a admin_queue |
| adminFeedbackEmitter | `backend-node/src/subscribers/emitters/adminFeedbackEmitter.js` | Emite confirmación al admin |
