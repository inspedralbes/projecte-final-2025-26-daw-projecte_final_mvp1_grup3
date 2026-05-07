## 1. Database

- [x] 1.1 Add composite index on (sender_id, receiver_id) to PRIVATE_MESSAGES in init.sql
- [x] 1.2 Add is_read field to PRIVATE_MESSAGES if not exists

## 2. Backend - Socket Handlers

- [x] 2.1 Add join_private_chat handler in socialHandlers.js
- [x] 2.2 Validate friendship before joining room in join_private_chat
- [x] 2.3 Add send_private_message handler
- [x] 2.4 Add typing_status handler
- [x] 2.5 Emit new_private_message to room on send
- [x] 2.6 Emit typing_indicator to room

## 3. Backend - API Updates

- [x] 3.1 Update ChatController to support mark as read via WebSocket events
- [x] 3.2 Add Redis pub/sub publish on new message

## 4. Frontend - Store Updates

- [x] 4.1 Add WebSocket connection in ChatStore
- [x] 4.2 Add optimistic UI flag when sending message
- [x] 4.3 Implement handle_new_private_message listener
- [x] 4.4 Implement handle_typing_indicator listener
- [x] 4.5 Add error handling for failed optimistic messages

## 5. Frontend - Components

- [x] 5.1 Update ChatWindow.vue to join room on friend click
- [x] 5.2 Add auto-scroll to bottom on new message
- [x] 5.3 Add typing indicator UI
- [x] 5.4 Add optimistic message state (pending/error)
- [x] 5.5 Emit typing_status on input

## 6. Integration & Testing

- [x] 6.1 Test join room with valid friend
- [x] 6.2 Test join room without friendship (rejected)
- [x] 6.3 Test send message via WebSocket
- [x] 6.4 Test typing indicator
- [x] 6.5 Test optimistic UI message appears instantly
- [x] 6.6 Test message stored when receiver offline
- [x] 6.7 Test auto-scroll animation