# PostgreSQL — Aclaracions tècniques (Loopy)

> Persistència relacional: esquema, convencions i relació amb Laravel.

---

## 1. Què és i per què l'hem triat

| Aspecte | Detall |
| :--- | :--- |
| **Motor** | PostgreSQL 16 |
| **Rol** | Única base de dades relacional del projecte |
| **Accés** | Només des de Laravel (Eloquent / Query Builder) |

**Per què PostgreSQL?** JSONB per metadata d'hàbits, integritat referencial, adequat per projectes DAW amb model relacional ric.

---

## 2. Model de dades

### Gestió de l'esquema

| Fitxer | Funció |
| :--- | :--- |
| `database/init.sql` | CREATE TABLE, índexs, constraints |
| `database/insert.sql` | Dades inicials (seed) |

**Important:** No utilitzem migracions Laravel com a font principal de l'esquema (norma del projecte). Els canvis de BD es documenten i apliquen via `init.sql`.

### Convenció de noms

- Taules: **MAJÚSCULES** (`USUARIS`, `HABITS`, `FRIENDSHIPS`)
- Models Laravel: `$table = 'USUARIS'`
- `$timestamps = false` quan la taula no té `created_at`/`updated_at`

---

## 3. Dominis principals (entitats)

| Domini | Taules clau |
| :--- | :--- |
| Identitat | `USUARIS`, `ADMINISTRADORS` |
| Hàbits | `HABITS`, `USUARIS_HABITS`, `CATEGORIES`, `REGISTRE_ACTIVITAT` |
| Gamificació | `RATXES`, `MISSIOS_DIARIES`, `LOGROS_MEDALLES`, `USUARIS_LOGROS` |
| Plantilles | `PLANTILLES`, `PLANTILLA_HABIT` |
| Social | `SOCIAL_POSTS`, `SOCIAL_COMMENTS`, `SOCIAL_LIKES` |
| Amistat | `FRIENDSHIPS`, `PRIVATE_MESSAGES` |
| Clans | `CLANS`, `CLAN_MEMBERS`, `CLAN_MESSAGES` |
| Botiga | `BOTIGA_ITEMS`, `USUARIS_ITEMS` |
| Calendari | `DAILY_SNAPSHOTS` |
| Moderació | `REPORTS`, `REPORTS_USUARI` |

Diagrama E/R: `doc/img_documentació/img_documentació_tecnica/model_er.png`

---

## 4. Camps rellevants per als professors

### Gamificació

- `USUARIS.nivell`, `xp_total`, `monedes`
- `HABITS.dificultat` → XP: fàcil 100, mitjà 250, difícil 400
- `RATXES.ratxa_actual`, `ratxa_maxima`

### Hàbits amb APIs externes

- `HABITS.metadata` (JSONB): llibres, exercicis Wger, vídeos YouTube, etc.

### Moderació

- `USUARIS.prohibit`, `motiu_prohibicio`, `dies_prohibicio`

---

## 5. Com accedeix cada tecnologia

| Tecnologia | Accés a PostgreSQL |
| :--- | :--- |
| **Laravel** | Sí (Eloquent) |
| **Node.js** | **No** (mai connecta directament) |
| **Frontend** | **No** (només via API Laravel) |
| **Redis** | No (dades temporals) |

---

## 6. Relacions típiques

```text
USUARIS 1──N USUARIS_HABITS N──1 HABITS
USUARIS 1──N FRIENDSHIPS (requester / addressee)
USUARIS 1──N SOCIAL_POSTS
CLANS 1──N CLAN_MEMBERS N──1 USUARIS
```

`FRIENDSHIPS` té `UNIQUE(requester_id, addressee_id)` i `status`: `pending`, `accepted`, `rejected`.

---

## 7. Cerca amb accents (PostgreSQL)

Per cerques d'usuaris o text: usar `unaccent()` o `ILIKE` — a PostgreSQL "À" ≠ "A".

---

## 8. Docker i ports

| Servei | Port host |
| :--- | :--- |
| PostgreSQL | `5433` → `5432` intern |
| Adminer | `8081` (gestor visual BD) |

Credencials per defecte: veure `.env.example` (`DB_DATABASE=loopy_db`).

---

## 9. Preguntes freqüents dels professors

**P: Per què no migracions Laravel?**  
R: Decisió de projecte: esquema versionat en SQL clar per revisió i entrega acadèmica; evita divergències entre entorns.

**P: Node escriu a la BD?**  
R: No. Tota escriptura passa pel worker Laravel després de processar la cua Redis.

**P: Com inicialitzeu la BD en Docker?**  
R: Volum + scripts `init.sql` i `insert.sql` al primer arranc del contenidor Postgres.

---

## 10. Referències internes

- `database/init.sql`, `database/insert.sql`
- `.cursor/rules/database-models.mdc`
- [laravel.md](./laravel.md)
