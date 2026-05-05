## 1. Database SQL

- [x] 1.1 Add CLANS table (id, nom, categoria_id, es_public, max_membres, lider_id, created_at, updated_at) to init.sql
- [x] 1.2 Add CLAN_MEMBERS table (clan_id, usuari_id, rol, data_unio) to init.sql
- [x] 1.3 Add CLAN_REQUESTS table (id, clan_id, usuari_id, tipus, estat, invitador_id, created_at) to init.sql
- [x] 1.4 Add CLAN_MESSAGES table (id, clan_id, usuari_id, contingut, habit_id, plantilla_id, created_at) to init.sql
- [x] 1.5 Add unique index on (clan_id, usuari_id) for CLAN_MEMBERS
- [x] 1.6 Add foreign keys and indexes for all tables

## 2. API - Clan Management

- [x] 2.1 Create ClanController
- [x] 2.2 Implement create() - validate level >= 5, create clan, assign leader
- [x] 2.3 Implement index() - list public clans
- [x] 2.4 Implement show() - get clan details
- [x] 2.5 Implement update() - update clan settings (leader only)
- [x] 2.6 Implement leave() - member leaves clan
- [x] 2.7 Add routes to api.php (/api/clans/*)

## 3. API - Membership & Requests

- [x] 3.1 Create ClanRequestController
- [x] 3.2 Implement joinPublic() - join public clan
- [x] 3.3 Implement requestJoin() - request to join private clan
- [x] 3.4 Implement acceptRequest() - leader accepts request
- [x] 3.5 Implement rejectRequest() - leader rejects request
- [x] 3.6 Implement invite() - member invites user
- [x] 3.7 Implement acceptInvitation() - invited user accepts
- [x] 3.8 Implement removeMember() - leader removes member
- [x] 3.9 Implement getPendingRequests() - leader views requests
- [x] 3.10 Add routes to api.php (/api/clan-requests/*)

## 4. API - Clan Chat

- [x] 4.1 Add message endpoints to ClanController
- [x] 4.2 Implement sendMessage() - store message, optionally with habit/plantilla reference
- [x] 4.3 Implement getMessages() - paginated chat history
- [x] 4.4 Add shareHabit() - share habit to chat
- [x] 4.5 Add sharePlantilla() - share template to chat
- [x] 4.6 Add importHabit() - import shared habit
- [x] 4.7 Add importPlantilla() - import shared template

## 5. Backend - Socket Events

- [x] 5.1 Extend Socket.io handler for clan_message event
- [x] 5.2 Handle clan_message delivery to connected members
- [x] 5.3 Handle clan_request_notify event for leaders
- [x] 5.4 Handle clan_invitation_received event
- [x] 5.5 Handle clan_share notification event

## 6. Frontend - Stores

- [x] 6.1 Create clanStore (frontend/stores/useClanStore.js)
- [x] 6.2 Implement fetchClans(), createClan(), updateClan()
- [x] 6.3 Implement fetchMembers(), removeMember()
- [x] 6.4 Implement requestJoin(), acceptRequest(), rejectRequest()
- [x] 6.5 Create chatStore for clans (frontend/stores/useClanChatStore.js)
- [x] 6.6 Implement sendMessage(), fetchMessages()
- [x] 6.7 Implement shareHabit(), sharePlantilla(), importHabit(), importPlantilla()

## 7. Frontend - Components

- [x] 7.1 Create ClanList.vue - browse and search public clans
- [x] 7.2 Create ClanSettings.vue - create/edit clan form
- [x] 7.3 Create ClanDetail.vue - clan info and member list
- [x] 7.4 Create RequestManager.vue - manage pending requests (leader only)
- [x] 7.5 Create MemberList.vue - list with expell option (leader only)
- [x] 7.6 Create InvitationModal.vue - search and invite users
- [x] 7.7 Create ClanChat.vue - chat messages and sharing

## 8. Frontend - Routes

- [x] 8.1 Add /clans route in router
- [x] 8.2 Add /clans/:id route for clan detail
- [x] 8.3 Protect routes with auth and level >= 5 check

## 9. Integration & Testing

- [x] 9.1 Test create clan with level >= 5
- [x] 9.2 Test create clan fails with level < 5
- [x] 9.3 Test join public clan flow
- [x] 9.4 Test request join private clan flow
- [x] 9.5 Test leader accept/reject request
- [x] 9.6 Test member invitation flow
- [x] 9.7 Test chat messages in real-time
- [x] 9.8 Test share/import habit and plantilla
- [x] 9.9 Test leader remove member
- [x] 9.10 Test level 5 restriction across all endpoints