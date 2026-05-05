## Purpose
Definir la consulta de detalls d'hàbits històrics des de snapshots, incloent modal i filtres de categoria en vista diària.

## Requirements

### Requirement: Botó "detalls" a cada hàbit de la vista diària
Cada targeta d'hàbit de la vista diària del calendari SHALL mostrar un botó "detalls" que obre el modal d'historial per a aquell hàbit i aquella data.

#### Scenario: Visibilitat del botó detalls
- **WHEN** es renderitza la llista d'hàbits a la vista diària del calendari
- **THEN** cada `HabitHistoryCard` SHALL mostrar un botó "detalls" independent del camp `acabado` de l'hàbit

#### Scenario: Clic al botó detalls
- **WHEN** l'usuari fa clic a "detalls" d'un hàbit a la vista diària
- **THEN** el sistema SHALL obrir un modal de SweetAlert2 amb el contingut de l'historial d'aquell hàbit per a aquella data

### Requirement: Contingut del modal de detalls d'hàbit historial
El modal SHALL mostrar la informació tal com estava en el moment del snapshot, independentment dels canvis posteriors que l'usuari hagi fet a l'hàbit.

#### Scenario: Recompensa real mostrada al modal
- **WHEN** s'obre el modal de detalls d'un hàbit completat (`acabado: true`)
- **THEN** el modal SHALL mostrar la XP i les monedes guanyades calculades segons la dificultat que tenia l'hàbit en aquella data: XP (fàcil: 100, mitjà: 250, difícil: 400) i monedes (fàcil: 2, mitjà: 5, difícil: 10), extretes del camp `dificultat` de `habits_json`

#### Scenario: Recompensa zero per hàbit no completat
- **WHEN** s'obre el modal de detalls d'un hàbit no completat (`acabado: false`)
- **THEN** el modal SHALL mostrar XP `0` i monedes `0`, indicant que aquell dia no es va completar

#### Scenario: Hàbit amb metadata d'API
- **WHEN** el camp `metadata` de l'hàbit al `habits_json` del snapshot no és `null`
- **THEN** el modal SHALL mostrar la informació de l'API desada (portada del llibre, títol de la rutina de Spotify, miniatura de YouTube, etc.)

#### Scenario: Hàbit sense metadata d'API
- **WHEN** el camp `metadata` de l'hàbit al `habits_json` del snapshot és `null` o no existeix
- **THEN** el modal SHALL mostrar únicament la recompensa (XP i monedes) sense cap bloc de metadata

#### Scenario: Títol original del snapshot (no el títol actual)
- **WHEN** l'usuari ha editat el títol d'un hàbit després de la data del snapshot
- **THEN** el modal SHALL mostrar el títol tal com estava en el moment del snapshot (el contingut de `habits_json.titol`), no el títol actual de la taula `HABITS`

#### Scenario: Tancament del modal
- **WHEN** l'usuari fa clic fora del modal o al botó de tancament de SweetAlert2
- **THEN** el modal SHALL tancar-se i tornar a la vista diària sense cap canvi d'estat

### Requirement: Filtratge d'hàbits per categoria a la vista diària
La vista diària SHALL permetre filtrar la llista d'hàbits mostrats per categoria mitjançant un selector desplegable.

#### Scenario: Selector de categoria visible
- **WHEN** es renderitza la vista diària amb almenys un hàbit
- **THEN** el sistema SHALL mostrar un selector desplegable amb totes les categories dels hàbits del snapshot, més l'opció "Totes les categories"

#### Scenario: Filtratge per categoria seleccionada
- **WHEN** l'usuari selecciona una categoria al selector desplegable
- **THEN** les targetes d'hàbit que no pertanyen a aquella categoria SHALL amagar-se amb una animació suau, i les que sí hi pertanyen SHALL romandre visibles

#### Scenario: Restabliment del filtre
- **WHEN** l'usuari selecciona "Totes les categories" al selector desplegable
- **THEN** totes les targetes d'hàbit SHALL tornar a ser visibles amb una animació suau

#### Scenario: Cap hàbit visible després del filtre
- **WHEN** l'usuari aplica un filtre de categoria i cap hàbit del snapshot pertany a aquella categoria
- **THEN** el sistema SHALL mostrar un missatge "Cap hàbit d'aquesta categoria aquell dia" en lloc d'una llista buida

### Requirement: Immutabilitat de les dades del snapshot al modal
Les dades mostrades al modal de detalls SHALL provenir exclusivament del `habits_json` del snapshot corresponent, garantint que els canvis presents no afectin la visualització del passat.

#### Scenario: Categoria esborrada en el present
- **WHEN** l'usuari ha eliminat o canviat la categoria d'un hàbit després de la data del snapshot
- **THEN** el modal SHALL mostrar la categoria i metadata tals com estaven al snapshot, sense reflectir el canvi actual
