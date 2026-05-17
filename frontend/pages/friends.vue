<template>
  <div class="friends-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 p-6">
    <div class="max-w-2xl mx-auto min-w-0">
      <HeaderSocial />

      <div class="friends-tabs">
        <button
          type="button"
          :class="['friends-tab', activeTab === 'amigos' ? 'friends-tab--active' : '']"
          @click="activeTab = 'amigos'"
        >
          {{ $t('friends.amigos') }}
        </button>
        <button
          type="button"
          :class="['friends-tab', activeTab === 'pendientes' ? 'friends-tab--active' : '']"
          @click="activeTab = 'pendientes'"
        >
          {{ $t('friends.pendientes') }}
          <span v-if="pendingCount > 0" class="friends-tab__badge">{{ pendingCount }}</span>
        </button>
        <button
          type="button"
          :class="['friends-tab', activeTab === 'buscador' ? 'friends-tab--active' : '']"
          @click="activeTab = 'buscador'"
        >
          {{ $t('friends.buscador') }}
        </button>
      </div>

      <!-- TAB: Amics -->
      <div v-if="activeTab === 'amigos'" class="friends-content">
        <div v-if="friendsLoading" class="friends-empty">{{ $t('home.loading') }}</div>
        <div v-else-if="friends.length === 0" class="friends-empty">
          <p>{{ $t('friends.no_amigos') }}</p>
          <button type="button" class="friends-empty__link" @click="activeTab = 'buscador'">{{ $t('friends.buscar_amigos') }}</button>
        </div>
        <template v-else>
          <FriendCard
            v-for="friend in friends"
            :key="friend.id"
            :friend="friend"
            @open-chat="openChat"
            @view-profile="viewProfile"
          />
        </template>
        <div v-if="friendsLastPage > 1" class="friends-paginator">
          <button type="button" class="friends-paginator__btn" :disabled="friendsPage <= 1" @click="changeFriendsPage(friendsPage - 1)">‹</button>
          <template v-for="p in friendsPages" :key="p">
            <button type="button" :class="['friends-paginator__btn', p === friendsPage ? 'friends-paginator__btn--active' : '']" @click="changeFriendsPage(p)">{{ p }}</button>
          </template>
          <button type="button" class="friends-paginator__btn" :disabled="friendsPage >= friendsLastPage" @click="changeFriendsPage(friendsPage + 1)">›</button>
        </div>
      </div>

      <!-- TAB: Pendents -->
      <div v-if="activeTab === 'pendientes'" class="friends-content">
        <div v-if="pendingLoading" class="friends-empty">{{ $t('home.loading') }}</div>
        <div v-else-if="pendingRequests.length === 0" class="friends-empty">
          {{ $t('friends.no_pendientes') }}
        </div>
        <div
          v-for="req in pendingRequests"
          :key="req.id"
          class="friends-request-card"
        >
          <div class="friends-request-card__user cursor-pointer" @click="viewProfile(req.requester.id)">
            <div class="friends-avatar" :style="avatarBackgroundStyle">
              <div class="friends-avatar__inner">
                <img
                  :src="getMonsterImage(req.requester)"
                  alt="Monstre del perfil"
                  class="friends-avatar__img"
                  :style="monsterStyle"
                  decoding="async"
                  draggable="false"
                />
              </div>
            </div>
            <div>
              <p class="friends-request-card__name">{{ req.requester.nom }}</p>
              <p class="friends-request-card__meta">Nivell {{ req.requester.nivell }}</p>
            </div>
          </div>
          <div class="friends-request-card__actions">
            <button type="button" class="friends-btn friends-btn--accept" @click="acceptRequest(req.id)">
              {{ $t('friends.accept') }}
            </button>
            <button type="button" class="friends-btn friends-btn--reject" @click="rejectRequest(req.id)">
              {{ $t('friends.reject') }}
            </button>
          </div>
        </div>
      </div>

      <!-- TAB: Buscador -->
      <div v-if="activeTab === 'buscador'" class="friends-content">
        <div class="friends-search">
          <svg class="friends-search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="$t('friends.search_placeholder')"
            class="friends-search__input"
          />
        </div>
        <div v-if="searching" class="friends-empty">{{ $t('home.loading') }}</div>
        <div v-else-if="paginatedUsers.length > 0" class="friends-search-results">
          <div
            v-for="user in paginatedUsers"
            :key="user.id"
            class="friends-user-card"
          >
            <div class="friends-user-card__info" @click="viewProfile(user.id)">
              <div class="friends-avatar" :style="avatarBackgroundStyle">
                <div class="friends-avatar__inner">
                  <img
                    :src="getMonsterImage(user)"
                    alt="Monstre del perfil"
                    class="friends-avatar__img"
                    :style="monsterStyle"
                    decoding="async"
                    draggable="false"
                  />
                </div>
              </div>
              <div>
                <p class="friends-user-card__name">{{ user.nom }}</p>
                <p class="friends-user-card__meta">Nivell {{ user.nivell }}</p>
              </div>
            </div>
            <button
              type="button"
              class="friends-btn friends-btn--send"
              :disabled="sending"
              @click="sendRequest(user.id)"
            >
              {{ $t('friends.send_request') }}
            </button>
          </div>
          <div v-if="searchLastPage > 1" class="friends-paginator">
            <button type="button" class="friends-paginator__btn" :disabled="searchPage <= 1" @click="changeSearchPage(searchPage - 1)">‹</button>
            <template v-for="p in searchPages" :key="p">
              <button type="button" :class="['friends-paginator__btn', p === searchPage ? 'friends-paginator__btn--active' : '']" @click="changeSearchPage(p)">{{ p }}</button>
            </template>
            <button type="button" class="friends-paginator__btn" :disabled="searchPage >= searchLastPage" @click="changeSearchPage(searchPage + 1)">›</button>
          </div>
        </div>
        <div v-else class="friends-empty">
          {{ searchQuery ? $t('friends.no_results') : $t('friends.search_hint') }}
        </div>
      </div>

      <ChatWindow
        v-if="showChat"
        :friend-id="chatFriendId"
        :friend-name="chatFriendName"
        @close="showChat = false"
      />
    </div>
  </div>
