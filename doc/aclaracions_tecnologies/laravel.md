# Laravel — Aclaracions tècniques (Loopy)

> Document per respondre preguntes dels professors sobre **per què**, **com** i **amb què** s'integra Laravel al projecte Loopy.

---

## 1. Què és i per què l'hem triat

| Aspecte | Detall |
| :--- | :--- |
| **Versió** | Laravel 11, PHP 8.3 |
| **Rol al projecte** | API REST, autenticació JWT, persistència a PostgreSQL, workers Redis, proxies d'APIs externes |
| **Per què Laravel** | MVC madur, ecosistema PHP per DAW, Eloquent per ORM, Artisan per workers, integració Redis nativa |

Laravel **no** gestiona sockets directament. És la capa de **negoci i lectura** del sistema.

---

## 2. Model d'arquitectura

### Patró principal: **MVC + Service Layer + Domain Actions**

```
Petició HTTP → Middleware (JWT) → Controller (prim) → Action/Service → Model → PostgreSQL
```

| Capa | Responsabilitat | Exemple |
| :--- | :--- | :--- |
| **Controller** | Validar entrada, retornar JSON | `FriendshipController`, `HabitController` |
| **Action** | Cas d'ús d'un domini (una operació) | `AcceptFriendRequestAction`, `RemoveFriendAction` |
| **Service** | Lògica reutilitzable o complexa | `RedisFeedbackService`, serveis de gamificació |
| **Query** | Consultes de lectura | `ListFriendsQuery`, `GetClanQuery` |
| **Model** | Mapatge Eloquent ↔ taula | `User`, `Habit`, `Friendship` |

### CQRS lleuger (lectura vs escriptura)

| Tipus | Canal | Exemple |
| :--- | :--- | :--- |
| **GET (lectura)** | HTTP síncron des del frontend | `GET /api/friends`, `GET /api/habits` |
| **CUD (escriptura)** | Redis queue (des de Node) | Crear hàbit, completar hàbit, ruleta, admin |

Això desacobla la UI del processament pesat i permet feedback en temps real via socket.

---

## 3. Estructura de carpetes

```
backend-laravel/
├── app/
│   ├── Http/Controllers/Api/     # REST (usuari + admin)
│   ├── Domains/                  # Lògica per domini (Habits, Social, Admin, Shop...)
│   │   ├── Habits/Actions/
│   │   ├── Habits/Services/
│   │   ├── Social/Actions/
│   │   └── Shared/Services/      # RedisFeedbackService, etc.
│   ├── Models/                   # Eloquent (taules MAJÚSCULES)
│   └── Console/Commands/         # UnifiedRedisWorker
├── routes/api/
│   ├── auth.php
│   ├── user.php
│   └── admin.php
└── config/                       # jwt.php, database.php, redis.php
```

**Per què `Domains/`?** Agrupa la lògica per funcionalitat (hàbits, social, botiga) en lloc d'un únic `Services/` gegant. Facilita trobar codi i mantenir responsabilitats clares.

---

## 4. Com es comunica amb altres tecnologies

```text
Frontend (Nuxt) ──GET + JWT──► Laravel :8000 ──► PostgreSQL
Node :3001 ──LPUSH queues──► Redis ◄──BRPOP── Laravel Worker
Laravel Worker ──PUBLISH──► feedback_channel ──SUBSCRIBE──► Node ──Socket.io──► Frontend
```

| Tecnologia | Relació amb Laravel |
| :--- | :--- |
| **PostgreSQL** | Persistència via Eloquent / Query Builder |
| **Redis** | Cues (entrada CUD) + Pub/Sub (sortida feedback) + cache/sessions |
| **Node.js** | No es crida per HTTP; comparteix Redis com a bus de missatges |
| **Frontend** | `authFetch` → API Laravel per GET; sockets per confirmacions CUD |

---

## 5. Flux d'exemple: acceptar sol·licitud d'amistat (GET/CUD mixt)

**Lectura** (GET, síncron):

1. Frontend: `authFetch('/api/friends/pending')`
2. `FriendshipController::getPendingRequests` → `ListPendingFriendRequestsQuery`
3. Retorna JSON amb sol·licituds pendents

**Escriptura** (PUT, síncron en aquest cas — les amistats no passen per cua Redis):

1. Frontend: `authFetch('/api/friends/accept/{id}', { method: 'PUT' })`
2. `AcceptFriendRequestAction` valida permisos i actualitza `FRIENDSHIPS.status`
3. Retorna 200

> **Nota per als professors:** No tot CUD passa per Redis. Operacions lleugeres (amistats, reports, perfil) van directes per REST. Les operacions pesades o que necessiten feedback en viu (hàbits, plantilles, ruleta, admin massiu) van per **Node → Redis → Worker**.

---

## 6. Flux d'exemple: completar un hàbit (CUD asíncron)

1. Frontend emet `habit_action` per Socket.io
2. Node fa `LPUSH` a `habits_queue`
3. `UnifiedRedisWorker` (Laravel) fa `BRPOP`, despatxa a `HabitService`
4. Laravel guarda a PostgreSQL i publica a `feedback_channel`
5. Node rep el missatge i emet `feedback` / esdeveniments específics al client

Fitxers clau:

- Worker: `app/Console/Commands/UnifiedRedisWorker.php`
- Feedback: `app/Domains/Shared/Services/RedisFeedbackService.php`

---

## 7. Convencions del projecte

| Regla | Motiu |
| :--- | :--- |
| Taules en **MAJÚSCULES** (`USUARIS`, `HABITS`) | Convenció SQL del projecte; models amb `$table` explícit |
| Esquema a `database/init.sql` | Font de veritat; no migracions Laravel com a principal |
| Controllers prims | Testabilitat i separació de responsabilitats |
| Sense operador ternari | Norma interna de llegibilitat per l'equip |
| Paràmetres a la ruta | `GET /api/users/{id}` en lloc de query strings |
| Comentaris en català | Coherència amb la documentació del cicle |

---

## 8. Autenticació

- **JWT** compartit amb Node (`JWT_SECRET` al `.env`)
- Middleware `EnsureUserToken` extreu `user_id` del token
- Les rutes d'usuari exigeixen token vàlid; admin té rutes separades

Veure també: [jwt.md](./jwt.md)

---

## 9. Preguntes freqüents dels professors

**P: Per què no feu tot per API REST?**  
R: Per no bloquejar la UI en operacions que calculen XP, ratxes, logros i poden trigar. El client rep confirmació per socket quan Laravel acaba.

**P: Quin patró d'arquitectura és?**  
R: **MVC** (Laravel clàssic) + **Service Layer** + **CQRS lleuger** + **event-driven** via Redis Pub/Sub.

**P: On està la lògica de negoci?**  
R: A `Domains/*/Actions` i `Domains/*/Services`, **no** als controllers.

**P: Com proveu Laravel?**  
R: PHPUnit a `backend-laravel/tests/Feature/` (ex. cues, metadata d'hàbits).

---

## 10. Referències internes

- Documentació general: `doc/E_DOCUMENTACIO_TECNICA.md`
- Redis ↔ Laravel: `doc/arquitectura_backend/CONEXION-REDIS-LARAVEL.md`
- Regles Cursor: `.cursor/rules/laravel-backend.mdc`, `backend-architecture.mdc`
