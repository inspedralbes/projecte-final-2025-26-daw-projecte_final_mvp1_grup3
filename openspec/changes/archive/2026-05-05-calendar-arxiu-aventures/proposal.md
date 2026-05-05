## Why

L'usuari no té cap manera de revisar el seu historial de progrés: quins hàbits va completar, quantes monedes va guanyar o com era la seva mascota en una data concreta. Implementar el Calendari (Arxiu d'Aventures) dona a Loopy una capa de narrativa personal que reforça la motivació a llarg termini, convertint el registre diari en un "àlbum de viatge" immutable.

## What Changes

- **Nou sistema de Snapshot diari**: Una tasca programada (cron 23:59) captura l'estat de l'usuari cada dia i el desa a la taula `DAILY_SNAPSHOTS` (progrés de gamificació, hàbits actius + estat de completació, economia del dia).
- **Nova pàgina mensual** (`/calendar`): Grid de 7 columnes amb cel·les de dia que mostren indicadors de categories completades. Punt d'entrada des del header de la Home.
- **Nova pàgina diària** (`/calendar/day`): Rèplica de la Home en mode "mirror del snapshot" —mascota amb el progrés d'aquell dia, llista d'hàbits en mode només lectura, sense ruleta ni missió diària.
- **Indicador temporal contextual al header diari**: mostra "Avui/Hoy/Today" per avui, "Ahir/Ayer/Yesterday" per ahir i `DiaSetmana DD/MM` per dies anteriors.
- **Modal de detalls d'hàbit historial**: SweetAlert2 que mostra la recompensa real (XP + monedes segons la dificultat del dia) i metadata d'API (portada de llibre, miniatura YouTube, etc.) tal com estava el dia del snapshot.
- **Filtratge per categoria** a la vista diària: selector desplegable que amaga/mostra hàbits per categoria amb animació suau.
- **Icona d'accés al calendari** al header de la Home: div circular claymorphism a la dreta dels comptadors de nivell.
- **Color a `CATEGORIES`**: nou camp `color` per representar els indicadors al grid mensual.

## Capabilities

### New Capabilities

- `daily-snapshot`: Captura automàtica diària a les 23:59 de l'estat de l'usuari (gamificació, hàbits, economia). Inclou la taula `DAILY_SNAPSHOTS`, el Laravel Command/cron, la cua Redis i el worker Node.js. Afegeix `color` a `CATEGORIES` i `metadata` a `HABITS` (al SQL).
- `calendar-view`: Sistema de vistes del calendari (mensual i diària) que recupera i renderitza snapshots des de l'API Laravel. Inclou la navegació temporal, el punt d'entrada a la Home i les regles de només lectura.
- `habit-history-detail`: Modal de detalls d'un hàbit en una data passada, mostrant la recompensa real (XP + monedes segons dificultat del dia) i la metadata d'API desada al snapshot. Inclou el filtratge per categoria a la vista diària.

### Modified Capabilities

_(Cap especificació existent canvia de requisits.)_

## Impact

**Schema PostgreSQL (`database/init.sql`)**
- Nova taula `DAILY_SNAPSHOTS` amb camps: `id`, `usuari_id` (FK USUARIS), `data`, `mascota_json` (JSONB), `habits_json` (JSONB), `economia_json` (JSONB), `created_at`, `UNIQUE(usuari_id, data)`.
- Nou camp `color VARCHAR(20)` a `CATEGORIES`.
- Nou camp `metadata JSONB` a `HABITS` (sincronitzar amb el model Eloquent existent).
- Seeds inicials de colors a `database/insert.sql`.

**Backend Laravel**
- Nou model `DailySnapshot`.
- Nou `SnapshotService` amb lògica de captura (consulta `USUARIS`, `HABITS`, `USUARIS_HABITS`, `REGISTRE_ACTIVITAT`).
- Nou `SnapshotRunCommand` (`snapshot:run --date=`) programat a `routes/console.php` amb `Schedule::command('snapshot:run')->dailyAt('23:59')` (Laravel 11).
- Nou `CalendarController` amb endpoints (sense middleware d'auth, segons regla del projecte):
  - `GET /api/calendar/snapshot/{usuariId}/{date}`
  - `GET /api/calendar/month/{usuariId}/{year}/{month}`

**Backend Node.js**
- Nou scheduler que publica a `queue:snapshot` a les 23:59.
- `UnifiedRedisWorker.php` (o equivalent) amplia el processament per executar `snapshot:run` quan rebi missatges de la cua.

**Backend Redis**
- Nova cua `queue:snapshot` per encuar el job nocturn.

**Frontend Nuxt**
- Noves pàgines: `pages/calendar/index.vue` (mensual), `pages/calendar/day.vue` (diària).
- Nous components: `CalendarMonthGrid`, `CalendarDayCell`, `HabitHistoryCard`.
- Nou composable `useCalendar.js` (navegació temporal, helpers de format i navegació per gestos a la vista diària).
- Nou store Pinia `useCalendarStore` (snapshot actiu, mes/dia seleccionat, filtre categoria, cache).
- Modificació de `pages/home.vue`: icona d'entrada al calendari al header.
- Modificació del component de mascota existent: afegir prop `readonly` per al mode mirall.

**Dependències**
- SweetAlert2 (ja present al projecte) per al modal de detalls.

## Out of Scope (deferits a iteracions futures)

- Personalització visual de la mascota al snapshot (`color_plastilina`, `objectes_equipats[]`): no existeix taula `GAME_STATE`; la versió MVP del calendari renderitza la mascota amb el progrés numèric (nivell, XP) i un aspecte estandarditzat.
- Snapshots retroactius previs a la data de desplegament.
- Persistència de la cache del store Pinia entre sessions (`localStorage`).
- Política de retenció (purge de snapshots antics).
