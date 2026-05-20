# Socket.io — Aclaracions tècniques (Loopy)

> Comunicació en **temps real** entre frontend, Node i (indirectament) Laravel.

---

## 1. Què és Socket.io al projecte

| Capa | Tecnologia | Fitxer clau |
| :--- | :--- | :--- |
| **Client** | `socket.io-client` | `frontend/plugins/socket.client.js` |
| **Servidor** | `socket.io` sobre Express | `backend-node/src/index.js` |
| **Pont** | Composables + registre global | `useSocketBridge.js`, `useSocketRegistry.js` |

Socket.io **no substitueix** l'API REST: complementa les operacions CUD amb **feedback immediat**.

---

## 2. Regla d'or del projecte (GET vs CUD)

| Operació | Canal |
| :--- | :--- |
| **GET** (llegir dades) | `authFetch` → Laravel HTTP |
| **CUD** (crear/modificar/esborrar) | `socket.emit` → Node → Redis → Laravel |
| **Confirmació CUD** | Laravel → Redis Pub/Sub → Node → `socket.on` → UI |

**Per què?** Evitar que el client esperi bloquejat una resposta HTTP llarga i mantenir una sola font de veritat (PostgreSQL via Laravel).

---

## 3. Diagrama de comunicació

```text
┌──────────────┐                    ┌──────────────┐
│   Nuxt 3     │  WebSocket :3001   │   Node.js    │
│  $socket     │◄──────────────────►│  Socket.io   │
└──────┬───────┘                    └──────┬───────┘
       │ authFetch GET                      │ LPUSH / SUBSCRIBE
       ▼                                    ▼
┌──────────────┐                    ┌──────────────┐
│   Laravel    │◄──── BRPOP ────────│    Redis     │
│   :8000      │──── PUBLISH ──────►│              │
└──────────────┘                    └──────────────┘
```

---

## 4. Connexió al frontend

### Plugin global (`socket.client.js`)

1. Crea instància `io(socketUrl, { auth: { token } })`
2. `autoConnect: false` — es connecta quan hi ha JWT vàlid
3. Registra feedback global amb `inicialitzarFeedbackGlobal`
4. Reintenta amb `refrescarSessio()` si falla autenticació

### Pont per components (`useSocketBridge`)

```javascript
// Emissió
bridge.emitir('habit_action', payload);
// Escolta
bridge.registrar('feedback', callback);
```

**Per què un bridge?** Desacobla components del plugin Nuxt i facilita tests/mocks.

---

## 5. Sales (rooms) i feedback

- Cada usuari s'uneix a la sala `user_{userId}` en fer accions
- Laravel inclou `user_id` al payload de feedback
- Node reemet només a la sala correcta (no a tots els clients)

Fitxers: `backend-node/src/shared/socketRooms.js`, `feedbackSubscriber.js`

---

## 6. Esdeveniments principals

### Hàbits (CUD via cua)

| Emit (client → Node) | Acció |
| :--- | :--- |
| `habit_action` | CREATE, UPDATE, DELETE, metadata... |
| `habit_completed` | Toggle completat |
| `habit_progress` | Actualitzar progrés parcial |

| Rep (Node → client) | Acció |
| :--- | :--- |
| `feedback` | Payload genèric de confirmació/error |
| Esdeveniments específics | Segons tipus d'acció processada |

### Social i notificacions

| Emit | Ús |
| :--- | :--- |
| `friend_request_notify` | Avís a destinatari |
| `friend_request_accepted_notify` | Avís d'acceptació |
| Missatges de xat | Temps real entre amics |

### Admin

| Emit | Ús |
| :--- | :--- |
| `admin_join` | Sala d'administradors connectats |

### WebRTC (Focus Mode / vídeo)

| Emit | Ús |
| :--- | :--- |
| `video_offer`, `video_answer` | Negociació SDP |
| `new_ice_candidate` | Candidates ICE |

WebRTC **no passa per Laravel**: només senyalització per Socket.io; el flux de dades és P2P.

---

## 7. Registre de callbacks UI (`useSocketUiCallbacks`)

Store Pinia lleuger que registra funcions per reaccionar al feedback sense acoblament directe:

- `registrarHabitConfirmed` / `eliminarHabitConfirmed`
- `registrarPlantillaConfirmed`
- `registrarRouletteResult`

**Per què?** Diverses pàgines (home, habits, plantilles) escolten el mateix feedback sense duplicar `socket.on` a cada component.

---

## 8. Autenticació del socket

1. Client envia JWT a `auth.token` en connectar
2. Node valida amb `JWT_SECRET` (mateix que Laravel)
3. `socket.data.userId` disponible als handlers

Sense token → `connect_error: Authentication required`

---

## 9. Preguntes freqüents dels professors

**P: Socket.io és HTTP?**  
R: Comença amb HTTP i fa upgrade a WebSocket. Per això usem `transports: ['websocket']` al client.

**P: Per què no fem GET per socket?**  
R: REST és més simple per cache, paginació, i ferramentes (Postman, tests). Sockets només on cal immediatesa.

**P: Com sabeu que l'acció ha acabat?**  
R: Laravel publica a `feedback_channel`; el client rep `feedback` amb `success`, dades actualitzades o error.

**P: Les amistats van per socket?**  
R: No. Enviar/acceptar/rebutjar/eliminar amic va per **REST** (`/api/friends/*`). Socket només per notificacions opcionals.

---

## 10. Referències internes

- `agents/frontend/AgentSocket.md`
- `.cursor/rules/socket-realtime.mdc`
- [node.md](./node.md), [redis.md](./redis.md), [pinia.md](./pinia.md)
