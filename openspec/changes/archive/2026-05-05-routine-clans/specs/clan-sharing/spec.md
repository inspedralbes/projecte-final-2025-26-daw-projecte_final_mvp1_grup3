## ADDED Requirements

### Requirement: Member can share habit to clan
A clan member SHALL be able to share their habit to the clan chat.

#### Scenario: Share habit successfully
- **WHEN** member sends POST /api/clans/{id}/share/habit with habit_id
- **THEN** system creates message with habit_id reference

#### Scenario: Share habit not owned by member
- **WHEN** member tries to share habit they don't own
- **THEN** system returns 400 error

#### Scenario: Non-member shares habit
- **WHEN** non-member sends POST /api/clans/{id}/share/habit
- **THEN** system returns 403 error

### Requirement: Member can share plantilla to clan
A clan member SHALL be able to share their template to the clan chat.

#### Scenario: Share plantilla successfully
- **WHEN** member sends POST /api/clans/{id}/share/plantilla with plantilla_id
- **THEN** system creates message with plantilla_id reference

#### Scenario: Share plantilla not owned by member
- **WHEN** member tries to share plantilla they don't own
- **THEN** system returns 400 error

### Requirement: Member can import shared habit
A member SHALL be able to import a habit shared by another member.

#### Scenario: Import shared habit
- **WHEN** member sends POST /api/clans/{id}/import/habit/{message_id}
- **THEN** system copies habit to user's habits

#### Scenario: Import habit already owned
- **WHEN** member imports habit they already have
- **THEN** system returns 400 error

### Requirement: Member can import shared plantilla
A member SHALL be able to import a template shared by another member.

#### Scenario: Import shared plantilla
- **WHEN** member sends POST /api/clans/{id}/import/plantilla/{message_id}
- **THEN** system copies plantilla to user's templates

#### Scenario: Import plantilla already owned
- **WHEN** member imports plantilla they already have
- **THEN** system returns 400 error

### Requirement: User receives shared content notification
Connected members SHALL receive notifications when content is shared.

#### Scenario: Shared habit notification
- **WHEN** member shares habit
- **THEN** other members receive clan_share notification via Socket.io

#### Scenario: Shared plantilla notification
- **WHEN** member shares plantilla
- **THEN** other members receive clan_share notification via Socket.io