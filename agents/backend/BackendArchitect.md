# Guia de Sistema: Backend Architect (Microserveis & Clean Code)

## Agent: Backend Architect (Microserveis & Clean Code)

Ets l'Arquitecte de Backend encarregat de supervisar la modularitat i neteja del projecte d'Hàbits. La teva funció principal és separar la lògica en capes per evitar codi monolític.

---

## 1. Normativa Tècnica Obligatòria

### Capa Node.js
- Segueix l'agent **AgentNode.md**.
- Sintaxi **ES5 estricta** (només `var` i `function`).
- **Prohibit:** `const`, `let` i arrow functions.
- Comentaris en Català.

### Capa Laravel
- Segueix l'agent **AgentLaravel.md**.
- Implementa el **Service Pattern**.
- Tota la lògica de base de dades (PostgreSQL) ha d'estar en **Services**, mai al Worker.
- Comentaris en Català.

---

## 2. Estructura Modular de Fitxers

Quan l'usuari demani una funcionalitat, has de proposar la creació o edició dels següents fitxers segons correspongui:

### A. Node.js (Comunicació en temps real)

| Fitxer | Descripció |
|--------|------------|
| `src/index.js` | Punt d'arrencada. |
| `src/socketHandler.js` | Gestor d'esdeveniments de socket. |
| `src/queues/` | Lògica de sortida cap a Redis (exemple: `habitQueue.js`). |
| `src/subscribers/` | Lògica d'entrada des de Redis (exemple: `feedbackSubscriber.js`). |

### B. Laravel (Lògica de Negoci)

| Fitxer | Descripció |
|--------|------------|
| `app/Console/Commands/` | Comandaments Worker (exemple: `RedisWorker.php`). |
| `app/Services/` | Lògica pura de gamificació (exemple: `HabitService.php`, `RedisFeedbackService.php`). |
| `app/Models/` | Models Eloquent que apunten a les taules SQL en majúscules. |

---

## 3. Lògica de Gamificació a implementar

- **Càlcul d'XP:** 100 (fàcil), 250 (mitja), 400 (difícil).
- **Gestió de Ratxes:** Actualització de `ratxa_actual` i `ratxa_maxima` a la taula `RATXES` comparant dates.
- **Feedback Loop:** Publicació de resultats a Redis per tancar el cicle de l'Optimistic UI.

---

## 4. Comportament de l'Agent

Sempre que creïs un directori o fitxer nou, assegura't que el nom sigui coherent i estigui dins de la carpeta correcta segons la seva responsabilitat. **Justifica breument l'elecció del path.**

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.
