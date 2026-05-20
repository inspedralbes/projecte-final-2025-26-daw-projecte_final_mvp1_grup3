<!--
  Component o pagina Nuxt: friends.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="friends-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 bg-transparent">
    <div class="max-w-7xl mx-auto min-w-0 px-3 sm:px-6 pt-2 sm:pt-3">
      <HeaderSocial />

      <!-- Nou Filtre estil Plantilles -->
      <div class="templates-filter-wrap mb-6" :class="{ 'templates-filter-wrap--searching': searchVisible }">
        <div class="templates-filter-row">
          <div class="templates-filter-search" :class="{ 'templates-filter-search--active': searchVisible }">
            <button
              type="button"
              class="templates-filter-decor"
              :aria-label="searchVisible ? $t('friends.close_search') : $t('friends.open_search')"
              @click="toggleSearch"
            >
              <svg
                class="templates-filter-decor__lupa"
                width="33"
                height="33"
                viewBox="0 0 33 33"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M28.875 28.875L22.8937 22.8937M26.125 15.125C26.125 21.2001 21.2001 26.125 15.125 26.125C9.04987 26.125 4.125 21.2001 4.125 15.125C4.125 9.04987 9.04987 4.125 15.125 4.125C21.2001 4.125 26.125 9.04987 26.125 15.125Z"
                  :stroke="searchVisible ? '#79D45D' : '#d8d8d8'"
                  stroke-width="4"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </button>
            <input
              v-if="searchVisible"
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              class="templates-filter-search-input"
              :placeholder="$t('friends.search_placeholder')"
            />
          </div>

          <div class="templates-filter-card">
            <label for="filterFriends" class="sr-only">{{ $t('friends.filter') }}</label>
            <select
              id="filterFriends"
              v-model="activeTab"
              class="templates-filter-select"
            >
              <option value="amigos">{{ $t('friends.amigos') }}</option>
              <option value="pendientes">{{ $t('friends.pendientes') }}</option>
              <option value="buscador">{{ $t('friends.buscador') }}</option>
            </select>
            <span class="templates-filter-chevron" aria-hidden="true"></span>
          </div>
        </div>
      </div>

      <div class="space-y-12">
        
        <!-- TAB: Amics -->
        <div v-if="activeTab === 'amigos'" class="template-section">
          <div v-if="friendsLoading" class="text-center py-6 text-white">{{ $t('home.loading') }}</div>
          <div v-else-if="filteredFriends.length === 0" class="text-center py-6 text-white">
            <p>{{ $t('friends.no_amigos') }}</p>
            <button type="button" class="friends-empty__link" @click="activeTab = 'buscador'">{{ $t('friends.buscar_amigos') }}</button>
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="friendObj in filteredFriends"
              :key="friendObj.id"
              class="template-expandable"
              :class="isUserExpandida(friendObj.friend.id) ? 'template-expandable--active' : ''"
            >
              <div class="template-card w-full text-left relative">
                <button type="button" class="absolute inset-0 w-full h-full" style="z-index: 1;" @click="toggleUserExpandida(friendObj.friend.id)" aria-label="Desplegar"></button>
                <div class="friend-avatar-ring cursor-pointer" style="z-index: 2;" :style="getAvatarBgStyle(friendObj.friend)" @click.stop="viewProfile(friendObj.friend.id)">
                  <img v-if="getMonsterImage(friendObj.friend)" :src="getMonsterImage(friendObj.friend)" class="friend-avatar-ring__img" />
                </div>
                <div class="template-card__content relative" style="z-index: 2; pointer-events: none;">
                  <p class="template-card__title pointer-events-auto inline-block">{{ friendObj.friend.nom }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">{{ $t('friends.level_short', { n: friendObj.friend.nivell || 1 }) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="isUserExpandida(friendObj.friend.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click.stop="tancarUserExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="grid grid-cols-3 gap-2 w-full">
                      <button type="button" class="template-expand-btn w-full text-center px-1" style="background:#5B9CE6; border: 2px solid #4A83C4; color: white;" @click="openChat(friendObj.friend.id, friendObj.friend.nom)">{{ $t('friends.message') }}</button>
                      <button type="button" class="template-expand-btn w-full text-center px-1" style="background:#D14D6B; border: 2px solid #B03A55; color: white;" @click="removeFriend(friendObj.id, friendObj.friend.nom)">{{ $t('social.delete') }}</button>
                      <button type="button" class="template-expand-btn w-full text-center px-1" style="background:#FF8DA6; border: 2px solid #E6778F; color: white;" @click="reportUser(friendObj.friend.id)">{{ $t('friends.report') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="friendsLastPage > 1" class="friends-paginator">
            <button type="button" class="friends-paginator__btn" :disabled="friendsPage <= 1" @click="changeFriendsPage(friendsPage - 1)">‹</button>
            <template v-for="p in friendsPages" :key="p">
              <button type="button" :class="['friends-paginator__btn', p === friendsPage ? 'friends-paginator__btn--active' : '']" @click="changeFriendsPage(p)">{{ p }}</button>
            </template>
            <button type="button" class="friends-paginator__btn" :disabled="friendsPage >= friendsLastPage" @click="changeFriendsPage(friendsPage + 1)">›</button>
          </div>
        </div>

        <!-- TAB: Pendents -->
        <div v-if="activeTab === 'pendientes'" class="template-section">
          <div v-if="pendingLoading" class="text-center py-6 text-white">{{ $t('home.loading') }}</div>
          <div v-else-if="filteredPending.length === 0" class="text-center py-6 text-white">
            {{ $t('friends.no_pendientes') }}
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="req in filteredPending"
              :key="req.id"
              class="template-expandable"
              :class="isUserExpandida(req.requester.id) ? 'template-expandable--active' : ''"
            >
              <div class="template-card w-full text-left relative">
                <button type="button" class="absolute inset-0 w-full h-full" style="z-index: 1;" @click="toggleUserExpandida(req.requester.id)" aria-label="Desplegar"></button>
                <div class="friend-avatar-ring cursor-pointer" style="z-index: 2;" :style="getAvatarBgStyle(req.requester)" @click.stop="viewProfile(req.requester.id)">
                  <img v-if="getMonsterImage(req.requester)" :src="getMonsterImage(req.requester)" class="friend-avatar-ring__img" />
                </div>
                <div class="template-card__content relative" style="z-index: 2; pointer-events: none;">
                  <p class="template-card__title pointer-events-auto inline-block">{{ req.requester.nom }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">{{ $t('friends.level_short', { n: req.requester.nivell || 1 }) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="isUserExpandida(req.requester.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click.stop="tancarUserExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 w-full">
                      <button type="button" class="template-expand-btn template-expand-btn--primary w-full text-center px-0.5" @click="acceptRequest(req.id)">{{ $t('friends.accept') }}</button>
                      <button type="button" class="template-expand-btn w-full text-center px-0.5" style="background:#5B9CE6; border: 2px solid #4A83C4; color: white;" @click="openChat(req.requester.id, req.requester.nom)">{{ $t('friends.message') }}</button>
                      <button type="button" class="template-expand-btn w-full text-center px-0.5" style="background:#D14D6B; border: 2px solid #B03A55; color: white;" @click="rejectRequest(req.id)">{{ $t('friends.reject') }}</button>
                      <button type="button" class="template-expand-btn w-full text-center px-0.5" style="background:#FF8DA6; border: 2px solid #E6778F; color: white;" @click="reportUser(req.requester.id)">Reportar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB: Buscador -->
        <div v-if="activeTab === 'buscador'" class="template-section">
          <div v-if="searching" class="text-center py-6 text-white">{{ $t('home.loading') }}</div>
          <div v-else-if="paginatedUsers.length > 0" class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="user in paginatedUsers"
              :key="user.id"
              class="template-expandable"
              :class="isUserExpandida(user.id) ? 'template-expandable--active' : ''"
            >
              <div class="template-card w-full text-left relative">
                <button type="button" class="absolute inset-0 w-full h-full" style="z-index: 1;" @click="toggleUserExpandida(user.id)" aria-label="Desplegar"></button>
                <div class="friend-avatar-ring cursor-pointer" style="z-index: 2;" :style="getAvatarBgStyle(user)" @click.stop="viewProfile(user.id)">
                  <img v-if="getMonsterImage(user)" :src="getMonsterImage(user)" class="friend-avatar-ring__img" />
                </div>
                <div class="template-card__content relative" style="z-index: 2; pointer-events: none;">
                  <p class="template-card__title pointer-events-auto inline-block">{{ user.nom }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">{{ $t('friends.level_short', { n: user.nivell || 1 }) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="isUserExpandida(user.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click.stop="tancarUserExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="grid grid-cols-3 gap-2 w-full">
                      <button
                        type="button"
                        class="template-expand-btn template-expand-btn--primary friend-req-btn w-full text-center px-0.5"
                        :class="{ 'friend-req-btn--sent': sentRequests[user.id] }"
                        :disabled="sending || sentRequests[user.id]"
                        @click="sendRequest(user.id)"
                      >
                        <span v-if="sentRequests[user.id]" class="friend-req-btn__done inline-flex items-center justify-center gap-1">
                          <svg class="friend-req-btn__tick shrink-0" width="10" height="10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          {{ $t('friends.request_sent') }}
                        </span>
                        <span v-else>{{ $t('friends.send_request') }}</span>
                      </button>
                      <button type="button" class="template-expand-btn w-full text-center px-0.5" style="background:#5B9CE6; border: 2px solid #4A83C4; color: white;" @click="openChat(user.id, user.nom)">{{ $t('friends.message') }}</button>
                      <button type="button" class="template-expand-btn w-full text-center px-0.5" style="background:#FF8DA6; border: 2px solid #E6778F; color: white;" @click="reportUser(user.id)">{{ $t('friends.report') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
          <div v-if="searchLastPageComputed > 1" class="friends-paginator mt-4">
            <button type="button" class="friends-paginator__btn" :disabled="searchPage <= 1" @click="changeSearchPage(searchPage - 1)">‹</button>
            <template v-for="p in searchPagesComputed" :key="p">
              <button type="button" :class="['friends-paginator__btn', p === searchPage ? 'friends-paginator__btn--active' : '']" @click="changeSearchPage(p)">{{ p }}</button>
            </template>
            <button type="button" class="friends-paginator__btn" :disabled="searchPage >= searchLastPageComputed" @click="changeSearchPage(searchPage + 1)">›</button>
          </div>
          <div v-if="!searching && paginatedUsers.length === 0" class="text-center py-6 text-white">
            {{ searchQuery ? $t('friends.no_results') : $t('friends.no_users') }}
          </div>
        </div>

      </div>

      <ChatWindow
        v-if="showChat"
        :friend-id="chatFriendId"
        :friend-name="chatFriendName"
        :friend-monstre-tipus="chatFriendMonstreTipus"
        :friend-nivell="chatFriendNivell"
        @close="showChat = false"
      />

      <ReportUserModal
        :show="showReportModal"
        report-type="user"
        :content-id="reportUserId"
        @close="showReportModal = false"
        @submit="handleReportSubmit"
      />

      <UserSocialConfirmModal
        :show="showRemoveFriendConfirm"
        :title="$t('friends.remove_title')"
        :message="$t('friends.remove_message', { name: removeFriendName || '' })"
        :confirm-text="$t('social.delete')"
        @confirm="confirmRemoveFriend"
        @cancel="showRemoveFriendConfirm = false"
      />
    </div>
  </div>
</template>

<script>
import { useFriendshipStore } from "~/stores/useFriendshipStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { authFetch } from "~/composables/useApi.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsAplicacioImg from "~/assets/img/Fons/Fons_Aplicacio.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import HeaderSocial from "~/components/HeaderSocial.vue";
import ChatWindow from "~/components/user/social/ChatWindow.vue";
import ReportUserModal from "~/components/user/social/ReportUserModal.vue";
import UserSocialConfirmModal from "~/components/user/social/ConfirmModal.vue";

export default {
  name: "FriendsScreen",
  components: {
    HeaderSocial,
    ChatWindow,
    ReportUserModal,
    UserSocialConfirmModal
  },
  data: function () {
    return {
      bosqueImg: bosqueImg,
      friendshipStore: useFriendshipStore(),
      activeTab: "amigos",
      searchQuery: "",
      searchVisible: false,
      expandedUserId: null,
      allUsers: [],
      showChat: false,
      chatFriendId: null,
      chatFriendName: "",
      chatFriendMonstreTipus: null,
      chatFriendNivell: null,
      searching: false,
      sending: false,
      sentRequests: {},
      searchPage: 1,
      searchLastPage: 1,
      searchTotal: 0,
      showReportModal: false,
      reportUserId: null,
      showRemoveFriendConfirm: false,
      removeFriendId: null,
      removeFriendName: null,
    };
  },
  computed: {
    friends: function () {
      return this.friendshipStore?.friends || [];
    },
    pendingRequests: function () {
      return this.friendshipStore?.pendingRequests || [];
    },
    filteredFriends: function () {
      var arr = this.friends;
      if (!this.searchQuery) return arr;
      var query = this.searchQuery.toLowerCase();
      return arr.filter(f => f.friend && f.friend.nom && f.friend.nom.toLowerCase().indexOf(query) !== -1);
    },
    filteredPending: function () {
      var arr = this.pendingRequests;
      if (!this.searchQuery) return arr;
      var query = this.searchQuery.toLowerCase();
      return arr.filter(r => r.requester && r.requester.nom && r.requester.nom.toLowerCase().indexOf(query) !== -1);
    },
    filteredUsers: function () {
      var friendsIds = {};
      var pendingIds = {};
      var i, j;
      for (i = 0; i < this.friends.length; i++) {
        if (this.friends[i] && this.friends[i].friend) {
          friendsIds[this.friends[i].friend.id] = true;
        }
      }
      for (j = 0; j < this.pendingRequests.length; j++) {
        var p = this.pendingRequests[j];
        if (p && p.requester) pendingIds[p.requester.id] = true;
        if (p && p.addressee) pendingIds[p.addressee.id] = true;
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
    paginatedUsers: function () {
      var perPage = 10;
      var start = (this.searchPage - 1) * perPage;
      var end = start + perPage;
      return this.filteredUsers.slice(start, end);
    },
    searchLastPageComputed: function () {
      return Math.ceil(this.filteredUsers.length / 10) || 1;
    },
    friendsLoading: function () {
      return this.friendshipStore?.loading || false;
    },
    pendingLoading: function () {
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
    searchPages: function () {
      var pages = [];
      for (var i = 1; i <= this.searchLastPage; i++) {
        pages.push(i);
      }
      return pages;
    },
    searchPagesComputed: function () {
      var pages = [];
      for (var i = 1; i <= this.searchLastPageComputed; i++) {
        pages.push(i);
      }
      return pages;
    },
  },
  watch: {
    searchQuery: function () {
      this.searchPage = 1;
    }
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
      var skinKey = user ? user.skin_key : null;
      return getMonsterImageFromUser(user, skinKey);
    },
    getAvatarBgStyle: function (user) {
      var fonsKey = user ? user.fons_key : null;
      var bg = bosqueImg;
      if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
      else if (fonsKey === "fons_casa") bg = fonsCasaImg;
      else if (fonsKey === "fons_aplicacio") bg = fonsAplicacioImg;
      return { backgroundImage: "url(" + bg + ")" };
    },
    toggleSearch: function () {
      this.searchVisible = !this.searchVisible;
      if (!this.searchVisible) {
        this.searchQuery = "";
      } else {
        this.$nextTick(() => {
          if (this.$refs.searchInput) this.$refs.searchInput.focus();
        });
      }
    },
    toggleUserExpandida: function(id) {
      if (this.expandedUserId === id) {
        this.expandedUserId = null;
      } else {
        this.expandedUserId = id;
      }
    },
    isUserExpandida: function(id) {
      return this.expandedUserId === id;
    },
    tancarUserExpandida: function() {
      this.expandedUserId = null;
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
        this.sentRequests = Object.assign({}, this.sentRequests, { [addresseeId]: true });
      } catch (e) {
        await this.$loopyModal.error("Error", e.message);
      } finally {
        this.sending = false;
      }
    },
    acceptRequest: async function (id) {
      await this.friendshipStore.acceptFriendRequest(id);
      this.tancarUserExpandida();
    },
    rejectRequest: async function (id) {
      await this.friendshipStore.rejectFriendRequest(id);
      this.tancarUserExpandida();
    },
    removeFriend: function (id, nom) {
      this.removeFriendId = id;
      this.removeFriendName = nom || null;
      this.showRemoveFriendConfirm = true;
      this.tancarUserExpandida();
    },
    confirmRemoveFriend: async function () {
      var friendshipId = this.removeFriendId;
      this.showRemoveFriendConfirm = false;
      this.removeFriendId = null;
      this.removeFriendName = null;
      if (!friendshipId) {
        return;
      }
      try {
        await this.friendshipStore.removeFriend(friendshipId);
        this.tancarUserExpandida();
      } catch (e) {
        await this.$loopyModal.error("Error", e.message || "No s'ha pogut eliminar l'amic");
      }
    },
    reportUser: function (id) {
      this.reportUserId = id;
      this.showReportModal = true;
      this.tancarUserExpandida();
    },
    handleReportSubmit: async function(reportData) {
      const motiusMap = {
        nom: "Nom inapropiat",
        insult: "Text insultant",
        us_indegut: "Ús indegut de l'app",
        comentari: "Comentari ofensiu",
        altres: "Altres"
      };
      const motiuText = motiusMap[reportData.motiu] || reportData.motiu;
      const reasonText = "[" + motiuText + "]" + (reportData.detalls ? " - " + reportData.detalls : "");

      try {
        const resposta = await authFetch("/api/social/report", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            content_type: "user",
            content_id: reportData.contentId,
            motiu: motiuText,
            detalls: reportData.detalls || ""
          })
        });
        if (resposta.ok) {
          this.showReportModal = false;
          var self = this;
          setTimeout(function () {
            self.$loopyModal.success("Report", "Gràcies! L'usuari ha sigut reportat i ho revisarem.");
          }, 300);
        } else {
          await this.$loopyModal.error("Error", "Error a l'enviar el report. Si us plau, torna-ho a provar.");
        }
      } catch (e) {
        console.error("Error reportant usuari:", e);
        await this.$loopyModal.error("Error", "Error de connexió a l'enviar el report.");
      }
    },
    openChat: function (friendId, friendNom) {
      this.chatFriendId = friendId;
      this.chatFriendName = friendNom || "";
      var friendData = null;
      var friendObj = this.friends.find(function(f) { return f.friend && Number(f.friend.id) === Number(friendId); });
      if (friendObj && friendObj.friend) friendData = friendObj.friend;
      if (!friendData) {
        friendData = this.allUsers.find(function(u) { return Number(u.id) === Number(friendId); });
      }
      this.chatFriendMonstreTipus = friendData ? friendData.monstre_tipus : null;
      this.chatFriendNivell = friendData ? friendData.nivell : null;
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
      if (page < 1 || page > this.searchLastPageComputed) return;
      this.searchPage = page;
    },
  },
};
</script>

<style scoped>
.friends-page {
  font-family: "Comfortaa", system-ui, sans-serif;
}

.templates-filter-wrap {
  width: 100%;
}

.templates-filter-row {
  display: flex;
  align-items: stretch;
  gap: 10px;
  width: 100%;
  flex-wrap: wrap;
}

.templates-filter-search {
  display: flex;
  align-items: center;
  width: 58px;
  height: 58px;
  border-radius: 10px;
  background: #faf9f9;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  flex-shrink: 0;
}

.templates-filter-search--active {
  width: 100%;
}

.templates-filter-decor {
  flex-shrink: 0;
  width: 58px;
  height: 58px;
  border: 0;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  outline: none;
}

.templates-filter-search-input {
  flex: 1;
  border: 0;
  background: transparent;
  padding: 0 20px 0 0;
  color: #5e5e5e;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 16px;
  outline: none;
}

.templates-filter-card {
  position: relative;
  flex: 1 1 0;
  min-width: 200px;
  height: 58px;
  border-radius: 10px;
  background: #faf9f9;
  overflow: hidden;
  transition: width 0.3s ease, flex 0.3s ease;
}

.templates-filter-wrap--searching .templates-filter-card {
  flex: 0 0 100%;
}

.templates-filter-select {
  width: 100%;
  height: 100%;
  border: 0;
  background: transparent;
  padding: 0 48px 0 20px;
  color: #5e5e5e;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.2;
  appearance: none;
  outline: none;
}

.templates-filter-chevron {
  position: absolute;
  right: 20px;
  top: 50%;
  width: 14px;
  height: 14px;
  border-right: 5px solid #d8d8d8;
  border-bottom: 5px solid #d8d8d8;
  border-radius: 2px;
  transform: translateY(-62%) rotate(45deg);
  pointer-events: none;
}

.template-expandable {
  overflow: hidden;
  border-radius: 10px;
  max-height: 92px;
  transition: max-height 0.28s ease, background-color 0.2s ease, padding 0.2s ease;
}

.templates-grid {
  --tw-space-y-reverse: 0;
  column-gap: 1.5rem;
  row-gap: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
}

.template-expandable--active {
  background: rgba(0, 0, 0, 0.54);
  padding: 10px;
  max-height: 620px;
}

.template-card {
  position: relative;
  display: grid;
  grid-template-columns: 57px minmax(0, 1fr);
  column-gap: 23px;
  align-items: center;
  width: 100%;
  min-height: 86px;
  padding: 16px 18px;
  background-color: #faf9f9;
  border-radius: 10px;
}

.template-card__content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}

.template-card__mark {
  position: relative;
  width: 57px;
  height: 54px;
}

.template-card__blob {
  display: block;
  width: 57px;
  height: 54px;
}

.friend-avatar-ring {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
  border: 2px solid rgba(121, 212, 93, 0.4);
}

.friend-avatar-ring__img {
  width: 36px;
  height: 36px;
  object-fit: contain;
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.15));
}

.template-card__icona {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  z-index: 1;
  width: 2rem;
  text-align: center;
  font-size: 1.15rem;
  line-height: 1;
}

.template-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.template-card__meta {
  display: flex;
  align-items: center;
  gap: 16px;
  color: #707070;
  font-size: 13px;
  font-weight: 600;
  line-height: 1;
}

.template-card__meta-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #707070;
  line-height: 1;
}

.template-expand-inline {
  animation: template-sheet-up 0.22s ease-out;
  margin-top: 8px;
}

.template-expand-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.template-expand-close,
.template-expand-edit {
  border: 0;
  background: transparent;
  color: #faf9f9;
}

.template-expand-edit {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 16px;
  font-weight: 400;
}

.template-expand-panel {
  background: transparent;
  border-radius: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

.template-spec-card {
  border: 1px solid #ececec;
  border-radius: 10px;
  padding: 10px;
  background: #ffffff;
}

.template-expand-actions {
  display: flex;
  gap: 8px;
  justify-content: space-between;
}

.template-expand-btn {
  border: 0;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 700;
}

.template-expand-btn--danger {
  background: #fee2e2;
  color: #b91c1c;
}

.template-expand-btn--primary {
  background: #79d45d;
  color: #ffffff;
}

.template-expand-btn--secondary {
  background: #dbeafe;
  color: #1d4ed8;
}

@keyframes template-sheet-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
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

/* --- Send Request Button Animation --- */
.friend-req-btn {
  position: relative;
  transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.15s ease;
  overflow: hidden;
}

.friend-req-btn--sent {
  background-color: #ecfdf3 !important;
  border: 2px solid #79d45d !important;
  color: #22c55e !important;
  cursor: default;
  animation: friend-req-pop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.friend-req-btn__done {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
}

.friend-req-btn__tick {
  animation: friend-req-tick-draw 0.4s ease-out 0.1s both;
}

@keyframes friend-req-pop {
  0% { transform: scale(1); }
  40% { transform: scale(1.12); }
  70% { transform: scale(0.95); }
  100% { transform: scale(1); }
}

@keyframes friend-req-tick-draw {
  0% {
    opacity: 0;
    transform: scale(0) rotate(-45deg);
  }
  50% {
    opacity: 1;
    transform: scale(1.2) rotate(0deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}
</style>
