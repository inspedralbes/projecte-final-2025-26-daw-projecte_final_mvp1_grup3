# Loopy — Presentació Tècnica (Contingut per diapositives)

> **Fitxer de referència:** `tecnica_2425_Loopy.pdf`  
> **Projecte:** Loopy — *El Teu Habit Loop Gamificat*  
> **Descripció en una frase:** Plataforma web de seguiment d'hàbits amb gamificació, mascota evolutiva, social (fòrum, amics, clans), botiga virtual i integració d'APIs externes (llibres, exercicis, vídeos, clima).

---

## Guia d'estil visual (Canva / PowerPoint)

### Paleta de colors (HEX)

| Rol | Nom | HEX | Ús |
| :--- | :--- | :--- | :--- |
| **Primari** | Loopy Green | `#79D45D` | Botons, accents actius, ticks, barres de progrés |
| **Primari fosc** | Green Dark | `#5CB83A` | Hover, gradients (portada) |
| **Primari clar** | Green Soft | `#ECFDF3` | Fons de targetes completades, badges |
| **Primari vora** | Green Border | `#BBF7D0` | Vores d'èxit / missió completada |
| **Text principal** | Ink | `#2B2D42` | Títols i cos de text |
| **Text secundari** | Muted | `#707070` | Subtítols, peus de diapositiva |
| **Text suau** | Light Gray | `#949494` | Labels, metadades |
| **Fons clar** | Off White | `#FAF9F9` | Fons de diapositives de contingut |
| **Fons secció** | Section BG | `#F7F7F7` | Caixes, columnes |
| **Accent lila** | Habit Purple | `#A855F7` | Icones categoria estudi |
| **Accent blau** | Sky | `#94BEF0` | Clima, elements informatius |
| **Accent coral** | Alert | `#FF6B8A` | Errors, alertes (ús moderat) |
| **Admin actiu** | Admin Green | `#79D45D` | Sidebar admin (item actiu) |
| **Blanc** | White | `#FFFFFF` | Targetes, sidebar admin |
| **Gradient portada** | — | `linear-gradient(135deg, #79D45D 0%, #94BEF0 50%, #ECFDF3 100%)` | Portada i portades de secció |

### Tipografies

