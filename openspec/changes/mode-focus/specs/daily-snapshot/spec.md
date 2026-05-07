## MODIFIED Requirements

### Requirement: Estructura del snapshot (camps)
Cada registre de `DAILY_SNAPSHOTS` SHALL contenir els camps següents:
- `usuari_id`: FK a la taula `USUARIS`.
- `data`: Data del dia capturat (`DATE`, clau única per `usuari_id`).
- `mascota_json`: `jsonb` amb el progrés de gamificació de l'usuari (nivell, XP).
- `habits_json`: `jsonb` amb array d'hàbits actius i el seu estat de completació, amb el camp `metadata` desat tal com estava aquell dia, i amb metadades de Focus Mode per hàbit quan s'hagi utilitzat (`completed_with_focus` i `predominant_focus_mode`).
- `economia_json`: `jsonb` amb les monedes i XP guanyades específicament durant les 24 hores del dia capturat.

#### Scenario: Contingut de mascota_json
- **WHEN** es genera el snapshot d'un usuari
- **THEN** `mascota_json` SHALL contenir, com a mínim, els camps `nivell`, `xp_total`, `xp_actual_nivel` i `xp_objetivo_nivel` corresponents als valors actuals de l'usuari en el moment de la captura

#### Scenario: Contingut de habits_json
- **WHEN** es genera el snapshot d'un usuari
- **THEN** `habits_json` SHALL incloure tots els hàbits assignats com a actius a l'usuari aquell dia, cadascun amb `id`, `titol`, `icona`, `color`, `dificultat`, `categoria_id`, `metadata` (nullable), `acabado` (bool: `true` si l'hàbit es va completar aquell dia, `false` en cas contrari), `completed_with_focus` (bool) i `predominant_focus_mode` (`25_5` o `50_10` quan correspongui, altrament `null`)

#### Scenario: Contingut de economia_json
- **WHEN** es genera el snapshot d'un usuari
- **THEN** `economia_json.xp_guanyada_avui` i `economia_json.monedes_guanyades_avui` SHALL reflectir els totals acumulats únicament durant les 24 hores del dia capturat (no el total acumulat de tota la vida)

#### Scenario: Càlcul de monedes guanyades
- **WHEN** es calcula `economia_json.monedes_guanyades_avui`
- **THEN** el sistema SHALL sumar les monedes corresponents a la dificultat de cada hàbit completat aquell dia, segons el mapa de gamificació del projecte (fàcil: 2, mitjà: 5, difícil: 10)

#### Scenario: Càlcul de XP guanyada
- **WHEN** es calcula `economia_json.xp_guanyada_avui`
- **THEN** el sistema SHALL sumar la XP de cada hàbit completat aquell dia segons la dificultat (fàcil: 100, mitjà: 250, difícil: 400)

### Requirement: Endpoint de consulta de snapshot diari
El sistema SHALL exposar `GET /api/calendar/snapshot/{usuariId}/{date}` (format `YYYY-MM-DD`) que retorna el snapshot complet de l'usuari indicat per a la data sol·licitada, incloent els camps de Focus Mode dins `habits_json` quan existeixin.

#### Scenario: Snapshot existent
- **WHEN** es fa `GET /api/calendar/snapshot/1/2026-01-15` i existeix el registre
- **THEN** el sistema SHALL retornar `200` amb el JSON complet del snapshot (`mascota_json`, `habits_json`, `economia_json`) amb `completed_with_focus` i `predominant_focus_mode` per hàbit si aplica

#### Scenario: Snapshot inexistent
- **WHEN** es fa `GET /api/calendar/snapshot/1/2025-01-01` i no hi ha registre per aquella data
- **THEN** el sistema SHALL retornar `404` amb `{"message": "No snapshot found for this date"}`