</template>

<script>
import { useFriendshipStore } from "~/stores/useFriendshipStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { authFetch } from "~/composables/useApi.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import HeaderSocial from "~/components/HeaderSocial.vue";
import FriendCard from "~/components/user/social/FriendCard.vue";
import ChatWindow from "~/components/user/social/ChatWindow.vue";
import bosqueImg from "~/assets/img/Bosque.png";

export default {
  name: "FriendsScreen",
  components: {
    HeaderSocial,
    FriendCard,
    ChatWindow,
  },
  data: function () {
    return {
      friendshipStore: useFriendshipStore(),
      bosqueImg: bosqueImg,
      activeTab: "amigos",
      searchQuery: "",
      allUsers: [],
      searchResults: [],
      showChat: false,
      chatFriendId: null,
      chatFriendName: "",
      searching: false,
      sending: false,
      searchPage: 1,
      searchLastPage: 1,
      searchTotal: 0,
    };
  },
  computed: {
    filteredUsers: function () {
      var friendsIds = {};
      var pendingIds = {};
      var allFriends = this.friendshipStore?.friends || [];
      var i;
      for (i = 0; i < allFriends.length; i++) {
        if (allFriends[i] && allFriends[i].friend && allFriends[i].friend.id) {
          friendsIds[allFriends[i].friend.id] = true;
        }
      }
      var allPending = this.friendshipStore?.pendingRequests || [];
      var j;
      for (j = 0; j < allPending.length; j++) {
        if (allPending[j] && allPending[j].requester && allPending[j].requester.id) {
          pendingIds[allPending[j].requester.id] = true;
        }
        if (allPending[j] && allPending[j].addressee && allPending[j].addressee.id) {
          pendingIds[allPending[j].addressee.id] = true;
        }
      }
      var users = this.allUsers.filter(function (user) {
        return user.id && !friendsIds[user.id] && !pendingIds[user.id];
      });
      if (!this.searchQuery) {
        return users;
      }
      var query = this.searchQuery.toLowerCase();
      return users.filter(function (user) {
        return user.nom && user.nom.toLowerCase().indexOf(query) !== -1;
      });
    },
    friends: function () {
      return this.friendshipStore?.friends || [];
    },
    friendsLoading: function () {
      return this.friendshipStore?.loading || false;
    },
    friendsPage: function () {
      return this.friendshipStore?.friendsPage || 1;
    },
    friendsLastPage: function () {
      return this.friendshipStore?.friendsLastPage || 1;
    },
    friendsPages: function () {
      var pages = [];
      for (var i = 1; i <= this.friendsLastPage; i++) {
        pages.push(i);
      }
      return pages;
    },
    pendingRequests: function () {
      return this.friendshipStore?.pendingRequests || [];
    },
    pendingLoading: function () {
      return this.friendshipStore?.loading || false;
    },
    pendingCount: function () {
      return (this.friendshipStore?.pendingRequests || []).length;
    },
    avatarBackgroundStyle: function () {
      return {
        backgroundImage: "url(" + this.bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    },
    paginatedUsers: function () {
      var start = (this.searchPage - 1) * 8;
      var end = start + 8;
      return this.filteredUsers.slice(start, end);
    },
    searchPages: function () {
      var pages = [];
      for (var i = 1; i <= this.searchLastPage; i++) {
        pages.push(i);
      }
      return pages;
    },
    monsterStyle: function () {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
      };
    },
  },
  mounted: async function () {
    if (this.friendshipStore) {
      await this.friendshipStore.fetchFriendsList();
      await this.friendshipStore.fetchPendingRequests();
      await this.fetchAllUsers();
    }
  },
  methods: {
    getMonsterImage: function (user) {
      return getMonsterImageFromUser(user);
    },
    fetchAllUsers: async function () {
      this.searching = true;
      try {
        var resposta = await authFetch("/api/users?search=");
        if (resposta.ok) {
          var dades = await resposta.json();
          this.allUsers = dades.data || dades || [];
          this.searchTotal = dades.total || this.allUsers.length;
          this.searchLastPage = dades.last_page || Math.ceil(this.allUsers.length / 8) || 1;
          this.searchResults = this.allUsers;
        }
      } catch (e) {
        console.error("Error carregant usuaris:", e);
      } finally {
        this.searching = false;
      }
    },
    sendRequest: async function (addresseeId) {
      this.sending = true;
      try {
        await this.friendshipStore.sendFriendRequest(addresseeId);
        this.allUsers = this.allUsers.filter(function (u) {
          return u.id !== addresseeId;
        });
      } catch (e) {
        alert(e.message);
      } finally {
        this.sending = false;
      }
    },
    acceptRequest: async function (id) {
      await this.friendshipStore.acceptFriendRequest(id);
    },
    rejectRequest: async function (id) {
      await this.friendshipStore.rejectFriendRequest(id);
    },
    openChat: function (friendId, friendNom) {
      var friend = null;
      var allFriends = this.friendshipStore?.friends || [];
      for (var i = 0; i < allFriends.length; i++) {
        if (allFriends[i].friend && allFriends[i].friend.id === friendId) {
          friend = allFriends[i].friend;
          break;
        }
      }
      this.chatFriendId = friendId;
      this.chatFriendName = friend ? friend.nom : (friendNom || "");
      this.showChat = true;
    },
    viewProfile: function (userId) {
      if (userId) {
        var authStore = useAuthStore();
        if (userId === authStore.user?.id) {
          this.$router.push('/perfil');
        } else {
          this.$router.push('/user/' + userId);
        }
      }
    },
    changeFriendsPage: function (page) {
      if (page < 1 || page > this.friendsLastPage) return;
      this.friendshipStore.fetchFriendsList(page);
    },
    changeSearchPage: function (page) {
      if (page < 1 || page > this.searchLastPage) return;
      this.searchPage = page;
    },
  },
};
</script>

