## Why

Users need to connect with each other beyond the social forum to foster competition and mutual support. The current system lacks direct friendships and private messaging, limiting social engagement. This enables meaningful connections between users who share fitness goals.

## What Changes

- Add FRIENDSHIPS table with request/accept/reject workflow
- Add PRIVATE_MESSAGES table for 1-on-1 chat
- Create friendship management API endpoints
- Create private chat API endpoints with real-time support via Socket.io
- Implement public profile viewing
- Add WebSocket events for friend requests and private messages
- Implement chat with profanity filtering and reporting
- Grant "Socializador" achievement at 5 confirmed friends

## Capabilities

### New Capabilities

- `friendship-management`: Friend requests, accept/reject workflow, friends list with online status
- `public-profile-view`: Third-party public profile viewing with pet, level, XP, achievements, streak
- `private-chat`: 1-on-1 real-time messaging with read receipts

### Modified Capabilities

- (none - this is a new feature extending social capabilities)

## Impact

- **Database**: New tables FRIENDSHIPS and PRIVATE_MESSAGES in PostgreSQL
- **API**: New endpoints in Laravel for friendship and chat management
- **Real-time**: Socket.io server on port 3001 for messaging and notifications
- **Frontend**: New FriendsScreen, FriendCard, PublicProfileView, ChatWindow components
- **Gamification**: New "Socializador" achievement trigger at 5 friends