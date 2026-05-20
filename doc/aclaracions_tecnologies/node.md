# Node.js — Aclaracions tècniques (Loopy)

> Document per respondre preguntes sobre el **backend Node** (port 3001): sockets, cues Redis i dominis.

---

## 1. Què és i per què l'hem triat

| Aspecte | Detall |
| :--- | :--- |
| **Versió** | Node.js 20 LTS |
| **Llibreries** | Express (HTTP mínim), Socket.io 4, client Redis |
| **Rol** | Temps real, pont cap a Laravel, onboarding amb Gemini, senyalització WebRTC |

**Per què Node per a això i no Laravel?**

- Socket.io és natural a l'ecosistema JavaScript
- Connexions WebSocket persistents sense bloquejar PHP-FPM
- Separació clara: Laravel = negoci + BD; Node = esdeveniments + bridge

---

## 2. Model d'arquitectura

### **Event-driven + Domain folders + Redis Bridge**

```
Client Socket.io → Handler per domini → Queue Publisher (LPUSH) → Redis
Redis SUBSCRIBE feedback_channel → Emitter → Socket.io → Client
```

| Concepte | Implementació |
| :--- | :--- |
| **Orquestrador** | `socketHandler.js` registra tots els dominis |
| **Handlers** | Un fitxer per tipus d'esdeveniment (`habitSocketHandlers.js`, etc.) |
| **Publishers** | Enviament a cues Redis (`habitQueuePublisher.js`) |
| **Subscribers** | `feedbackSubscriber.js` escolta Laravel |

No és microserveis: és un **monòlit Node modular** dins un sol procés.

---

## 3. Estructura de carpetes

```
backend-node/src/
├── index.js                    # HTTP + Socket.io server
├── socketHandler.js            # Registre de dominis
├── middleware/jwtAuth.js       # Validació JWT al handshake
├── domains/
│   ├── Habits/handlers/        # habit_action, habit_completed...
│   ├── Habits/publishers/      # LPUSH habits_queue
│   ├── Social/handlers/        # xat, posts, amistats (notificacions)
│   ├── Admin/handlers/
│   ├── WebRTC/handlers/        # video_offer, ICE candidates
│   └── Onboarding/services/    # Gemini API
├── queues/                     # habitQueue, plantillaQueue, adminQueue...
├── subscribers/
│   └── feedbackSubscriber.js   # SUBSCRIBE feedback_channel
└── shared/                     # socketRooms, socketUserId
```

---

## 4. Com es comunica amb altres tecnologies

| Connexió | Protocol | Direcció |
| :--- | :--- | :--- |
| **Frontend** | WebSocket (Socket.io) | Bidireccional |
| **Redis** | LPUSH (anada), SUBSCRIBE (tornada) | Node ↔ Redis ↔ Laravel |
| **Laravel** | Indirecta (només via Redis) | Sense HTTP entre Node i Laravel |
| **Gemini** | HTTPS | Onboarding IA |
| **PostgreSQL** | **No** connecta directament | Laravel és l'únic que escriu a BD |

---

## 5. Flux complet: crear un hàbit

```text
1. Frontend: socket.emit('habit_action', { action: 'CREATE', ... })
2. Node: habitSocketHandlers → valida JWT/userId → join sala user_{id}
3. Node: habitQueuePublisher.pushToLaravel() → LPUSH habits_queue
4. Laravel: UnifiedRedisWorker BRPOP → HabitService → PostgreSQL
5. Laravel: RedisFeedbackService.publish → feedback_channel
6. Node: feedbackSubscriber → io.to('user_X').emit('feedback', payload)
7. Frontend: actualitza Pinia / UI
```

---

## 6. Convencions ES5 estrictes

| Permès | Prohibit |
| :--- | :--- |
| `var`, `function()` | `const`, `let`, arrow `=>` |
| `async function` + `await` | `map/filter/reduce`, destructuring, ternaris |

**Per què ES5?** Norma d'equip per homogeneïtzar Node amb parts legacy del frontend i facilitar revisió entre alumnes amb nivells diferents de JavaScript modern.

---

## 7. Dominis registrats al socketHandler

| Domini | Esdeveniments (exemples) | Cua Redis |
| :--- | :--- | :--- |
| Habits | `habit_action`, `habit_completed` | `habits_queue` |
| Plantilles | accions de plantilla | `plantilles_queue` |
| Ruleta | tirada diària | `roulette_queue` |
| Admin | accions de moderació | `admin_queue` |
| Social | xat, posts (part notificacions) | Mix REST + socket |
| WebRTC | `video_offer`, `new_ice_candidate` | Sense cua (P2P) |
| Presència | online/offline | Sense cua |

---

## 8. Seguretat

- **JWT al handshake** (`middleware/jwtAuth.js`): sense token vàlid, no es connecta
- **`JWT_SECRET`** compartit amb Laravel (mateix algoritme de verificació)
- Sales per usuari: `user_{id}` per rebre feedback només del propi usuari

---

## 9. Preguntes freqüents dels professors

**P: Node té base de dades?**  
R: No. Tota persistència la fa Laravel. Node és stateless excepte connexions socket actives.

**P: Per què no fer sockets amb Laravel Reverb/Pusher?**  
R: Decisió de projecte: separar responsabilitats i aprofitar l'experiència de l'equip amb Socket.io + Redis com a bus.

**P: Què passa si Node cau?**  
R: El frontend perd temps real; les GET per Laravel segueixen funcionant. Les accions CUD en cua esperen al Redis fins que el worker Laravel les processi (encara que Node no reemeti feedback fins que torni).

**P: Port del servei?**  
R: `3001` per defecte (`SOCKET_URL` al `.env`).

---

## 10. Referències internes

- `agents/backend/AgentNode.md`
- `.cursor/rules/node-backend.mdc`, `socket-realtime.mdc`
- [redis.md](./redis.md), [sockets.md](./sockets.md)
