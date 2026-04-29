## 1. Database SQL

- [x] 1.1 Add FRIENDSHIPS table (id, requester_id, addressee_id, status, created_at, updated_at) to init.sql
- [x] 1.2 Add PRIVATE_MESSAGES table (id, sender_id, receiver_id, contingut, is_read, created_at) to init.sql
- [x] 1.3 Add unique index on (requester_id, addressee_id) for FRIENDSHIPS
- [x] 1.4 Add foreign keys and indexes for both tables

## 2. API - Friendship

- [x] 2.1 Create FriendshipController
- [x] 2.2 Implement sendRequest() - create pending friendship
- [x] 2.3 Implement acceptRequest() - accept pending request
- [x] 2.4 Implement rejectRequest() - reject pending request
- [x] 2.5 Implement getFriendsList() - list accepted friends
- [x] 2.6 Implement getPendingRequests() - list incoming pending
- [x] 2.7 Add routes to api.php (/api/friends/*)

## 3. API - Public Profile

- [x] 3.1 Create UserProfileController
- [x] 3.2 Implement getPublicProfile() - return pet, level, XP, achievements, streak
- [x] 3.3 Add route GET /api/users/{id}/profile
- [x] 3.4 Add route GET /api/users/self/profile

## 4. API - Private Chat

- [x] 4.1 Create ChatController
- [x] 4.2 Implement sendMessage() - validate friendship, store message
- [x] 4.3 Implement getChatHistory() - paginated message history
- [x] 4.4 Implement markAsRead() - update is_read flag
- [x] 4.5 Add routes to api.php (/api/chat/*)

## 5. Backend - Socket Events

- [x] 5.1 Extend existing Socket.io handler for private_message event
- [x] 5.2 Handle private_message delivery to online receiver
- [x] 5.3 Handle friend_request_notify event
- [x] 5.4 Store message if receiver offline (persisted via API history)

## 6. Frontend - Stores

- [x] 6.1 Create friendshipStore (frontend/stores/useFriendshipStore.js)
- [x] 6.2 Implement sendFriendRequest(), acceptFriendRequest(), rejectFriendRequest()
- [x] 6.3 Implement fetchFriendsList(), fetchPendingRequests()
- [x] 6.4 Create chatStore (frontend/stores/useChatStore.js)
- [x] 6.5 Implement sendMessage(), fetchChatHistory(), markAsRead()

## 7. Frontend - Components

- [x] 7.1 Create FriendsScreen.vue with tabs (Amigos, Pendientes, Buscador)
- [x] 7.2 Create FriendCard.vue with user info and chat quick access
- [x] 7.3 Create PublicProfileView.vue modal with pet, level, XP, achievements
- [x] 7.4 Create ChatWindow.vue with message bubbles and scroll

## 8. Frontend - Routes

- [x] 8.1 Add /friends route in router
- [x] 8.2 Protect route with auth middleware

## 9. Gamification - Achievements

- [x] 9.1 Add "Socializador" achievement to achievements table
- [x] 9.2 Implement trigger: check friend count after each accept
- [x] 9.3 Grant achievement when count reaches exactly 5

## 10. Integration & Testing

- [ ] 10.1 Test send/accept/reject friend request flow
- [ ] 10.2 Test view friends list with online status
- [ ] 10.3 Test view public profile
- [ ] 10.4 Test send private message between friends
- [ ] 10.5 Test real-time message delivery via Socket.io
- [ ] 10.6 Test "Socializador" achievement unlock