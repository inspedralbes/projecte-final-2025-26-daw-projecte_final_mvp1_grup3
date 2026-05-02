# Agent de Desenvolupament Backend (Laravel)

Aquest document defineix el comportament, les responsabilitats i les restriccions tècniques de l'agent especialitzat en la capa de backend desenvolupada amb Laravel.

## 1. Rol i Objectiu

L'agent és el responsable de la **Capa de Negoci i Dades** del sistema. Les seves funcions principals són:

- Gestionar la persistència de dades en PostgreSQL.
- Implementar la lògica de gamificació.
- Actuar com l'únic **emissor de tokens de seguretat (JWT)** per a tot l'ecosistema del projecte.

## 2. Usuari per Defecte (Sense Autenticació)

- **No s'utilitza autenticació** (JWT, Sanctum, sessions, etc.).
- L'usuari per defecte i administrador és sempre el de **id 1**.
- Tots els controladors i serveis que necessitin un `usuari_id` han d'utilitzar `1` com a valor per defecte.
- Les rutes API són públiques i no requereixen cap middleware d'autenticació.

## 3. Restriccions Tècniques (No Negociables)

L'agent ha de respectar estrictament les següents versions i protocols:

- **Framework**: Laravel 11.0.0 amb PHP 8.3.3.
- **Base de Dades**: PostgreSQL 16.2.
- **Seguretat**: Únic `Issuer` de JWT. S'utilitza una clau secreta compartida (`JWT_SECRET`) per a la validació local.
- **Comunicació Asíncrona**: Ús de Redis 7.2.4 com a bus de dades (Bridge) per a la sincronització amb el backend de Node.js.
- **Condicionals**: No utilitzis operadors ternaris, utilitza els classics if i elses o bucles whiles o for/foreach.
- **Prohibició global**: Cap fitxer de `backend-laravel` pot contenir operadors ternaris.

## 4. Estructura de Fitxers i Responsabilitats

L'organització del codi segueix una arquitectura enfocada a la separació de conceptes:

- **Controllers (`app/Http/Controllers/`)**:
  - Gestió de rutes d'autenticació.
  - Definició de l'API REST per al consum del frontend.

## 3.1. Convencions de Rutes API (Obligat)

- **Paràmetres al path, no query**: Els identificadors (id, categoria_id, etc.) han d'anar sempre dins de la URL, no com a query parameters.
- **Correcte**: `GET /api/preguntes-registre/{categoria_id}`, `GET /api/habits/{id}`
- **Prohibit**: `GET /api/preguntes-registre?categoria_id=1`
- Ús de paràmetres RESTful al path per mantenir coherència i claredat de l'API.
- **Services (`app/Services/`)**:
  - Contenen la lògica de negoci pesada.
  - Càlcul d'experiència (XP) i gestió de ratxes.
  - Components petits i reutilitzables.
- **Models (`app/Models/`)**:
  - Implementacions de models Eloquent per a la interacció amb PostgreSQL.
- **Commands (`app/Console/Commands/`)**:
  - Conté el `RedisWorker.php`, responsable de processar cues de forma infinita.

## 5. Estructura de Codi i Comentaris (Obligatori)

Per garantir el rigor visual i la traçabilitat del codi PHP, l'agent ha d'estructurar cada classe (Controller, Service o Command) seguint aquest esquema de comentaris de bloc:

```php
//================================ NAMESPACES / IMPORTS ============

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

//================================ RUTES / LOGICA PRIVADA ========
```

### Normativa de Documentació Interna:

L'agent té prohibit escriure codi sense documentació explicativa. S'ha de seguir aquest model dins de cada mètode:

1. **Capçalera de Funció**: Descripció clara del propòsit general de la funció.
2. **Explicació Pas a Pas**: Ús de lletres per marcar la seqüència lògica interna:
   - `// A. Recuperació de dades de la base de dades...`
   - `// B. Validació de requisits o lògica de negoci...`
   - `// C. Processament final i resposta o notificació...`
3. **Lògica de Control**: Tot l'ús de `if`, `foreach` o `while` ha d'anar precedit d'un comentari que expliqui la condició o el propòsit del bucle.

## 6. Estil de Codi i Convencions

- **Idioma**: Tot el codi (nom de variables, funcions, classes) i els comentaris han d'estar en **català**.
- **Nomenclatura**: Ús obligatori de **camelCase** per a variables i funcions.
- **Arquitectura**: La lògica mai s'ha de quedar als controladors; s'ha de delegar sempre als `Services`.

## 7. Mecanisme de Comunicació Redis (Bridge)

1. **Entrada**: El `UnifiedRedisWorker` escolta les cues `habits_queue`, `plantilles_queue`, `admin_queue`, `roulette_queue` mitjançant BRPOP multillista.
2. **Processament**: El worker delega en un Queue Handler (`app/Console/Commands/QueueHandlers/`). Cada handler té `handle(array $dades): void` i crida el Service corresponent (HabitService, PlantillaService, AdminActionService, RouletteService).
3. **Sortida**: Publicar confirmació al canal `feedback_channel` mitjançant `Redis::publish` (via RedisFeedbackService).

## 8. Esquema de Refactorització (Estructura Objectiu)

Cal respectar aquesta estructura en totes les tasques de Laravel:

### 8.1 Controllers
- **Nomenclatura**: Controllers de només lectura amb sufix `Read` (ex: `HabitReadController`, `UserProfileReadController`, `GameStateReadController`).
- **Docblocks obligatoris**: Cada controller ha de tenir un docblock de classe amb `Operacions: READ | CREATE | UPDATE | DELETE` (segons correspongui). Cada mètode amb docblock indicant l’operació i una breu descripció.

### 8.2 Resources
- **Tots els GET** han de passar per un Resource. El controller crida el Service i retorna `new XResource($dades)` o `XResource::collection($dades)`. No es permet `response()->json()` directe per a respostes estructurades.

### 8.3 Queue Handlers
- Ubicació: `app/Console/Commands/QueueHandlers/`.
- Fitxers: `HabitQueueHandler.php`, `PlantillaQueueHandler.php`, `AdminQueueHandler.php`, `RouletteQueueHandler.php`.
- Cada handler implementa `handle(array $dades): void` i delega en el Service corresponent.

## 9. Lògica de Gamificació (Referència)

| Dificultat de l'Hàbit | Experiència (XP) |
| :-------------------- | :--------------- |
| Fàcil                 | 100 XP           |
| Mitjà                 | 250 XP           |
| Difícil               | 400 XP           |

### Skills Disponibles

- **validadorReglesNegociXP** (Principal)
- **generadorDocumentacioTecnica** (Secundària)

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.
