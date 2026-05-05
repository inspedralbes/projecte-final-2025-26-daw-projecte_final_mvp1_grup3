## ADDED Requirements

### Requirement: User can join private chat room
A user with an established friendship SHALL be able to join a private chat room via WebSocket to receive messages in real-time.

#### Scenario: Join chat room successfully
- **WHEN** user clicks on a friend in the friends list
- **THEN** client emits join_private_chat with { friend_id, token }
- **AND** server validates JWT, verifies friendship exists, joins socket to room chat_{user_id}_{friend_id}

#### Scenario: Join chat room without friendship
- **WHEN** user who is not friends emits join_private_chat
- **THEN** server rejects with error and does not join room

#### Scenario: Join chat room while offline messages exist
- **WHEN** user joins room with unread messages
- **THEN** server marks all messages as read (is_read: true) and returns history

### Requirement: User can send private message via WebSocket
A connected user SHALL be able to send a private message to a friend via WebSocket that appears instantly.

#### Scenario: Send message via WebSocket
- **WHEN** user sends send_private_message with { receiver_id, message_text }
- **THEN** server validates JWT and friendship, persists message, emits new_private_message to room
- **AND** message appears in chat instantly (Optimistic UI)

#### Scenario: Send message to non-friend via WebSocket
- **WHEN** user sends message to non-friend
- **THEN** server rejects with error, message does not persist

#### Scenario: Message sent when receiver offline
- **WHEN** sender sends message via WebSocket while receiver is offline
- **THEN** message persists in database for delivery on receiver's next connection

### Requirement: User sees typing status indicator
A user SHALL see when their friend is typing a message.

#### Scenario: See typing indicator
- **WHEN** friend emits typing_status with { is_typing: true }
- **THEN** client displays typing indicator in chat window

#### Scenario: Typing indicator cleared
- **WHEN** friend emits typing_status with { is_typing: false } or after timeout
- **THEN** client hides typing indicator

### Requirement: Chat auto-scrolls to new messages
A user SHALL see new messages without manual scrolling.

#### Scenario: Auto-scroll on new message
- **WHEN** new message arrives via WebSocket
- **THEN** chat window scrolls to bottom with smooth animation

### Requirement: Optimistic UI updates
A user SHALL see their message immediately after sending.

#### Scenario: Message appears instantly
- **WHEN** user sends message
- **THEN** message appears in chat UI before server confirmation
- **AND** if server rejects, message is removed with error indicator

### Requirement: User receives real-time private message
A connected user SHALL receive private messages instantly without polling.

#### Scenario: Receive message when online
- **WHEN** sender sends message via WebSocket
- **THEN** receiver receives new_private_message event instantly
- **AND** message is added to chat store and UI updates