| Ús | Font | Pes | Mida recomanada (diapositiva 16:9) |
| :--- | :--- | :--- | :--- |
| **Títol principal** | [Bricolage Grotesque](https://fonts.google.com/specimen/Bricolage+Grotesque) | 700–800 | 44–56 pt |
| **Títol de secció** | Bricolage Grotesque | 700 | 36–42 pt |
| **Subtítol** | Bricolage Grotesque | 600 | 24–28 pt |
| **Cos / bullets** | [Comfortaa](https://fonts.google.com/specimen/Comfortaa) | 400–600 | 18–22 pt |
| **Peu de pàgina** | Comfortaa | 400 | 12–14 pt |
| **Codi / tècnic** | `JetBrains Mono` o `Consolas` | 400 | 14–16 pt |

> **Alternativa si no carregues fonts:** Montserrat (títols) + Inter (cos) — molt proper a l'estètica actual.

### Espaiat i layout (16:9 — 1920×1080 px)

| Element | Valor |
| :--- | :--- |
| Marges laterals | 80–96 px |
| Marge superior (sota capçalera) | 64 px |
| Marge inferior (peu) | 48 px |
| Espai entre títol i contingut | 32 px |
| Espai entre bullets | 12–16 px |
| Radi de targetes | 16–24 px |
| Ombra de targetes | `0 8px 24px rgba(43, 45, 66, 0.08)` |
| Icona en bullet | 24–28 px, color `#79D45D` |
| Logo Loopy (portada) | ~180–220 px d'alçada |

### Regles visuals

- Fons majoritàriament **clar** (`#FAF9F9`); evitar fons negre complet (no és l'estètica de la web).
- Una sola accent color per diapositiva: verd Loopy + un secundari màxim.
- Icones **Lucide** (traç 2px, cantonades arrodonides) en verd o gris fosc.
- Diagrama d'arquitectura: caixes blanques amb vora `#E8E8E8`, fletxes `#79D45D`.
- Admin (si cal): sidebar blanc, text `#2B2D42`, actiu `#79D45D` sobre fons `#ECFDF3`.

---

## Llistat de diapositives

---

### DIAPOSITIVA 1: Portada

- **Títol:** Loopy — Documentació Tècnica
- **Subtítol:** Curs 2025-26 · Projecte Final de Cicle · 2n DAW
- **Tagline (opcional):** El Teu Habit Loop Gamificat
- **Elements:**
  - Logo Loopy (centre)
  - Integrants (baix esquerra): Biel Domínguez · Llorenç Carnisser · Iker Mata · Iker Lopez
  - Logo Institut Pedralbes (baix dreta)
- **Icona decorativa:** `sparkles` (Lucide), verd suau
- **Estil:** Gradient `135deg` (#79D45D → #94BEF0 → #ECFDF3); text títol en `#2B2D42`

---

### DIAPOSITIVA 2: Índex (full de ruta)

- **Títol:** Índex
- **Contingut (numerat):**
  1. L'ADN tècnic de Loopy (arquitectura i stack)
  2. Evolució de les funcionalitats (sprints)
  3. Problemes i solucions (reptes reals)
  4. Aspectes tècnics i seguretat (desplegament)
- **Icona lateral:** `list-ordered` (Lucide)
- **Peu:** Grup 3 — Institut Pedralbes

---

## SECCIÓ 1: L'ADN TÈCNIC DE LOOPY

---

### DIAPOSITIVA 3: Portada Secció 1

- **Número:** 01
- **Títol:** Què és Loopy?
- **Subtítol:** Arquitectura, stack i patrons de disseny
- **Icona de fons (gran, opacitat 8%):** `layers` o `network`
- **Estil:** Banda lateral verda `#79D45D` (8 px) + fons `#FAF9F9`

---

### DIAPOSITIVA 4: Proposta de valor (context)

- **Títol:** Què resol Loopy?
- **Bullets:**
  - Seguiment d'hàbits amb el model **Habit Loop** (senyal → rutina → recompensa)
  - **Gamificació:** XP, monedes, ratxes, missions diàries, ruleta
  - **Mascota** que reacciona al progrés de l'usuari
  - **Social:** fòrum, amics, clans i xat
  - **Onboarding amb IA** (Google Gemini) per suggerir hàbits inicials
- **Icones:** `repeat` · `trophy` · `heart` · `users` · `bot`

---

### DIAPOSITIVA 5: Arquitectura en 3 capes

- **Títol:** Arquitectura modular
- **Diagrama (3 blocs + fletxes):**

```text
[Nuxt 3 · :3000]  ──GET JWT──►  [Laravel 11 · :8000] ──► PostgreSQL 16
       │                              ▲
       │ Socket.io                    │ Redis cues
       ▼                              │
[Node 20 · :3001] ◄────Redis 7────► [Worker Laravel]
```

- **Bullets sota el diagrama:**
  - **GET** → API REST Laravel (lectura síncrona)
  - **CUD** → Node → Redis → Worker → feedback per socket
- **Icones:** `monitor-smartphone` · `server` · `database` · `radio`

---

### DIAPOSITIVA 6: El Stack Tecnològic

- **Títol:** Stack tecnològic
- **Layout:** Graella 2×3 amb logos + icona + una línia per tecnologia

| Capa | Tecnologia | Icona Lucide |
| :--- | :--- | :--- |
| **Frontend** | Nuxt 3 · Vue 3 · Pinia · Tailwind CSS · i18n (ca/es/en) | `layout-template` |
| **API REST** | Laravel 11 · PHP 8.3 · JWT | `box` |
| **Temps real** | Node.js 20 · Socket.io 4.7 | `zap` |
| **Base de dades** | PostgreSQL 16 | `database` |
| **Missatgeria** | Redis 7 (cues LPUSH/BRPOP + Pub/Sub) | `layers` |
| **IA** | Google Gemini (onboarding) | `brain` |
| **Infra** | Docker · Docker Compose · GitHub Actions | `container` · `git-branch` |
| **Tests** | PHPUnit · Vitest · Playwright · Cypress | `test-tube` |

- **Nota al peu:** Capacitor 6 previst per distribució mòbil nativa

---

### DIAPOSITIVA 7: Patrons de disseny

- **Títol:** Patrons aplicats
- **Bullets (2 columnes):**
  - `git-branch` **CQRS lleuger** — GET HTTP / CUD per cua
  - `boxes` **Service Layer** — lògica fora dels controladors
  - `radio` **Event-driven** — Redis Pub/Sub → Socket.io
  - `folder-tree` **Dominis** — `Domains/*` (Laravel i Node)
  - `package` **Pinia Stores** — estat global al client
  - `shield` **Middleware JWT** — `ensure.user` / `ensure.admin`

---

### DIAPOSITIVA 8: Flux d'una acció (exemple completar hàbit)

- **Títol:** Exemple: completar un hàbit
- **Passos (timeline vertical, 5 nodes):**
  1. `mouse-pointer-click` Usuari prem **+** a la Home
  2. `wifi` Socket emet `habit_action` → cua Redis
  3. `cog` Worker Laravel processa i persisteix
  4. `message-circle` Feedback a `user_{id}` via Socket
  5. `check-circle` UI actualitza XP, ratxa i missió
- **Color dels nodes:** verd `#79D45D` quan OK

---

## SECCIÓ 2: EVOLUCIÓ I SPRINTS

---

### DIAPOSITIVA 9: Portada Secció 2

- **Número:** 02
- **Títol:** Evolució de les funcionalitats
- **Subtítol:** Del MVP al producte final
- **Icona de fons:** `trending-up`

---

### DIAPOSITIVA 10: Timeline de Sprints

- **Títol:** Roadmap de desenvolupament
- **Línia de temps horitzontal (5 fases):**

| Fase | Nom | Entregables clau | Icona |
| :--- | :--- | :--- | :--- |
| **Sprint 0** | MVP | Auth JWT · Docker · PostgreSQL · Home bàsica · Socket base | `flag` |
| **Sprint 1** | Core hàbits | CRUD hàbits · Progrés diari · Gamificació (XP, ratxes) · Plantilles | `check-circle` |
| **Sprint 2** | Experiència | Mascota · Botiga · Calendari · Ruleta · Mode focus · i18n | `check-circle` |
| **Sprint 3** | Social & Admin | Fòrum · Amics · Clans · Panell admin · Moderació | `check-circle` |
| **Sprint 4** | Qualitat & deploy | APIs externes · Índexs BD · CI/CD · Tests E2E · Refactor dominis | `loader` (en curs) |

- **Visual:** Línia `#79D45D`; punts completats amb `check-circle` verd

---

### DIAPOSITIVA 11: Funcionalitats per domini

- **Títol:** Mapa de funcionalitats
- **Bullets (icona per fila):**
  - `home` Dashboard, missió diària, ruleta
  - `list-checks` Hàbits, plantilles, focus
  - `calendar` Arxiu d'aventures (calendari)
  - `shopping-bag` Botiga i inventari (skins, consumibles)
  - `users` Social, amics, clans, WebRTC (senyalització)
  - `shield` Admin: usuaris, hàbits, reports, logs
  - `cloud` Proxies: Google Books, Wger, YouTube, OpenWeather

---

### DIAPOSITIVA 12: Gestió del projecte

- **Títol:** Eines de gestió
- **Bullets:**
  - `kanban` **Taiga** — backlog i timeline
  - `figma` **Figma** — disseny UI (soft design / clay)
  - `github` **GitHub** — `feature/*` → PR → `main`
  - `file-text` Docs: `E_DOCUMENTACIO_TECNICA.md`, manual d'usuari
- **QR opcional:** enllaç Taiga o repo (diapositiva petita)

---

## SECCIÓ 3: REPTES TÈCNICS I SOLUCIONS

---

### DIAPOSITIVA 13: Portada Secció 3

- **Número:** 03
- **Títol:** Problemes i solucions
- **Subtítol:** Aprenentatges del desenvolupament real
- **Icona de fons:** `wrench`

---

### DIAPOSITIVA 14: Repte A — Arquitectura GET/CUD

- **Layout:** Problema (esquerra, coral suau) → Solució (dreta, verd)

| | Contingut | Icona |
| :--- | :--- | :--- |
| **Problema** | UI bloquejada en operacions lentes; inconsistència si es fa CUD directe per HTTP | `triangle-alert` |
| **Solució** | Patró **Node → Redis → Worker Laravel → Socket**; UI optimista + confirmació | `lightbulb` |

- **Bullet extra:** Desacobla lectura (ràpida) d'escriptura (async)

---

### DIAPOSITIVA 15: Repte B — Lentitud API READ

- **Problema:** Endpoints GET lents (dashboard, hàbits, admin) per **falta d'índexs** a PostgreSQL | `gauge`
- **Solució:** Índexs a `usuari_id`, `habit_id`, `data`, `created_at`; optimitzar `GamificationService` | `database-zap`
- **Impacte:** Full table scans eliminats en taules grans

---

### DIAPOSITIVA 16: Repte C — Estat i sincronització UI

- **Problema:** Hàbit completat a pantalla però no persistit; missió diària sense actualitzar | `bug`
- **Solució:** Completar via API + sync `game-state`; normalitzador de dificultat (`mitja`/`media`); timezone `Europe/Madrid` | `refresh-cw`

---

### DIAPOSITIVA 17: Repte D — Integracions externes

- **Problema:** Claus API al client; timeouts i rate limits (YouTube, Books…) | `key-round`
- **Solució:** **Proxy Laravel** (`/api/external/*`); `metadata` JSONB normalitzat; fallback manual al formulari | `shield-check`

---

### DIAPOSITIVA 18: Repte E — Refactor i mantenibilitat

- **Problema:** Serveis monolítics (>1000 línies); imports trencats post-refactor | `file-warning`
- **Solució:** Carpeta `Domains/*` (Habits, Shop, AI, Plantilla…); controladors prims; convencions ES5 (Node) / PHP 8.3 | `folder-cog`

---

## SECCIÓ 4: REQUISITS I SEGURETAT

---

### DIAPOSITIVA 19: Portada Secció 4

- **Número:** 04
- **Títol:** Aspectes tècnics i seguretat
- **Subtítol:** Requisits, desplegament i compliment
- **Icona de fons:** `lock`

---

### DIAPOSITIVA 20: Seguretat i autenticació

- **Títol:** Seguretat de l'aplicació
- **Bullets:**
  - `lock` **JWT HS256** compartit Laravel + Node; refresh i blacklist
  - `user-check` Middleware `ensure.user` / `ensure.admin`
  - `ban` Usuaris prohibits (`account_banned`) + moderació admin
  - `key` Contrasenyes **bcrypt**; claus API només al `.env` servidor
  - `globe-lock` CORS restringit en producció
  - `chrome` OAuth Google (Socialite) opcional

---

### DIAPOSITIVA 21: Desplegament i CI/CD

- **Títol:** Desplegament
- **Bullets:**
  - `container` **Docker Compose** — mateix stack dev/prod
  - `rocket` **GitHub Actions:** lint → test → SSH deploy → health check
  - `tag` Tags semàntics automàtics `vX.Y.Z`
  - `server` Serveis: Nuxt, Laravel, Node, Postgres, Redis, worker
- **Pipeline visual (4 caixes):** Push → Lint/Build → PHPUnit → Deploy

---

### DIAPOSITIVA 22: Requisits i qualitat

- **Títol:** Qualitat i proves
- **Bullets:**
  - `test-tube` PHPUnit (API), Vitest (unit frontend)
  - `play` Playwright / Cypress (E2E)
  - `paintbrush` Laravel Pint (format PHP)
  - `smartphone` Disseny responsive (430×932 mòbil · desktop admin)
- **Taula BD:** `database/init.sql` versionat (sense dependre només de migracions)

---

### DIAPOSITIVA 23: Disponibilitat i recursos

- **Títol:** On trobar Loopy
- **Bullets:**
  - `globe` **Demo local:** http://localhost:3000 (Docker)
  - `github` **Repositori:** `inspedralbes/projecte-final-2025-26-daw-projecte_final_mvp1_grup3`
  - `book-open` **Documentació:** `doc/E_DOCUMENTACIO_TECNICA.md`
  - `layout` **Figma:** enllaç al disseny UI
- **Nota:** Inserir URL de producció quan estigui activa

---

### DIAPOSITIVA 24: Cloenda i Q&A

- **Títol:** Gràcies
- **Subtítol:** Preguntes?
- **Elements:**
  - Logo Loopy (centre, petit)
  - QR → repositori GitHub o demo
  - Text: *Loopy — Transforma els teus hàbits en una aventura*
- **Icona:** `message-circle-question` o `heart`
- **Contacte (opcional):** correus del grup / centre

---

## Annex: Icones Lucide recomanades (resum)

| Concepte | Nom Lucide |
| :--- | :--- |
| Índex | `list-ordered` |
| Arquitectura | `layers`, `network` |
| Frontend | `layout-template` |
| Backend | `box`, `server` |
| Node / Socket | `zap`, `radio` |
| Base de dades | `database` |
| Redis | `layers` |
| Docker | `container` |
| Seguretat | `lock`, `shield` |
| Deploy | `rocket` |
| Problema | `triangle-alert` |
| Solució | `lightbulb` |
| Bug | `bug` |
| Èxit | `check-circle` |
| IA | `brain`, `bot` |
| Gamificació | `trophy`, `coins` |
| Social | `users` |
| Admin | `shield` |

> A Canva: cerca "Lucide" o importa SVG des de [lucide.dev](https://lucide.dev). A PowerPoint: plugin *Icons by Icons8* o SVG exportats.

---

## Annex: Checklist abans de presentar

- [ ] Substituir URL de demo/producció si ja està publicada
- [ ] Verificar noms dels integrants
- [ ] Afegir logo escola i logo Loopy en alta resolució
- [ ] Revisar que el PDF segueix l'índex del professorat (`tecnica_2425_Loopy.pdf`)
- [ ] Provar llegibilitat des de fons de classe (contrast mínim 4.5:1)
- [ ] Duració orientativa: 12–15 min (≈1 min per diapositiva de contingut)

---

*Document preparat per a la defensa del Projecte Final de Cicle — Loopy — Grup 3 — Curs 2025-26.*
