## ADDED Requirements

### Requirement: User can join public clan
A user with level >= 5 SHALL be able to join public clans voluntarily.

#### Scenario: Join public clan successfully
- **WHEN** user with level >= 5 sends POST /api/clans/{id}/join
- **THEN** system adds member and returns 200

#### Scenario: Join public clan at capacity
- **WHEN** user tries to join clan at max_membres limit
- **THEN** system returns 400 error

#### Scenario: User below level 5 joins clan
- **WHEN** user with level < 5 sends POST /api/clans/{id}/join
- **THEN** system returns 403 error

### Requirement: User can request to join private clan
A user with level >= 5 SHALL be able to request admission to private clans.

#### Scenario: Request to join private clan
- **WHEN** user with level >= 5 sends POST /api/clans/{id}/request
- **THEN** system creates CLAN_REQUEST and returns 201

#### Scenario: Already a member requests
- **WHEN** member sends POST /api/clans/{id}/request
- **THEN** system returns 400 error

### Requirement: Leader can accept/reject join request
A clan leader SHALL be able to accept or reject pending join requests.

#### Scenario: Accept join request
- **WHEN** leader sends PUT /api/clan-requests/{id}/accept
- **THEN** system adds member and returns 200

#### Scenario: Reject join request
- **WHEN** leader sends PUT /api/clan-requests/{id}/reject
- **THEN** system marks request as rejected and returns 200

#### Scenario: Non-leader accepts request
- **WHEN** member sends PUT /api/clan-requests/{id}/accept
- **THEN** system returns 403 error

### Requirement: Member can invite other users
A clan member SHALL be able to invite external users to the clan.

#### Scenario: Invite user to public clan
- **WHEN** member sends POST /api/clans/{id}/invite with user_id to public clan
- **THEN** system creates invitation, invited user joins automatically

#### Scenario: Invite user to private clan
- **WHEN** member sends POST /api/clans/{id}/invite with user_id to private clan
- **THEN** system creates invitation as pending request

#### Scenario: Invite already member
- **WHEN** member sends invite to existing member
- **THEN** system returns 400 error

### Requirement: User can accept clan invitation
An invited user SHALL be able to accept the invitation.

#### Scenario: Accept invitation to public clan
- **WHEN** user sends PUT /api/clan-invitations/{id}/accept for public clan
- **THEN** system adds member automatically

#### Scenario: Accept invitation to private clan
- **WHEN** user sends PUT /api/clan-invitations/{id}/accept for private clan
- **THEN** system creates join request pending leader approval

### Requirement: Leader can remove member
A clan leader SHALL be able to remove any member.

#### Scenario: Leader removes member
- **WHEN** leader sends DELETE /api/clans/{id}/members/{member_id}
- **THEN** system removes member and returns 200

#### Scenario: Non-leader removes member
- **WHEN** member sends DELETE /api/clans/{id}/members/{member_id}
- **THEN** system returns 403 error

### Requirement: User can view pending requests
A clan leader SHALL be able to view pending join requests.

#### Scenario: View pending requests
- **WHEN** leader sends GET /api/clans/{id}/requests
- **THEN** system returns list of pending requests