<style scoped>
.friends-page {
  font-family: "Comfortaa", system-ui, sans-serif;
}

.friends-tabs {
  display: flex;
  gap: 0;
  background: rgba(8, 8, 8, 0.32);
  border-radius: 10px;
  padding: 4px;
  margin-bottom: 16px;
}

.friends-tab {
  flex: 1;
  border: 0;
  background: transparent;
  color: #FAF9F9;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  padding: 10px 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.friends-tab:hover {
  background: rgba(255, 255, 255, 0.1);
}

.friends-tab--active {
  background: #79D45D;
  color: #fff;
  font-weight: 700;
}

.friends-tab__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  margin-left: 4px;
  border-radius: 999px;
  background: #FF6B8A;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  line-height: 1;
}

.friends-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.friends-empty {
  text-align: center;
  padding: 32px 16px;
  color: #FAF9F9;
  font-size: 14px;
  opacity: 0.8;
}

.friends-empty__link {
  display: inline-block;
  margin-top: 8px;
  border: 0;
  background: transparent;
  color: #79D45D;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: underline;
}

/* Avatar */
.friends-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.friends-avatar__inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.15);
  padding: 1px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.friends-avatar__img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

/* Request cards (pendents) */
.friends-request-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #FAF9F9;
  border-radius: 10px;
  padding: 12px 14px;
}

