## ADDED Requirements

### Requirement: User can send message to clan chat
A clan member SHALL be able to send messages to the clan chat channel.

#### Scenario: Send message successfully
- **WHEN** member sends POST /api/clans/{id}/messages with message content
- **THEN** system stores message and returns 201

#### Scenario: Non-member sends message
- **WHEN** user who is not a member sends POST /api/clans/{id}/messages
- **THEN** system returns 403 error

#### Scenario: Send message with habit reference
- **WHEN** member sends POST /api/clans/{id}/messages with habit_id
- **THEN** system stores message with habit reference

#### Scenario: Send message with plantilla reference
- **WHEN** member sends POST /api/clans/{id}/messages with plantilla_id
- **THEN** system stores message with plantilla reference

### Requirement: User can view clan chat history
A clan member SHALL be able to view chat history.

#### Scenario: View chat history
- **WHEN** member sends GET /api/clans/{id}/messages
- **THEN** system returns paginated message history

#### Scenario: Empty chat history
- **WHEN** member views clan with no messages
- **THEN** system returns empty array

### Requirement: User receives real-time clan messages
Connected members SHALL receive clan messages instantly via Socket.io.

#### Scenario: Real-time message delivery
- **WHEN** member sends message to clan
- **THEN** all connected members receive clan_message event