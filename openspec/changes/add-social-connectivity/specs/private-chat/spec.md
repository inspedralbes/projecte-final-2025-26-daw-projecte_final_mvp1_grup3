## ADDED Requirements

### Requirement: User can send private message
A user SHALL be able to send a private message to a friend.

#### Scenario: Send private message successfully
- **WHEN** user sends POST /api/chat/{receiver_id} with message content
- **THEN** system stores message in PRIVATE_MESSAGES and returns 201

#### Scenario: Send message to non-friend
- **WHEN** user tries to send message to user they are not friends with
- **THEN** system returns 403 error

#### Scenario: Send message to blocked user
- **WHEN** user tries to send message to user who blocked them
- **THEN** system returns 403 error

### Requirement: User can view chat history
A user SHALL be able to view their chat history with a specific friend.

#### Scenario: View chat history
- **WHEN** user sends GET /api/chat/{friend_id}
- **THEN** system returns paginated message history ordered by created_at DESC

#### Scenario: View empty chat history
- **WHEN** user with no messages sends GET /api/chat/{friend_id}
- **THEN** system returns empty array

### Requirement: User receives real-time message
Connected users SHALL receive private messages instantly via Socket.io.

#### Scenario: Receive message when online
- **WHEN** sender sends private message to online receiver
- **THEN** receiver receives private_message event instantly

#### Scenario: Message stored when offline
- **WHEN** sender sends message to offline receiver
- **THEN** message is stored and delivered on next connection

### Requirement: User can mark messages as read
A user SHALL be able to mark messages as read.

#### Scenario: Mark messages as read
- **WHEN** user sends PUT /api/chat/{friend_id}/read
- **THEN** system updates all unread messages to read and returns 200

### Requirement: User can report inappropriate chat
A user SHALL be able to report inappropriate messages.

#### Scenario: Report message
- **WHEN** user sends POST /api/reports with chat report type
- **THEN** system creates report record and returns 201