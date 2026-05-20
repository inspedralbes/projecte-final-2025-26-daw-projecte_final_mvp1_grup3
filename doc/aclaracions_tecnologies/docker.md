# Docker — Aclaracions tècniques (Loopy)

> Orquestració de serveis per desenvolupament i desplegament.

---

## 1. Per què Docker al projecte

| Motiu | Detall |
| :--- | :--- |
| **Reproducibilitat** | Mateix entorn per tot l'equip i professors |
| **Multi-servei** | 6+ processos (Postgres, Redis, Laravel, Nginx, Node, Nuxt, worker) |
| **Aïllament** | Versions fixades (PHP 8.3, Node 20, PG 16) |

Sense Docker caldria instal·lar manualment cada stack (veure `doc/E_DOCUMENTACIO_TECNICA.md` §2.3).

---

## 2. Arquitectura de contenidors

```text
┌─────────────────────────────────────────────────────────┐
│                    docker-compose                        │
├─────────────┬─────────────┬──────────────┬──────────────┤
│  frontend   │ backend-node│backend-laravel│  postgres  │
│  Nuxt :3000 │ Socket :3001│  API :8000   │   :5433    │
├─────────────┴─────────────┴───────┬──────┴──────────────┤
│              redis :6380          │      adminer :8081 │
│         laravel-redis-worker      │                    │
└─────────────────────────────────────────────────────────┘
```

---

## 3. Serveis principals

| Contenidor | Imatge / Dockerfile | Port | Funció |
| :--- | :--- | :--- | :--- |
| `frontend` | `Dockerfile.frontend` | 3000 | Nuxt SPA |
| `backend-node` | `Dockerfile.node` | 3001 | Socket.io + Redis bridge |
| `backend-laravel` | `Dockerfile.laravel` | 8000 (via Nginx) | API REST |
| `postgres` | oficial PG 16 | 5433 | Base de dades |
| `redis` | oficial Redis 7 | 6380 | Cues + Pub/Sub + cache |
| `backend-laravel-redis-worker` | mateixa imatge Laravel | — | `php artisan redis:unified-worker` |
| `adminer` | opcional | 8081 | UI per consultar BD |

---

## 4. Xarxa i comunicació interna

Dins Docker, els serveis es resolen per **nom de servei**:

| Variable | Valor dins Docker |
| :--- | :--- |
| `DB_HOST` | `postgres` |
| `REDIS_HOST` | `redis` |
| `API_URL` (frontend) | `http://backend-laravel` o URL pública |
| `SOCKET_URL` | `http://backend-node:3001` |

Al `.env` de l'host (navegador a `localhost`), les URLs públiques apunten a ports exposats:

```env
API_URL=http://localhost:8000
SOCKET_URL=http://localhost:3001
```

---

## 5. Volums i persistència

- **Postgres:** volum per conservar dades entre `docker compose down` (sense `-v`)
- **`init.sql` / `insert.sql`:** muntats o copiats al primer inici del contenidor BD

Per reiniciar BD des de zero:

```bash
cd docker
docker compose down -v
docker compose up -d --build
```

---

## 6. Fitxer `.env` unificat

Un sol `.env` a l'arrel del repositori alimenta tots els serveis via `docker-compose.yml`:

- `JWT_SECRET` (compartit Laravel + Node)
- Credencials `DB_*`
- Claus APIs opcionals (Gemini, OpenWeather, etc.)

---

## 7. CI/CD

GitHub Actions (`.github/workflows/deploy.yml`): lint, tests i desplegament SSH a servidor de producció.

---

## 8. Comandes útils per als professors

```bash
cd docker
docker compose up -d --build          # Engegar tot
docker compose logs -f backend-node   # Logs Node
docker compose exec backend-laravel php artisan redis:unified-worker
docker compose down                   # Aturar
```

---

## 9. Preguntes freqüents

**P: Es pot desenvolupar sense Docker?**  
R: Sí, executant Laravel, Node, Nuxt i Postgres/Redis locals (documentat a E_DOCUMENTACIO_TECNICA §2.3).

**P: Per què un worker apart?**  
R: El `BRPOP` de Redis és bloquejant; ha de ser un procés de llarga durada separat del servidor web PHP.

**P: On estan els Dockerfiles?**  
R: Carpeta `docker/` a l'arrel del repositori.

---

## 10. Referències internes

- `docker/docker-compose.yml`
- `doc/setup_migracions/01-SETUP-DESDE-CERO.md`
- [laravel.md](./laravel.md), [node.md](./node.md), [redis.md](./redis.md)
