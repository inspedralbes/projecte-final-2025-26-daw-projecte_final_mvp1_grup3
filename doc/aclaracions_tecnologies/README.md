# Aclaracions per tecnologia — Loopy

Documents preparats per **defenses, entrevistes amb professors i revisió del projecte**. Cada fitxer explica:

- Què fa la tecnologia al projecte
- Quin **model d'arquitectura** seguim
- **Com es comunica** amb la resta de la stack
- **Per què** hem estructurat el codi d'aquesta manera
- Preguntes freqüents típiques

---

## Índex de documents

| Fitxer | Tecnologia | Tema principal |
| :--- | :--- | :--- |
| [laravel.md](./laravel.md) | Laravel 11 / PHP 8.3 | API REST, MVC, Actions, worker Redis |
| [node.md](./node.md) | Node.js 20 | Socket.io, dominis, pont Redis |
| [nuxt.md](./nuxt.md) | Nuxt 3 / Vue 3 | SPA, pages, composables, i18n |
| [pinia.md](./pinia.md) | Pinia | Estat global, stores per domini |
| [sockets.md](./sockets.md) | Socket.io | Temps real, GET vs CUD, esdeveniments |
| [redis.md](./redis.md) | Redis 7 | Cues LPUSH/BRPOP, Pub/Sub feedback |
| [postgresql.md](./postgresql.md) | PostgreSQL 16 | Model de dades, init.sql |
| [docker.md](./docker.md) | Docker Compose | Contenidors, ports, desplegament |
| [jwt.md](./jwt.md) | JWT | Autenticació compartida Laravel + Node |
| [tailwind.md](./tailwind.md) | Tailwind CSS | Estils utility-first |

---

## Diagrama global (resum)

```text
┌─────────────┐  GET+JWT   ┌──────────────┐     ┌─────────────┐
│  Nuxt 3     │───────────►│ Laravel :8000│────►│ PostgreSQL  │
│  Pinia      │            └──────┬───────┘     └─────────────┘
└──────┬──────┘                   │
       │ Socket.io                │ BRPOP / PUBLISH
       ▼                          ▼
┌─────────────┐  LPUSH/SUB  ┌──────────────┐
│ Node :3001  │◄───────────►│    Redis     │
└─────────────┘             └──────────────┘
```

**Regla clau:** GET → Laravel | CUD pesat → Node → Redis → Laravel → feedback socket

---

## Documentació relacionada

- [E_DOCUMENTACIO_TECNICA.md](../E_DOCUMENTACIO_TECNICA.md) — Document oficial Apartat E
- [arquitectura_backend/](../arquitectura_backend/) — Detall Redis i estructura
- [I_MANUAL_USUARI.md](../I_MANUAL_USUARI.md) — Manual d'usuari

---

*Loopy — Projecte final 2n DAW 2025-26, Grup 3*
