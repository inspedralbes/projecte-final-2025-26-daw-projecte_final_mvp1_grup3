## ADDED Requirements

### Requirement: Snapshot diari automàtic a les 23:59
El sistema SHALL capturar l'estat complet de cada usuari no prohibit una vegada al dia a les 23:59 i desar-lo a la taula `DAILY_SNAPSHOTS`. El snapshot és immutable un cop creat; cap operació posterior pot modificar-lo.

#### Scenario: Execució nocturna exitosa
-- **WHEN** el worker Node.js publica el missatge `snapshot:run` a la cua Redis `snapshot_queue` a les 23:59
- **THEN** el `SnapshotCommand` de Laravel s'executa, itera tots els usuaris amb `prohibit = FALSE` i crea un registre a `DAILY_SNAPSHOTS` per cadascun amb la data del dia

#### Scenario: Usuari sense hàbits actius
- **WHEN** el `SnapshotCommand` processa un usuari que no té cap hàbit actiu aquell dia
- **THEN** el sistema SHALL crear igualment el registre amb `habits_json = []` i els camps de mascota i economia amb els seus valors actuals

#### Scenario: Snapshot ja existent per aquell dia
- **WHEN** el `SnapshotCommand` intenta crear un snapshot per a un `usuari_id` i una `data` que ja existeix a `DAILY_SNAPSHOTS`
- **THEN** el sistema SHALL ometre la creació (no duplicar) i continuar amb el següent usuari sense llançar un error

### Requirement: Estructura del snapshot (camps)
Cada registre de `DAILY_SNAPSHOTS` SHALL contenir els camps següents:
- `usuari_id`: FK a la taula `USUARIS`.
- `data`: Data del dia capturat (`DATE`, clau única per `usuari_id`).
- `mascota_json`: `jsonb` amb el progrés de gamificació de l'usuari (nivell, XP).
- `habits_json`: `jsonb` amb array d'hàbits actius i el seu estat de completació, amb el camp `metadata` desat tal com estava aquell dia.
- `economia_json`: `jsonb` amb les monedes i XP guanyades específicament durant les 24 hores del dia capturat.

#### Scenario: Contingut de mascota_json
- **WHEN** es genera el snapshot d'un usuari
- **THEN** `mascota_json` SHALL contenir, com a mínim, els camps `nivell`, `xp_total`, `xp_actual_nivel` i `xp_objetivo_nivel` corresponents als valors actuals de l'usuari en el moment de la captura

#### Scenario: Contingut de habits_json
- **WHEN** es genera el snapshot d'un usuari
- **THEN** `habits_json` SHALL incloure tots els hàbits assignats com a actius a l'usuari aquell dia, cadascun amb `id`, `titol`, `icona`, `color`, `dificultat`, `categoria_id`, `metadata` (nullable) i `acabado` (bool: `true` si l'hàbit es va completar aquell dia, `false` en cas contrari)

#### Scenario: Contingut de economia_json
- **WHEN** es genera el snapshot d'un usuari
- **THEN** `economia_json.xp_guanyada_avui` i `economia_json.monedes_guanyades_avui` SHALL reflectir els totals acumulats únicament durant les 24 hores del dia capturat (no el total acumulat de tota la vida)

#### Scenario: Càlcul de monedes guanyades
- **WHEN** es calcula `economia_json.monedes_guanyades_avui`
- **THEN** el sistema SHALL sumar les monedes corresponents a la dificultat de cada hàbit completat aquell dia, segons el mapa de gamificació del projecte (fàcil: 2, mitjà: 5, difícil: 10)

#### Scenario: Càlcul de XP guanyada
- **WHEN** es calcula `economia_json.xp_guanyada_avui`
- **THEN** el sistema SHALL sumar la XP de cada hàbit completat aquell dia segons la dificultat (fàcil: 100, mitjà: 250, difícil: 400)

### Requirement: Recuperació manual de snapshots perduts
El sistema SHALL permetre executar el `SnapshotCommand` manualment especificant una data concreta per recuperar un dia en el qual el cron no s'hagi executat.

#### Scenario: Execució manual amb data específica
- **WHEN** s'executa `php artisan snapshot:run --date=YYYY-MM-DD`
- **THEN** el sistema SHALL generar els snapshots de tots els usuaris no prohibits per a la data especificada, respectant la regla de no duplicació

#### Scenario: Data futura no permesa
- **WHEN** s'executa `php artisan snapshot:run --date=YYYY-MM-DD` amb una data posterior a avui
- **THEN** el sistema SHALL retornar un error i no crear cap registre

### Requirement: Endpoint de consulta de snapshot diari
El sistema SHALL exposar `GET /api/calendar/snapshot/{usuariId}/{date}` (format `YYYY-MM-DD`) que retorna el snapshot complet de l'usuari indicat per a la data sol·licitada.

#### Scenario: Snapshot existent
- **WHEN** es fa `GET /api/calendar/snapshot/1/2026-01-15` i existeix el registre
- **THEN** el sistema SHALL retornar `200` amb el JSON complet del snapshot (`mascota_json`, `habits_json`, `economia_json`)

#### Scenario: Snapshot inexistent
- **WHEN** es fa `GET /api/calendar/snapshot/1/2025-01-01` i no hi ha registre per aquella data
- **THEN** el sistema SHALL retornar `404` amb `{"message": "No snapshot found for this date"}`

### Requirement: Endpoint de resum mensual
El sistema SHALL exposar `GET /api/calendar/month/{usuariId}/{year}/{month}` que retorna un array lleuger per al grid mensual, sense el contingut complet del snapshot.

#### Scenario: Mes amb activitat parcial
- **WHEN** es fa `GET /api/calendar/month/1/2026/01`
- **THEN** el sistema SHALL retornar un array de 31 elements (o els que tingui el mes), cadascun amb `{"day": N, "has_snapshot": bool, "category_colors": ["#hex", ...]}` on `category_colors` són els colors únics de les categories dels hàbits completats aquell dia

#### Scenario: Mes completament buit
- **WHEN** es fa `GET /api/calendar/month/1/2024/06` i no hi ha cap snapshot del mes
- **THEN** el sistema SHALL retornar un array amb tots els elements amb `has_snapshot: false` i `category_colors: []`

### Requirement: Color de categoria per als indicadors mensuals
El sistema SHALL associar un color a cada categoria de la taula `CATEGORIES` per poder representar visualment els indicadors al grid mensual.

#### Scenario: Camp color persistit a CATEGORIES
- **WHEN** es consulten les categories existents
- **THEN** la taula `CATEGORIES` SHALL exposar un camp `color VARCHAR(20)` amb un valor hexadecimal per defecte

#### Scenario: Construcció de category_colors al resum mensual
- **WHEN** es construeix la resposta de `GET /api/calendar/month/{usuariId}/{year}/{month}` per a un dia amb hàbits completats
- **THEN** `category_colors` SHALL contenir els colors únics (`CATEGORIES.color`) corresponents a les categories dels hàbits amb `acabado = true` aquell dia
