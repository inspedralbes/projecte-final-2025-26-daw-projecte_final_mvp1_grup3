# Pinia — Aclaracions tècniques (Loopy)

> Gestió d'estat global al frontend Nuxt 3.

---

## 1. Què és Pinia i per què l'hem triat

| Aspecte | Detall |
| :--- | :--- |
| **Llibreria** | Pinia (store oficial Vue 3) |
| **Integració** | `@pinia/nuxt` al `nuxt.config.ts` |
| **Alternativa descartada** | Vuex (legacy Vue 2) |

**Motius:** API més simple, TypeScript-friendly, devtools, composables naturals amb Nuxt 3.

---

## 2. Model d'arquitectura

### **Store pattern per domini**

Cada store encapsula:

- **State**: dades reactives compartides
- **Actions**: crides API o sockets + actualització local
- **Getters** (opcional): dades derivades

```
Pàgina (orquestrador lleuger) → Store / Composable → authFetch o $socket
```

**Regla del projecte:** Les pàgines no han de tenir lògica de negoci grossa; deleguen als stores i composables.

---

## 3. Stores del projecte

| Store | Responsabilitat | Font de dades |
| :--- | :--- | :--- |
| `useAuthStore` | Token, usuari, rol, headers | Laravel `/api/auth/*` |
| `useHabitStore` | Llista d'hàbits, CRUD via socket | GET Laravel + CUD socket |
| `gameStore` | Estat de joc (XP, nivell UI) | GET + feedback socket |
| `usePlantillaStore` | Plantilles d'hàbits | GET + CUD socket |
| `useFriendshipStore` | Amics, pendents | Laravel REST |
| `useSocialStore` | Posts, comentaris, likes | Laravel REST |
| `useShopStore` | Botiga, inventari | Laravel REST |
| `useClanStore` / `useClanChatStore` | Clans i xat | REST + socket |
| `useChatStore` | Missatges privats | REST + socket |
| `calendar` | Calendari / snapshots | Laravel REST |
| `useSocketUiCallbacks` | Registre de listeners feedback | Socket (no és state de domini) |
| `useModalStore` | Modals globals | UI pura |

---

## 4. Separació crítica: hàbits vs joc

| Store | Què guarda | Per què separat |
| :--- | :--- | :--- |
| `useHabitStore` | Hàbits, completats, metadata | Font única de llista d'hàbits |
| `gameStore` | XP, monedes, nivell per animacions | Evitar barrejar llista amb estat de gamificació transitòria |

**Pregunta típica del professor:** *"Per què dos stores?"*  
Perquè un canvi d'hàbit no ha de provocar re-render innecessari de tot el dashboard de joc, i viceversa.

---

## 5. Com es comunica amb el backend

### Lectura (GET)

```javascript
var resposta = await authFetch("/api/friends");
// Actualitza this.friends al store
```

### Escriptura (CUD via socket)

```javascript
// El component emet per socket; el store escolta feedback
useSocketUiCallbacks().registrarHabitConfirmed(function (payload) {
  habitStore.aplicarFeedback(payload);
});
```

**Patró:** Emit des del component o composable → confirmació al store via callback registrat.

---

## 6. Exemple: flux d'amistats (només REST)

`useFriendshipStore.js`:

1. `fetchFriendsList()` → `GET /api/friends`
2. `sendFriendRequest(id)` → `POST /api/friends/request` + opcional `socket.emit` notificació
3. `removeFriend(friendshipId)` → `DELETE /api/friends/{id}`

No passa per cua Redis perquè és operació lleugera i síncrona.

---

## 7. Exemple: flux d'hàbits (REST + socket)

1. **Montatge pàgina:** `habitStore.carregarHabits()` → GET Laravel
2. **Completar hàbit:** `socket.emit('habit_completed', ...)`
3. **Feedback:** callback actualitza `habitStore` i `gameStore` amb XP nou

---

## 8. Convencions de codi als stores

| Norma | Detall |
| :--- | :--- |
| ES5 al frontend social/hàbits | `var`, `function()`, sense arrows (coherència amb agents) |
| `defineStore('id', { state, actions })` | API Options per stores |
| Errors | `this.error = e.message`; re-lançar si cal modal |
| Deduplicació | Funcions com `dedupeById` per llistes |

---

## 9. Pinia vs Composables

| Pinia | Composable (`composables/`) |
| :--- | :--- |
| Estat **global** persistent entre pàgines | Lògica **reutilitzable** sense necessàriament state global |
| Auth, hàbits, social | `useApi`, `useSocketBridge`, `useHomeSocketUi` |

**Regla:** Si més de dues pàgines necessiten les mateixes dades → store Pinia. Si és lògica d'una feature → composable.

---

## 10. Preguntes freqüents dels professors

**P: És com Redux?**  
R: Concepte similar (estat global centralitzat), però més integrat amb Vue (reactivitat nativa, sense reducers boilerplate).

**P: On es persisteix l'estat?**  
R: A memòria del navegador. La persistència real és PostgreSQL; en recarregar, es tornen a fer GET.

**P: Com eviteu inconsistències socket vs API?**  
R: GET sempre des de Laravel com a font de veritat; el socket només actualitza després de confirmació del worker.

---

## 11. Referències internes

- `frontend/stores/`
- `.cursor/rules/frontend-architecture.mdc`
- [nuxt.md](./nuxt.md), [sockets.md](./sockets.md)