.friends-request-card__user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.friends-request-card__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #2b2d42;
}

.friends-request-card__meta {
  margin: 0;
  font-size: 12px;
  color: #707070;
}

.friends-request-card__actions {
  display: flex;
  gap: 6px;
  flex-shrink: 0;
}

/* Buttons */
.friends-btn {
  border: 0;
  border-radius: 10px;
  padding: 8px 14px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.15s, opacity 0.15s;
}

.friends-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.friends-btn--accept {
  background: #79D45D;
  border: 2px solid #6FBC58;
  color: #fff;
}

.friends-btn--reject {
  background: #E6E6E6;
  color: #5b5b5b;
}

.friends-btn--reject:hover {
  background: #d9d9d9;
}

.friends-btn--send {
  background: #79D45D;
  border: 2px solid #6FBC58;
  color: #fff;
}

.friends-btn--send:hover {
  filter: brightness(0.97);
}

/* Search */
.friends-search {
  position: relative;
  display: flex;
  align-items: center;
  background: #FAF9F9;
  border-radius: 10px;
  overflow: hidden;
}

.friends-search__icon {
  position: absolute;
  left: 14px;
  color: #d8d8d8;
  pointer-events: none;
}

.friends-search__input {
  width: 100%;
  border: 0;
  background: transparent;
  padding: 14px 14px 14px 44px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  color: #2b2d42;
  outline: none;
}

.friends-search__input::placeholder {
  color: #b0b0b0;
}

.friends-search__input:focus {
  box-shadow: inset 0 0 0 2px rgba(121, 212, 93, 0.4);
  border-radius: 10px;
}

/* Search results / user cards */
.friends-search-results {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.friends-user-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #FAF9F9;
  border-radius: 10px;
  padding: 12px 14px;
}

.friends-user-card__info {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  min-width: 0;
}

.friends-user-card__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #2b2d42;
}

.friends-user-card__meta {
  margin: 0;
  font-size: 12px;
  color: #707070;
}

/* Paginator */
.friends-paginator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 12px;
}

.friends-paginator__btn {
  min-width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 10px;
  background: rgba(250, 249, 249, 0.3);
  color: #FAF9F9;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}

.friends-paginator__btn:hover:not(:disabled) {
  background: rgba(250, 249, 249, 0.5);
}

.friends-paginator__btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.friends-paginator__btn--active {
  background: #79D45D;
  color: #fff;
}
</style>
