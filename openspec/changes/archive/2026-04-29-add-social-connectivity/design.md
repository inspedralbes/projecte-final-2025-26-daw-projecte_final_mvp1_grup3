## Context

This design implements the Social Connectivity System extending the existing social forum. The system adds direct friendships and private 1-on-1 chat capabilities. Current architecture uses Laravel 11 API + Node.js Socket.io for real-time features, consistent with the existing social-forum implementation.

**Constraints:**
- Must reuse existing Socket.io server (port 3001) for real-time messaging
- PostgreSQL 16 database already in use
- Frontend uses Nuxt 3 with claymorphism design aesthetic
- Existing auth middleware must protect all endpoints

**Stakeholders:** Regular users seeking fitness accountability partners, competitive users comparing progress

## Goals / Non-Goals

**Goals:**
- Enable friend request/accept/reject workflow between users
- Display friends list with online status
- Support 1-on-1 private chat with real-time delivery
- Show public profiles with pet, level, XP, achievements, streak
- Grant achievement at 5 confirmed friends

**Non-Goals:**
- Group chat or chat rooms (out of scope)
- Video/audio chat (out of scope)
- Social feed algorithms (handled by existing social-forum)

## Decisions

### 1. Database Schema: FRIENDSHIPS with composite unique constraint
**Decision:** Create FRIENDSHIPS table with (requester_id, addressee_id) unique index.
**Rationale:** Prevents duplicate friend requests and ensures bidirectional relationship is tracked in single direction. Simpler than storing both directions.
**Alternative Considered:** Two records per friendship (A→B and B→A) - rejected for complexity.

### 2. Real-time Architecture: Socket.io on separate port
**Decision:** Use existing backend-node service on port 3001 for private messages.
**Rationale:** Reuses established pattern from social-forum. Laravel handles persistence, Node.js handles delivery.
**Alternative Considered:** Laravel WebSocket (Pusher) - rejected due to external dependency cost.

### 3. Profile Visibility: Public vs Private
**Decision:** All profile fields (pet, level, XP, achievements, streak) are publicly visible to any authenticated user.
**Rationale:** Fitness gamification benefits from transparency and competition. User can only see their own private data elsewhere.
**Alternative Considered:** Privacy settings per field - rejected for UI complexity.

### 4. Chat Initialization: Only friends can chat
**Decision:** Private chat requires accepted friendship status.
**Rationale:** Prevents unsolicited messages, maintains community safety.
**Alternative Considered:** Open messaging - rejected per requirements.

### 5. Achievement Trigger: Count confirmed friendships
**Decision:** Check friend count after each acceptance and grant "Socializador" achievement at exactly 5 friends.
**Rationale:** Matches requirement from proposal. Uses counter rather than cumulative to avoid double-granting.

## Risks / Trade-offs

**[Risk]** Race condition in concurrent friend requests between same users
→ **[Mitigation]** Database unique constraint will reject duplicate. Second request returns "already exists" error.

**[Risk]** User sends self-friend request
→ **[Mitigation]** Validate in API that requester_id !== addressee_id. Return 400 error.

**[Risk]** Offline message delivery
→ **[Mitigation]** Store messages in PRIVATE_MESSAGES table. Deliver on reconnect via history endpoint.

**[Risk]** Profanity in chat messages
→ **[Mitigation]** Apply existing word filter (same as social forum). Report action available.

**[Risk]** Performance with large friends list
→ **[Mitigation]** Index on status + user_id. Paginate list API.

## Migration Plan

1. Add FRIENDSHIPS and PRIVATE_MESSAGES tables to database/init.sql
2. Create Laravel API endpoints (friend requests, chat history) - no migrations
3. Deploy Node.js Socket.io handlers for private_message event
4. Deploy frontend components (FriendsScreen, ChatWindow)
5. Monitor error logs for first 24 hours
6. Rollback: Revert SQL if critical errors

## Open Questions

- Should online status be cached (polling) or real-time (Socket.io room)?
- What is the exact "Socializador" achievement criteria: 5 total friends or 5 simultaneous?