## ADDED Requirements

### Requirement: User can create a clan
A user with level >= 5 SHALL be able to create a clan with name, category, member limit, and privacy setting.

#### Scenario: Create public clan successfully
- **WHEN** user with level >= 5 sends POST /api/clans with name, categoria_id, max_membres, es_public=true
- **THEN** system creates CLANS record and returns 201 with clan data

#### Scenario: Create private clan successfully
- **WHEN** user with level >= 5 sends POST /api/clans with name, categoria_id, max_membres, es_public=false
- **THEN** system creates CLANS record with es_public=false and returns 201

#### Scenario: Create clan with invalid member limit
- **WHEN** user sends POST /api/clans with max_membres not in (10, 15, 20)
- **THEN** system returns 400 error

#### Scenario: User below level 5 creates clan
- **WHEN** user with level < 5 sends POST /api/clans
- **THEN** system returns 403 error

### Requirement: User can view available clans
A user with level >= 5 SHALL be able to browse public clans.

#### Scenario: View public clans list
- **WHEN** user with level >= 5 sends GET /api/clans
- **THEN** system returns paginated list of public clans

#### Scenario: View empty clans list
- **WHEN** user sends GET /api/clans with no public clans
- **THEN** system returns empty array

### Requirement: User can update clan settings
A clan leader SHALL be able to update clan configuration.

#### Scenario: Update clan name
- **WHEN** leader sends PUT /api/clans/{id} with new name
- **THEN** system updates name and returns 200

#### Scenario: Non-leader updates clan
- **WHEN** member sends PUT /api/clans/{id}
- **THEN** system returns 403 error

### Requirement: User can leave clan
A member SHALL be able to voluntarily leave a clan.

#### Scenario: Member leaves clan
- **WHEN** member sends POST /api/clans/{id}/leave
- **THEN** system removes member and returns 200

#### Scenario: Leader leaves clan
- **WHEN** leader sends POST /api/clans/{id}/leave
- **THEN** system returns 400 error (leader must transfer or disband)