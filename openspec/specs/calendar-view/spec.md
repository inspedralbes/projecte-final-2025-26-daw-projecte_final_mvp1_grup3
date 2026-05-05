## Purpose
Definir la navegació i visualització del calendari (mensual i diària) en mode només lectura per consultar l'estat històric.

## Requirements

### Requirement: Punt d'entrada al calendari des de la Home
El sistema SHALL mostrar una icona de calendari al header de `pages/home.vue`, a la dreta dels comptadors de nivell, que permeti navegar a la vista mensual.

#### Scenario: Clic a la icona del calendari
- **WHEN** l'usuari fa clic a la icona de calendari del header de la Home
- **THEN** el sistema SHALL navegar a `/calendar` amb una transició suau de Nuxt (SPA, sense recàrrega)

#### Scenario: Aparença de la icona
- **WHEN** es renderitza el header de la Home
- **THEN** la icona SHALL ser un div circular amb fons pastís i una icona de calendari en estil claymorphism, consistent amb el disseny visual existent

### Requirement: Vista mensual del calendari
El sistema SHALL mostrar a `/calendar` un grid de 7 columnes (Dilluns a Diumenge) amb totes les cel·les del mes actiu.

#### Scenario: Càrrega inicial de la vista mensual
- **WHEN** l'usuari navega a `/calendar`
- **THEN** el sistema SHALL fer `GET /api/calendar/month/{usuariId}/{year}/{month}` amb el mes i any actuals i renderitzar el grid amb els indicadors de categories per als dies amb snapshot

#### Scenario: Cel·la de dia amb activitat
- **WHEN** un dia té `has_snapshot: true` a la resposta de l'API
- **THEN** la cel·la SHALL mostrar el número del dia i uns indicadors (cercles de colors) que representen les categories dels hàbits completats (`category_colors`)

#### Scenario: Cel·la de dia sense activitat
- **WHEN** un dia té `has_snapshot: false`
- **THEN** la cel·la SHALL mostrar el número del dia sense indicadors de colors

#### Scenario: Clic en una cel·la de dia
- **WHEN** l'usuari fa clic en una cel·la de dia a la vista mensual
- **THEN** el sistema SHALL navegar a la vista diària `/calendar/day?date=YYYY-MM-DD` per a aquella data

#### Scenario: Navegació al mes anterior
- **WHEN** l'usuari fa clic al botó `<`
- **THEN** el sistema SHALL carregar el mes anterior i actualitzar el header amb el nou "Mes Any"

#### Scenario: Navegació al mes següent
- **WHEN** l'usuari fa clic al botó `>`
- **THEN** el sistema SHALL carregar el mes següent i actualitzar el header amb el nou "Mes Any"

### Requirement: Vista diària del calendari (mirror del snapshot)
El sistema SHALL mostrar a `/calendar/day` una rèplica de la Home en mode de lectura, basada en les dades del snapshot de la data indicada pel paràmetre `?date=YYYY-MM-DD`.

#### Scenario: Càrrega de la vista diària amb snapshot existent
- **WHEN** l'usuari navega a `/calendar/day?date=2026-01-15` i hi ha snapshot per aquella data
- **THEN** el sistema SHALL fer `GET /api/calendar/snapshot/{usuariId}/2026-01-15`, renderitzar la mascota amb les dades de `mascota_json` i mostrar la llista d'hàbits de `habits_json`

#### Scenario: Header de la vista diària
- **WHEN** es renderitza la vista diària
- **THEN** el header SHALL mostrar:
  - "Avui" / "Hoy" / "Today" per la data actual,
  - "Ahir" / "Ayer" / "Yesterday" per al dia anterior,
  - i per a dies més antics un format curt localitzat `DiaSetmana DD/MM` (per exemple `Dv 1/2`);
  i també un botó "Tornar" a la cantonada superior esquerra

#### Scenario: Navegació enrere des de la vista diària
- **WHEN** l'usuari fa clic al botó "Tornar" de la vista diària
- **THEN** el sistema SHALL navegar a la vista mensual `/calendar` mantenint el mes de la data consultada

#### Scenario: Càrrega de la vista diària sense snapshot
- **WHEN** l'usuari navega a `/calendar/day?date=YYYY-MM-DD` i no hi ha snapshot per aquella data
- **THEN** el sistema SHALL mostrar un missatge "Encara no hi havia dades aquest dia" en lloc de la llista d'hàbits i mascota

#### Scenario: Estat emocional de la mascota
- **WHEN** el percentatge d'hàbits completats (`acabado: true`) del dia és ≥ 50%
- **THEN** la mascota SHALL mostrar una expressió feliç

#### Scenario: Mascota trista per baix compliment
- **WHEN** el percentatge d'hàbits completats del dia és < 50%
- **THEN** la mascota SHALL mostrar una expressió trista

#### Scenario: Absència d'elements del present a la vista diària
- **WHEN** es renderitza la vista diària del calendari
- **THEN** el botó de la ruleta i la secció de missió diària NOT SHALL aparèixer

#### Scenario: Swipe cap al dia següent
- **WHEN** l'usuari fa swipe cap a l'esquerra (o scroll horitzontal equivalent) dins la vista diària
- **THEN** el sistema SHALL navegar al dia següent (`+1`) si la data resultant no és posterior a avui

#### Scenario: Swipe cap al dia anterior
- **WHEN** l'usuari fa swipe cap a la dreta (o scroll horitzontal equivalent) dins la vista diària
- **THEN** el sistema SHALL navegar al dia anterior (`-1`)

#### Scenario: Bloqueig de navegació a futur
- **WHEN** l'usuari és al dia actual i intenta anar al següent dia amb swipe cap a l'esquerra
- **THEN** el sistema SHALL mantenir la data actual i no navegar

### Requirement: Mode de lectura exclusiu al calendari
Totes les vistes del calendari SHALL ser estrictament de lectura. Cap interacció SHALL modificar l'estat de l'usuari ni els snapshots.

#### Scenario: Intent de marcar un hàbit passat com a completat
- **WHEN** l'usuari visualitza la llista d'hàbits a la vista diària del calendari
- **THEN** els hàbits SHALL mostrar-se sense botons d'acció (marcar/desmarcar); l'estat `acabado` és informatiu i no interactuable

### Requirement: Navegació "Tornar" en cascada
El sistema SHALL implementar una navegació en cascada Dia → Mensual → Home per al botó "Tornar".

#### Scenario: Tornar de la vista diària
- **WHEN** l'usuari fa clic a "Tornar" des de la vista diària
- **THEN** el sistema SHALL navegar a la vista mensual `/calendar` amb el mes corresponent a la data consultada

#### Scenario: Tornar de la vista mensual
- **WHEN** l'usuari fa clic a "Tornar" des de la vista mensual
- **THEN** el sistema SHALL navegar a `/home`
