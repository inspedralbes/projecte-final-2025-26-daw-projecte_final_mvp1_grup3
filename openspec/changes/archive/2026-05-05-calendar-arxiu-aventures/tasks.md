## 1. Schema PostgreSQL i seeds (AgentDatabase)

- [x] 1.1 Afegir a `database/init.sql` la taula `DAILY_SNAPSHOTS` amb camps: `id SERIAL PK`, `usuari_id INT REFERENCES USUARIS(id) ON DELETE CASCADE`, `data DATE NOT NULL`, `mascota_json JSONB`, `habits_json JSONB`, `economia_json JSONB`, `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
- [x] 1.2 Afegir constraint `UNIQUE(usuari_id, data)` a `DAILY_SNAPSHOTS` per garantir un snapshot per usuari i dia
- [x] 1.3 Afegir a `database/init.sql` el camp `color VARCHAR(20)` a `CATEGORIES`
- [x] 1.4 Afegir a `database/init.sql` el camp `metadata JSONB` a `HABITS` (actualment només existeix al model Eloquent)
- [x] 1.5 Afegir a `database/insert.sql` un valor de `color` per defecte per a cada categoria existent (hexadecimals coherents amb la paleta del producte)
- [x] 1.6 Regenerar la BD i verificar l'estructura

## 2. Models Eloquent (AgentDatabase + AgentLaravel)

- [x] 2.1 Crear el model `app/Models/DailySnapshot.php` amb `$table = 'daily_snapshots'`, `$timestamps = false` (només `created_at`), `$fillable` complet i `$casts` amb arrays per als 3 camps JSON
- [x] 2.2 Definir la relació `usuari(): BelongsTo` a `User::class` al model `DailySnapshot`
- [x] 2.3 Afegir al model `Categoria` el camp `color` al `$fillable`

## 3. Backend Laravel: SnapshotService (AgentLaravel)

- [x] 3.1 Crear `app/Services/SnapshotService.php` amb el mètode `captureForUser(User $user, string $data): ?DailySnapshot`
- [x] 3.2 Implementar `buildMascotaJson(User $user)`: retorna `['nivell', 'xp_total', 'xp_actual_nivel', 'xp_objetivo_nivel']` directament dels camps de `USUARIS`
- [x] 3.3 Implementar `buildHabitsJson(User $user, string $data)`: query amb `HABITS h JOIN USUARIS_HABITS uh ON uh.habit_id = h.id WHERE uh.usuari_id = ? AND uh.actiu = TRUE`, fent `LEFT JOIN REGISTRE_ACTIVITAT ra ON ra.habit_id = h.id AND DATE(ra.data) = ?` per obtenir `acabado`
- [x] 3.4 Cada element de `habits_json` ha de contenir: `id`, `titol`, `icona`, `color`, `dificultat`, `categoria_id`, `metadata` (nullable) i `acabado` (bool, `false` si no hi ha registre del dia)
- [x] 3.5 Implementar `buildEconomiaJson(User $user, string $data)`: `xp_guanyada_avui = SUM(ra.xp_guanyada)` i `monedes_guanyades_avui = SUM(monedes per dificultat de l'hàbit)` reutilitzant les constants `MONEDES_PER_DIFICULTAT` de `HabitService`
- [x] 3.6 Lògica de no-duplicació: si ja existeix snapshot per `(usuari_id, data)`, retornar el registre existent sense crear-ne un de nou
- [x] 3.7 Afegir mètode `captureForAllUsers(string $data)` que itera `User::where('prohibit', false)->chunk(100, fn($users) => ...)` i crida `captureForUser` per a cadascun

## 4. Backend Laravel: Command i cron (AgentLaravel)

- [x] 4.1 Crear `app/Console/Commands/SnapshotRunCommand.php` (signature `snapshot:run {--date=}`) que cridi `SnapshotService::captureForAllUsers($data)`
- [x] 4.2 Validar que si `--date` és posterior a avui, el Command retorna error i surt amb codi != 0 sense crear cap registre
- [x] 4.3 Si `--date` no s'especifica, usar `Carbon::today()->format('Y-m-d')`
- [x] 4.4 Registrar el schedule a `routes/console.php` amb `Schedule::command('snapshot:run')->dailyAt('23:59')` (Laravel 11)

## 5. Backend Node.js + Redis: scheduler i subscriber (AgentNode + AgentRedis)

- [x] 5.1 Afegir al worker Node.js un scheduler (`node-cron` o equivalent) que publiqui `{event: 'snapshot:run', date: YYYY-MM-DD}` a la cua Redis `snapshot_queue` cada dia a les 23:59
- [x] 5.2 Estendre `UnifiedRedisWorker.php` (o equivalent) perquè processi missatges de `snapshot_queue` cridant `Artisan::call('snapshot:run', ['--date' => $date])`
- [x] 5.3 Verificar manualment el flux complet: forçar publicació a `snapshot_queue` i comprovar que es creen registres a `DAILY_SNAPSHOTS`

## 6. Backend Laravel: API endpoints (AgentLaravel)

- [x] 6.1 Crear `app/Http/Controllers/CalendarController.php` amb mètodes `showDay($usuariId, $data)` i `showMonth($usuariId, $year, $month)`
- [x] 6.2 Implementar `GET /api/calendar/snapshot/{usuariId}/{date}`: retorna 200 amb el JSON complet del snapshot, o 404 amb `{"message": "No snapshot found for this date"}`
- [x] 6.3 Implementar `GET /api/calendar/month/{usuariId}/{year}/{month}`: retorna `[{day, has_snapshot, category_colors}]` per a tots els dies del mes; `category_colors` és l'array de `CATEGORIES.color` únics dels hàbits amb `acabado = true` aquell dia
- [x] 6.4 Registrar les rutes a `routes/api.php` sense middleware d'autenticació (coherent amb la regla del projecte)
- [x] 6.5 Verificació manual via Postman/Thunder Client dels dos endpoints

## Evidència de verificació (2026-05-05)

- Execució de test suite al contenidor `backend-laravel` amb resultats PASS:
  - `tests/Feature/CalendarSnapshotApiTest.php`
  - `tests/Feature/RedisSnapshotPipelineTest.php`
  - `tests/Feature/SnapshotRunCommandTest.php`
  - `tests/Feature/SnapshotSchemaVerificationTest.php`
- Resultat global: **12 tests passed, 197 assertions**.

## Ajust d'abast (2026-05-05)

- El calendari es limita a **vista mensual** (s'elimina la vista setmanal `/calendar/week` i components associats).
- La vista diària incorpora navegació horitzontal per gest:
  - esquerra -> dia següent (sense permetre dates futures),
  - dreta -> dia anterior.

## 7. Frontend: store Pinia i composable (AgentPinia + AgentJavascript)

- [x] 7.1 Crear `stores/calendar.js` (`useCalendarStore`) amb estat: `selectedDate`, `selectedMonth`, `selectedYear`, `selectedWeekStart`, `snapshotCache` (Map keyed per `YYYY-MM-DD`), `monthSummaryCache` (Map keyed per `YYYY-MM`), `categoryFilter`
- [x] 7.2 Afegir accions al store: `fetchMonthSummary(year, month)`, `fetchDaySnapshot(date)`, `setFilter(categoryId)`, `clearFilter()`
- [x] 7.3 Crear `composables/useCalendar.js` amb helpers: `prevMonth()`, `nextMonth()`, `prevWeek()`, `nextWeek()`, `formatDayHeader(date)`, `formatRelativeDayLabel(date, locale)`, `getCompletionRate(habitsJson)`
- [x] 7.4 Les crides al store usen `authFetch` (composable existent) amb la URL `/api/calendar/...` segons la regla d'arquitectura frontend

## 8. Frontend: icona d'accés a la Home (AgentNuxt + AgentTailwind)

- [x] 8.1 Afegir al header de `pages/home.vue` un div circular claymorphism amb icona de calendari, a la dreta dels comptadors de nivell
- [x] 8.2 Vincular el clic a `navigateTo('/calendar')` amb transició suau de Nuxt

## 9. Frontend: vista mensual `/calendar` (AgentNuxt + AgentTailwind)

- [x] 9.1 Crear `pages/calendar/index.vue` que carregui el mes actual via `useCalendarStore.fetchMonthSummary`
- [x] 9.3 Crear `components/user/calendar/CalendarMonthGrid.vue`: grid de 7 columnes (Dll–Dg), capçalera amb mes/any i botons `<` / `>`
- [x] 9.4 Crear `components/user/calendar/CalendarDayCell.vue`: número del dia + cercles de colors (`category_colors`); cel·la buida si `has_snapshot: false`
- [x] 9.5 Implementar el clic a una cel·la → `navigateTo('/calendar/day?date=YYYY-MM-DD')`
- [x] 9.6 Implementar els botons `<` / `>` per canviar de mes
- [x] 9.7 Afegir botó "Tornar" que navega a `/home`

## 11. Frontend: vista diària `/calendar/day` (AgentNuxt + AgentTailwind)

- [x] 11.1 Crear `pages/calendar/day.vue` que llegeixi `?date` i carregui el snapshot via `useCalendarStore.fetchDaySnapshot`
- [x] 11.2 Reutilitzar el component de mascota existent passant prop `readonly: true` i `snapshotData: mascota_json`
- [x] 11.3 Afegir la prop `readonly` al component de mascota per desactivar botons d'interacció quan és `true`
- [x] 11.4 Crear `components/user/calendar/HabitHistoryCard.vue`: títol, icona, color, indicador `acabado` i botó "detalls"; sense botons de marcar/desmarcar
- [x] 11.5 Renderitzar el header amb etiqueta relativa localitzada ("Avui/Hoy/Today", "Ahir/Ayer/Yesterday", o `DiaSetmana DD/MM`) i botó "Tornar" a dalt a l'esquerra
- [x] 11.6 Implementar l'estat emocional de la mascota: feliç si % completats ≥ 50%, trista si < 50% (via `getCompletionRate` del composable)
- [x] 11.7 Verificar que els botons de ruleta i missió diària NO apareguin a la vista diària del calendari
- [x] 11.8 Mostrar missatge "Encara no hi havia dades aquest dia" si l'API retorna 404

## 12. Frontend: modal de detalls historial (AgentJavascript + AgentTailwind)

- [x] 12.1 Crear el composable/funció `openHabitHistoryModal(habit, date)` que generi el contingut HTML per a SweetAlert2
- [x] 12.2 Mostrar XP i monedes calculades per la `dificultat` de `habits_json` (XP: 100/250/400; monedes: 2/5/10) si `acabado: true`; XP `0` i monedes `0` si `acabado: false`
- [x] 12.3 Mostrar bloc condicional de metadata: si `metadata !== null`, renderitzar la informació de l'API (portada, títol, miniatura)
- [x] 12.4 Vincular el botó "detalls" de cada `HabitHistoryCard` a l'obertura del modal
- [x] 12.5 Verificar que el modal mostra el `titol` del snapshot (no el títol actual de l'hàbit)

## 13. Frontend: filtratge per categoria (AgentJavascript + AgentTailwind)

- [x] 13.1 Afegir al component de la vista diària un selector desplegable de categories (categories úniques presents al snapshot + opció "Totes les categories")
- [x] 13.2 Connectar el selector al camp `categoryFilter` del store i filtrar les `HabitHistoryCard` visibles
- [x] 13.3 Implementar animació suau (transition CSS o Vue) per amagar/mostrar les targetes
- [x] 13.4 Mostrar missatge "Cap hàbit d'aquesta categoria aquell dia" si cap targeta és visible després del filtre
