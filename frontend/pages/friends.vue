<template>
  <div class="min-h-screen bg-gray-50">
    <HeaderSocial />

    <div class="max-w-2xl mx-auto px-4 py-6">
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
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <span class="text-blue-600 font-semibold">{{ req.requester.nom.charAt(0) }}</span>
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
            @input="searchUsers"
          />
          <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <div v-if="searchResults.length > 0" class="space-y-2">
          <div
            v-for="user in searchResults"
            :key="user.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between"
          >
            <div @click="viewProfile(user.id)" class="flex items-center gap-3 cursor-pointer">
              <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <span class="text-blue-600 font-semibold">{{ user.nom.charAt(0) }}</span>
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
import { useFriendshipStore } from "~/stores/useFriendshipStore";
import { useAuthStore } from "~/stores/useAuthStore";
import HeaderSocial from "~/components/HeaderSocial.vue";
import FriendCard from "~/components/user/social/FriendCard.vue";
import ChatWindow from "~/components/user/social/ChatWindow.vue";
import PublicProfileView from "~/components/user/social/PublicProfileView.vue";

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
      activeTab: "amigos",
      searchQuery: "",
      searchResults: [],
      showChat: false,
      chatFriendId: null,
      chatFriendName: "",
      showProfile: false,
      profileUserId: null,
      sending: false,
    };
  },
  computed: {
    friends() {
      return this.friendshipStore.friends;
    },
    friendsLoading() {
      return this.friendshipStore.loading;
    },
    pendingRequests() {
      return this.friendshipStore.pendingRequests;
    },
    pendingLoading() {
      return this.friendshipStore.loading;
    },
    pendingCount() {
      return this.pendingRequests.length;
    },
  },
  async mounted() {
    await this.friendshipStore.fetchFriendsList();
    await this.friendshipStore.fetchPendingRequests();
  },
  methods: {
    async searchUsers() {
      if (this.searchQuery.length < 2) {
        this.searchResults = [];
        return;
      }
      try {
        var resposta = await this.$authFetch("/api/users?search=" + this.searchQuery);
        if (resposta.ok) {
          var dades = await resposta.json();
          this.searchResults = dades.data || dades || [];
        }
      } catch (e) {
        console.error("Error cercant usuaris:", e);
      }
    },
    async sendRequest(addresseeId) {
      this.sending = true;
      try {
        await this.friendshipStore.sendFriendRequest(addresseeId);
        this.searchResults = this.searchResults.filter(function (u) {
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
    openChat(friend) {
      this.chatFriendId = friend.id;
      this.chatFriendName = friend.nom;
      this.showChat = true;
    },
    viewProfile(userId) {
      this.profileUserId = userId;
      this.showProfile = true;
    },
  },
};
</script>