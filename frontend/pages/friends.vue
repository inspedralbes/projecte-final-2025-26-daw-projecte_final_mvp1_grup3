<template>
  <div class="min-h-screen overflow-x-hidden pb-24 lg:pb-8">
    <div class="max-w-2xl mx-auto min-w-0 px-2 sm:px-4 md:px-6 pt-2 sm:pt-3">
      <div
        class="rounded-2xl sm:rounded-3xl overflow-hidden bg-white shadow-md border border-gray-100"
      >
        <HeaderSocial />
        <div class="px-3 sm:px-5 py-4 sm:py-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $t('friends.title') }}</h1>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="flex border-b border-gray-100">
          <button
            @click="activeTab = 'amigos'"
            :class="['flex-1 py-3 font-medium text-sm transition-all', activeTab === 'amigos' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700']"
          >
            {{ $t('friends.amigos') }}
          </button>
          <button
            @click="activeTab = 'pendientes'"
            :class="['flex-1 py-3 font-medium text-sm transition-all', activeTab === 'pendientes' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700']"
          >
            {{ $t('friends.pendientes') }}
            <span v-if="pendingCount > 0" class="ml-1 px-1.5 py-0.5 text-xs bg-orange-500 text-white rounded-full">{{ pendingCount }}</span>
          </button>
          <button
            @click="activeTab = 'buscador'"
            :class="['flex-1 py-3 font-medium text-sm transition-all', activeTab === 'buscador' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700']"
          >
            {{ $t('friends.buscador') }}
          </button>
        </div>
      </div>

      <div v-if="activeTab === 'amigos'" class="space-y-3">
        <div v-if="friendsLoading" class="text-center py-8 text-gray-500">{{ $t('home.loading') }}</div>
        <div v-else-if="friends.length === 0" class="text-center py-8 text-gray-500">
          <p>{{ $t('friends.no_amigos') }}</p>
          <button @click="activeTab = 'buscador'" class="mt-2 text-blue-600 hover:underline">{{ $t('friends.buscar_amigos') }}</button>
        </div>
        <FriendCard
          v-for="friend in friends"
          :key="friend.id"
          :friend="friend"
          @open-chat="openChat"
          @view-profile="viewProfile"
        />
        <div v-if="friendsLastPage > 1" class="flex justify-center items-center gap-2 mt-4">
          <button
            @click="changeFriendsPage(friendsPage - 1)"
            :disabled="friendsPage <= 1"
            class="px-3 py-1 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100"
          >
            Anterior
          </button>
          <template v-for="p in friendsPages" :key="p">
            <button
              @click="changeFriendsPage(p)"
              :class="['px-3 py-1 text-sm rounded-lg border', p === friendsPage ? 'bg-blue-500 text-white border-blue-500' : 'border-gray-300 hover:bg-gray-100']"
            >
              {{ p }}
            </button>
          </template>
          <button
            @click="changeFriendsPage(friendsPage + 1)"
            :disabled="friendsPage >= friendsLastPage"
            class="px-3 py-1 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100"
          >
            Següent
          </button>
        </div>
      </div>

      <div v-if="activeTab === 'pendientes'" class="space-y-3">
        <div v-if="pendingLoading" class="text-center py-8 text-gray-500">{{ $t('home.loading') }}</div>
        <div v-else-if="pendingRequests.length === 0" class="text-center py-8 text-gray-500">
          {{ $t('friends.no_pendientes') }}
        </div>
        <div
          v-for="req in pendingRequests"
          :key="req.id"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full overflow-hidden shadow-inner" :style="avatarBackgroundStyle">
              <div class="w-full h-full rounded-full border border-gray-200 bg-white/20 p-1 flex items-center justify-center">
                <img
                  :src="getMonsterImage(req.requester)"
                  alt="Monstre del perfil"
                  class="w-full h-full object-contain"
                  :style="monsterStyle"
                  decoding="async"
                  draggable="false"
                />
              </div>
            </div>
            <div>
              <p class="font-medium text-gray-800">{{ req.requester.nom }}</p>
              <p class="text-xs text-gray-500">Nivell {{ req.requester.nivell }}</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="acceptRequest(req.id)" class="px-3 py-1.5 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600">
              {{ $t('friends.accept') }}
            </button>
            <button @click="rejectRequest(req.id)" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">
              {{ $t('friends.reject') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'buscador'" class="space-y-3">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="$t('friends.search_placeholder')"
            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500"
          />
          <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <div v-if="searching" class="text-center py-8 text-gray-500">{{ $t('home.loading') }}</div>
        <div v-else-if="paginatedUsers.length > 0" class="space-y-2">
          <div
            v-for="user in paginatedUsers"
            :key="user.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between"
          >
            <div @click="viewProfile(user.id)" class="flex items-center gap-3 cursor-pointer">
              <div class="w-10 h-10 rounded-full overflow-hidden shadow-inner" :style="avatarBackgroundStyle">
                <div class="w-full h-full rounded-full border border-gray-200 bg-white/20 p-1 flex items-center justify-center">
                  <img
                    :src="getMonsterImage(user)"
                    alt="Monstre del perfil"
                    class="w-full h-full object-contain"
                    :style="monsterStyle"
                    decoding="async"
                    draggable="false"
                  />
                </div>
              </div>
              <div>
                <p class="font-medium text-gray-800">{{ user.nom }}</p>
                <p class="text-xs text-gray-500">Nivell {{ user.nivell }}</p>
              </div>
            </div>
            <button
              @click="sendRequest(user.id)"
              :disabled="sending"
              class="px-3 py-1.5 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 disabled:opacity-50"
            >
              {{ $t('friends.send_request') }}
            </button>
          </div>
          <div v-if="searchLastPage > 1" class="flex justify-center items-center gap-2 mt-4">
            <button
              @click="changeSearchPage(searchPage - 1)"
              :disabled="searchPage <= 1"
              class="px-3 py-1 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100"
            >
              Anterior
            </button>
            <template v-for="p in searchPages" :key="p">
              <button
                @click="changeSearchPage(p)"
                :class="['px-3 py-1 text-sm rounded-lg border', p === searchPage ? 'bg-blue-500 text-white border-blue-500' : 'border-gray-300 hover:bg-gray-100']"
              >
                {{ p }}
              </button>
            </template>
            <button
              @click="changeSearchPage(searchPage + 1)"
              :disabled="searchPage >= searchLastPage"
              class="px-3 py-1 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100"
            >
              Següent
            </button>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          {{ searchQuery ? $t('friends.no_results') : $t('friends.search_hint') }}
        </div>
      </div>

        </div>
      </div>

      <ChatWindow
        v-if="showChat"
        :friend-id="chatFriendId"
        :friend-name="chatFriendName"
        @close="showChat = false"
      />

      <PublicProfileView
        v-if="showProfile"
        :user-id="profileUserId"
        @close="showProfile = false"
      />
    </div>
  </div>
</template>

<script>
import { useFriendshipStore } from "~/stores/useFriendshipStore.js";
import { authFetch } from "~/composables/useApi.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import HeaderSocial from "~/components/HeaderSocial.vue";
import FriendCard from "~/components/user/social/FriendCard.vue";
import ChatWindow from "~/components/user/social/ChatWindow.vue";
import PublicProfileView from "~/components/user/social/PublicProfileView.vue";
import bosqueImg from "~/assets/img/Bosque.png";

export default {
  name: "FriendsScreen",
  components: {
    HeaderSocial,
    FriendCard,
    ChatWindow,
    PublicProfileView,
  },
  data() {
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
      showProfile: false,
      profileUserId: null,
      searching: false,
      sending: false,
      searchPage: 1,
      searchLastPage: 1,
      searchTotal: 0,
    };
  },
  computed: {
    filteredUsers() {
      var self = this;
      var friendsIds = {};
      var pendingIds = {};
      var allFriends = this.friendshipStore?.friends || [];
      for (var i = 0; i < allFriends.length; i++) {
        if (allFriends[i] && allFriends[i].friend && allFriends[i].friend.id) {
          friendsIds[allFriends[i].friend.id] = true;
        }
      }
      var allPending = this.friendshipStore?.pendingRequests || [];
      for (var j = 0; j < allPending.length; j++) {
        if (allPending[j] && allPending[j].requester && allPending[j].requester.id) {
          pendingIds[allPending[j].requester.id] = true;
        }
        if (allPending[j] && allPending[j].addressee && allPending[j].addressee.id) {
          pendingIds[allPending[j].addressee.id] = true;
        }
      }
      var users = this.allUsers.filter(function(user) {
        return user.id && !friendsIds[user.id] && !pendingIds[user.id];
      });
      if (!this.searchQuery) {
        return users;
      }
      var query = this.searchQuery.toLowerCase();
      return users.filter(function(user) {
        return user.nom && user.nom.toLowerCase().indexOf(query) !== -1;
      });
    },
    friends() {
      return this.friendshipStore?.friends || [];
    },
    friendsLoading() {
      return this.friendshipStore?.loading || false;
    },
    friendsPage() {
      return this.friendshipStore?.friendsPage || 1;
    },
    friendsLastPage() {
      return this.friendshipStore?.friendsLastPage || 1;
    },
    friendsPages() {
      var pages = [];
      for (var i = 1; i <= this.friendsLastPage; i++) {
        pages.push(i);
      }
      return pages;
    },
    pendingRequests() {
      return this.friendshipStore?.pendingRequests || [];
    },
    pendingLoading() {
      return this.friendshipStore?.loading || false;
    },
    pendingCount() {
      return (this.friendshipStore?.pendingRequests || []).length;
    },
    avatarBackgroundStyle() {
      return {
        backgroundImage: "url(" + this.bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    },
    filteredUsers() {
      var self = this;
      var friendsIds = {};
      var pendingIds = {};
      var allFriends = this.friendshipStore?.friends || [];
      for (var i = 0; i < allFriends.length; i++) {
        if (allFriends[i] && allFriends[i].friend && allFriends[i].friend.id) {
          friendsIds[allFriends[i].friend.id] = true;
        }
      }
      var allPending = this.friendshipStore?.pendingRequests || [];
      for (var j = 0; j < allPending.length; j++) {
        if (allPending[j] && allPending[j].requester && allPending[j].requester.id) {
          pendingIds[allPending[j].requester.id] = true;
        }
        if (allPending[j] && allPending[j].addressee && allPending[j].addressee.id) {
          pendingIds[allPending[j].addressee.id] = true;
        }
      }
      var users = this.allUsers.filter(function(user) {
        return user.id && !friendsIds[user.id] && !pendingIds[user.id];
      });
      if (!this.searchQuery) {
        return users;
      }
      var query = this.searchQuery.toLowerCase();
      return users.filter(function(user) {
        return user.nom && user.nom.toLowerCase().indexOf(query) !== -1;
      });
    },
    paginatedUsers() {
      var start = (this.searchPage - 1) * 8;
      var end = start + 8;
      return this.filteredUsers.slice(start, end);
    },
    searchPages() {
      var pages = [];
      for (var i = 1; i <= this.searchLastPage; i++) {
        pages.push(i);
      }
      return pages;
    },
    monsterStyle() {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
      };
    },
  },
  async mounted() {
    if (this.friendshipStore) {
      await this.friendshipStore.fetchFriendsList();
      await this.friendshipStore.fetchPendingRequests();
      await this.fetchAllUsers();
    }
  },
  methods: {
    getMonsterImage(user) {
      return getMonsterImageFromUser(user);
    },
    async fetchAllUsers() {
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
    async sendRequest(addresseeId) {
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
    async acceptRequest(id) {
      await this.friendshipStore.acceptFriendRequest(id);
    },
    async rejectRequest(id) {
      await this.friendshipStore.rejectFriendRequest(id);
    },
    openChat(friendId, friendNom) {
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
    viewProfile(userId) {
      if (userId && typeof userId === 'number') {
        this.profileUserId = userId;
        this.showProfile = true;
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