# JWT (Autenticació) — Aclaracions tècniques (Loopy)

> Com identifiquem usuaris entre frontend, Laravel i Node.

---

## 1. Què és JWT al projecte

| Aspecte | Detall |
| :--- | :--- |
| **Tipus** | JSON Web Token (Bearer) |
| **Llibreria Laravel** | `tymon/jwt-auth` o equivalent del projecte |
| **Secret compartit** | `JWT_SECRET` al `.env` (Laravel **i** Node) |

Un sol token identifica l'usuari a:

- Peticions HTTP → Laravel
- Handshake Socket.io → Node

---

## 2. Flux d'autenticació

```text
1. Login (email/password o Google OAuth) → Laravel
2. Laravel retorna access_token (+ refresh si aplica)
3. Frontend guarda token a useAuthStore (cookie/local segons implementació)
4. authFetch afegeix Authorization: Bearer <token>
5. socket.client.js envia auth: { token } en connectar
```

---

## 3. On es valida el token

| Capa | Fitxer / Middleware |
| :--- | :--- |
| **Laravel API** | `EnsureUserToken`, middleware de rutes `api/user.php` |
| **Node Socket** | `backend-node/src/middleware/jwtAuth.js` |

Després de validar, Laravel exposa `user_id` a la request; Node assigna `socket.data.userId`.

---

## 4. Refresh i errors 401

`authFetch` (`utils/authFetch.js`):

1. Fa la petició amb token actual
2. Si rep **401**, intenta `refrescarSessio()` al store
3. Reintenta la petició un cop

El socket fa el mateix en `connect_error` amb missatge `Authentication required`.

---

## 5. Admin vs usuari

| Rol | Rutes | Socket |
| :--- | :--- | :--- |
| Usuari | `/api/*` (user.php) | Sala `user_{id}` |
| Admin | `/api/admin/*` | `admin_join` + handlers admin |

`useAuthStore.isAdmin` controla UI i emissió `admin_join`.

---

## 6. Per què JWT i no sessions PHP clàssiques?

| Motiu | Detall |
| :--- | :--- |
| **API stateless** | Frontend SPA separat del servidor |
| **Socket.io** | Necessita token al handshake, no cookies de sessió fàcils |
| **Node** | Verifica el mateix token sense cridar Laravel cada cop |

Laravel pot usar Redis per sessions internes, però l'API pública per al client és JWT.

---

## 7. Seguretat (punts per a la defensa)

- `JWT_SECRET` llarg i aleatori (generat amb `crypto.randomBytes`)
- Token **no** es guarda a logs
- HTTPS en producció
- Validació a **cada** petició API i connexió socket
- Usuaris prohibits: comprovació a Laravel abans d'operacions sensibles

---

## 8. Preguntes freqüents dels professors

**P: Node confia en el token sense preguntar a Laravel?**  
R: Sí, verifica la signatura amb el mateix `JWT_SECRET`. Això és estàndard en arquitectures stateless.

**P: Què passa si el token caduca durant una acció?**  
R: `authFetch` i el socket intenten refresh; si falla, redirecció a login.

**P: Google OAuth com encaixa?**  
R: Laravel gestiona el callback OAuth i emet el mateix tipus de JWT per al frontend.

---

## 9. Referències internes

- `backend-laravel/config/jwt.php`
- `frontend/stores/useAuthStore.js`
- `frontend/utils/authFetch.js`
- [laravel.md](./laravel.md), [sockets.md](./sockets.md), [nuxt.md](./nuxt.md)
