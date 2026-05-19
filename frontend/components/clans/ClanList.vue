<!--
  Component o pagina Nuxt: ClanList.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="clan-catalog-panel">
    <div class="clan-filter-wrap" :class="{ 'clan-filter-wrap--searching': searchVisible }">
      <div class="clan-filter-row">
        <div class="clan-filter-search" :class="{ 'clan-filter-search--active': searchVisible }">
          <button
            type="button"
            class="clan-filter-decor"
            :aria-label="searchVisible ? 'Tancar cerca' : 'Obrir cerca'"
            @click="toggleSearch"
          >
            <svg
              class="clan-filter-decor__lupa"
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
            class="clan-filter-search-input"
            placeholder="Cercar clans..."
          />
        </div>

        <div class="clan-filter-card">
          <label for="filterClans" class="sr-only">Filtrar clans</label>
          <select
            id="filterClans"
            v-model="filterType"
            class="clan-filter-select"
          >
            <option value="all">Tots els Clans</option>
            <option value="public">Públics</option>
            <option value="private">Privats</option>
          </select>
          <span class="clan-filter-chevron" aria-hidden="true"></span>
        </div>
      </div>
    </div>

    <button
      type="button"
      class="clan-create-trigger"
      @click="obrirSheet"
    >
      <span class="clan-create-trigger__icon" aria-hidden="true">
        <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
          <line x1="17" y1="2" x2="17" y2="31" stroke="white" stroke-width="4" stroke-linecap="round"/>
          <line x1="2" y1="16" x2="31" y2="16" stroke="white" stroke-width="4" stroke-linecap="round"/>
        </svg>
      </span>
    </button>

    <Teleport to="body">
      <Transition name="clan-sheet-backdrop">
        <div
          v-if="sheetObert"
          class="fixed inset-0 z-[80] bg-black/40"
          @click="tancarSheet"
        ></div>
      </Transition>

      <Transition name="clan-sheet-panel">
        <div
          v-if="sheetObert"
          class="fixed left-0 right-0 bottom-0 z-[81] bg-white rounded-t-3xl shadow-2xl max-h-[85vh] flex flex-col pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
          <div class="sticky top-0 z-[1] bg-white rounded-t-3xl flex flex-col items-center shrink-0 border-b border-gray-100 w-full pt-4 px-6">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mb-4"></div>
            <h3 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-[#949494] mb-4 text-center w-full">
              Crear Nou Clan
            </h3>
          </div>

          <form class="sheet-form-plain habit-form flex-1 min-h-0 overflow-y-auto px-6 py-4 space-y-5" @submit.prevent="submitClan">
            <div>
              <label class="habit-form-label" for="clan-nom-list">Nom del Clan</label>
              <input
                id="clan-nom-list"
                v-model="clanForm.nom"
                type="text"
                required
                maxlength="50"
                placeholder="Escriu el nom del clan..."
                class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
              />
            </div>

            <div>
              <HabitFormCategory
                embedded
                :categories="clanCategories"
                :user-categories="userCategories"
                :selected-id="clanForm.categoria_id || ''"
                :category-custom-label="clanForm.userCategoriaEtiqueta || ''"
                :category-custom-icona="clanForm.categoriaIcona || ''"
                :selected-user-category-id="clanForm.userCategoriaId || null"
                @select="onClanCategorySelect"
                @select-user="onClanCategorySelectUser"
                @add-user-category="onClanCategoryAdd"
              />
            </div>

            <div>
              <label class="habit-form-label" for="clan-public-list">Clan Públic</label>
              <SharedTemplatePublicSwitch
                input-id="clan-public-list"
                :model-value="clanForm.es_public"
                @update:model-value="clanForm.es_public = $event"
              />
            </div>

            <div>
              <label class="habit-form-label" for="clan-max-list">Límit de membres</label>
              <select
                id="clan-max-list"
                v-model.number="clanForm.max_membres"
                required
                class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
              >
                <option :value="10">10 membres</option>
                <option :value="15">15 membres</option>
                <option :value="20">20 membres</option>
              </select>
            </div>

            <p v-if="clanFormError" class="text-red-500 text-sm font-semibold">{{ clanFormError }}</p>

            <div class="grid grid-cols-2 gap-3 pt-4">
              <button
                type="button"
                class="flex w-full items-center justify-center border-0 bg-transparent py-2.5 text-base font-normal text-[#5E5E5E] shadow-none outline-none transition hover:opacity-80"
                @click="tancarSheet"
              >
                Cancel·lar
              </button>
              <button
                type="submit"
                :disabled="clanFormLoading"
                class="w-full rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] py-2.5 text-center text-base font-normal text-white transition hover:brightness-[0.97] disabled:opacity-50"
              >
                {{ clanFormLoading ? '...' : 'Crear' }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </Teleport>

    <div v-if="loading" class="clan-catalog-loading">
      <div class="clan-catalog-spinner"></div>
    </div>

    <template v-else-if="filteredClans.length === 0">
      <div class="clan-catalog-empty">
        <p>No s'han trobat clans.</p>
      </div>
    </template>

    <template v-else>
      <div v-if="paginatedPublicClans.length > 0">
        <div class="clan-divider">
          <span class="clan-divider__line"></span>
          <span class="clan-divider__text">clans públics</span>
          <span class="clan-divider__line"></span>
        </div>
        <div class="clan-catalog-list">
          <button
            v-for="clan in paginatedPublicClans"
            :key="'pub-' + clan.id"
            type="button"
            class="clan-catalog-item"
            @click="onClanClick(clan)"
          >
            <div class="clan-catalog-item__info">
              <span class="clan-catalog-item__name">{{ clan.nom }}</span>
              <span class="clan-catalog-item__meta">
                {{ clan.members_count || clan.membres_count || 0 }}/{{ clan.max_membres }} membres
                <span class="clan-catalog-badge clan-catalog-badge--public">Públic</span>
              </span>
            </div>
            <span class="clan-catalog-item__arrow" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 18L15 12L9 6" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </button>
        </div>
      </div>

      <div v-if="paginatedPrivateClans.length > 0">
        <div class="clan-divider">
          <span class="clan-divider__line"></span>
          <span class="clan-divider__text">clans privats</span>
          <span class="clan-divider__line"></span>
        </div>
        <div class="clan-catalog-list">
          <button
            v-for="clan in paginatedPrivateClans"
            :key="'priv-' + clan.id"
            type="button"
            class="clan-catalog-item"
            @click="onClanClick(clan)"
          >
            <div class="clan-catalog-item__info">
              <span class="clan-catalog-item__name">{{ clan.nom }}</span>
              <span class="clan-catalog-item__meta">
                {{ clan.members_count || clan.membres_count || 0 }}/{{ clan.max_membres }} membres
                <span class="clan-catalog-badge clan-catalog-badge--private">Privat</span>
              </span>
            </div>
            <span class="clan-catalog-item__arrow" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 18L15 12L9 6" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </button>
        </div>
      </div>
    </template>

    <div v-if="clansLastPage > 1" class="clan-catalog-pagination">
      <button
        @click="changePage(currentPage - 1)"
        :disabled="currentPage <= 1"
        class="clan-catalog-page-btn"
      >
        ←
      </button>
      <span class="clan-catalog-page-info">{{ currentPage }} / {{ clansLastPage }}</span>
      <button
        @click="changePage(currentPage + 1)"
        :disabled="currentPage >= clansLastPage"
        class="clan-catalog-page-btn"
      >
        →
      </button>
    </div>

    <!-- Panel info clan seleccionat -->
    <Teleport to="body">
      <div v-if="selectedClan" class="clan-info-overlay" @click.self="selectedClan = null">
        <div class="clan-info-sheet">
          <div class="clan-info-sheet__handle"><div class="clan-info-sheet__bar"></div></div>
          <div class="clan-info-sheet__body">
            <div class="clan-info-sheet__avatar">
              <span>{{ selectedClan.nom ? selectedClan.nom.charAt(0).toUpperCase() : 'C' }}</span>
            </div>
            <h2 class="clan-info-sheet__name">{{ selectedClan.nom }}</h2>
            <p class="clan-info-sheet__desc">{{ selectedClan.descripcio || 'Sense descripció' }}</p>
            <div class="clan-info-sheet__badges">
              <span v-if="selectedClan.es_public" class="clan-info-badge clan-info-badge--public">Públic</span>
              <span v-else class="clan-info-badge clan-info-badge--private">Privat</span>
              <span class="clan-info-badge clan-info-badge--count">{{ selectedClan.members_count || selectedClan.membres_count || 0 }} / {{ selectedClan.max_membres }} membres</span>
            </div>

            <div class="clan-info-members">
              <p v-if="loadingMembers" class="clan-info-members__loading">Carregant membres...</p>
              <div v-else-if="selectedClanMembers.length > 0" class="clan-info-members__list">
                <div
                  v-for="member in selectedClanMembers"
                  :key="member.usuari_id || member.id"
                  class="clan-info-member-card-wrap"
                >
                  <div
                    class="clan-info-member-card"
                    :class="member.rol === 'lider' ? 'clan-info-member-card--lider' : ''"
                    @click="toggleMemberExpand(member)"
                  >
                    <div class="clan-info-member-card__avatar" :style="getMemberAvatarBgStyle(member)" @click.stop="openMemberProfile(member)">
                      <img v-if="getMemberMonsterImg(member)" :src="getMemberMonsterImg(member)" class="clan-info-member-card__avatar-img" />
                    </div>
                    <div class="clan-info-member-card__info">
                      <span class="clan-info-member-card__name">{{ member.nom || 'Usuari' }}</span>
                      <span class="clan-info-member-card__level">Niv. {{ member.nivell || 1 }}</span>
                    </div>
                    <span v-if="member.rol === 'lider'" class="clan-info-member-card__badge">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="#FFD54F" stroke="#E6B800" stroke-width="1.5"><path d="M2 20h20l-2-8-4 4-4-8-4 8-4-4-2 8z"/><path d="M5 20v1h14v-1"/></svg>
                      Líder
                    </span>
                  </div>
                  <div v-if="expandedMemberId === (member.usuari_id || member.id)" class="clan-info-member-actions">
                    <button type="button" class="template-expand-btn w-full text-center" style="background:#5B9CE6; border: 2px solid #4A83C4; color: white;" @click="openMemberChat(member)">Missatge</button>
                    <button type="button" class="template-expand-btn w-full text-center" style="background:#FF8DA6; border: 2px solid #E6778F; color: white;" @click="openMemberProfile(member)">Veure perfil</button>
                  </div>
                </div>
              </div>
              <p v-else class="clan-info-members__empty">Cap membre trobat.</p>
            </div>

            <div class="clan-info-sheet__actions">
              <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel" @click="selectedClan = null">Enrere</button>
              <button type="button" class="confirm-modal__btn confirm-modal__btn--confirm" @click="onJoinSelectedClan">
                {{ selectedClan.es_public ? "Unir-se" : "Sol·licitar unir-se" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <ChatWindow
      v-if="chatMemberId"
      :friend-id="chatMemberId"
      :friend-name="chatMemberName"
      :friend-monstre-tipus="chatMemberMonstreTipus"
      :friend-nivell="chatMemberNivell"
      @close="closeMemberChat"
    />

  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/ModeFocus/Fons/Fons_Mode_Focus.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import ChatWindow from "~/components/user/social/ChatWindow.vue";
import SharedTemplatePublicSwitch from "~/components/shared/TemplatePublicSwitch.vue";
import HabitFormCategory from "~/components/user/habits/HabitFormCategory.vue";

export default {
  name: "ClanList",
  components: { ChatWindow, SharedTemplatePublicSwitch, HabitFormCategory },
  emits: ["open-create", "clan-created"],
  data: function() {
    return {
      searchQuery: "",
      searchVisible: false,
      loading: false,
      clans: [],
      filterType: "all",
      currentPage: 1,
      clansLastPage: 1,
      searchDebounce: null,
      sheetObert: false,
      clanFormLoading: false,
      clanFormError: null,
      clanForm: {
        nom: "",
        categoria_id: null,
        es_public: true,
        max_membres: 10,
        userCategoriaEtiqueta: null,
        userCategoriaId: null,
        categoriaIcona: ""
      },
      userCategories: [],
      selectedClan: null,
      selectedClanMembers: [],
      loadingMembers: false,
      expandedMemberId: null,
      chatMemberId: null,
      chatMemberName: "",
      chatMemberMonstreTipus: null,
      chatMemberNivell: 1,
      bosqueImg: bosqueImg,
      clanCategories: [
        { id: 1, key: "physical", icona: "🏃" },
        { id: 2, key: "food", icona: "🥗" },
        { id: 3, key: "study", icona: "📚" },
        { id: 4, key: "reading", icona: "📖" },
        { id: 5, key: "wellness", icona: "🧘" },
        { id: 6, key: "improvement", icona: "✨" },
        { id: 7, key: "home", icona: "🏠" },
        { id: 8, key: "hobby", icona: "🎨" }
      ]
    }
  },
  computed: {
    isSearching: function() {
      return this.searchQuery && this.searchQuery.trim().length > 0;
    },
    filteredClans: function() {
      var clans = this.clans;
      if (this.filterType === "public") {
        clans = clans.filter(function(c) { return c.es_public === true; });
      } else if (this.filterType === "private") {
        clans = clans.filter(function(c) { return c.es_public === false; });
      }
      if (this.searchQuery && this.searchQuery.trim()) {
        var query = this.searchQuery.toLowerCase();
        clans = clans.filter(function(c) {
          return c.nom && c.nom.toLowerCase().indexOf(query) !== -1;
        });
      }
      return clans;
    },
    sortedClans: function() {
      var publics = this.filteredClans.filter(function(c) { return c.es_public === true; });
      var privats = this.filteredClans.filter(function(c) { return c.es_public === false; });
      return publics.concat(privats);
    },
    paginatedClans: function() {
      var perPage = 10;
      var start = (this.currentPage - 1) * perPage;
      var end = start + perPage;
      return this.sortedClans.slice(start, end);
    },
    paginatedPublicClans: function() {
      return this.paginatedClans.filter(function(c) { return c.es_public === true; });
    },
    paginatedPrivateClans: function() {
      return this.paginatedClans.filter(function(c) { return c.es_public === false; });
    },
    clansPages: function() {
      var total = this.sortedClans.length;
      var perPage = 10;
      var lastPage = Math.max(1, Math.ceil(total / perPage));
      this.clansLastPage = lastPage;
      return lastPage;
    }
  },
  watch: {
    searchQuery: function() {
      var self = this;
      if (self.searchDebounce) clearTimeout(self.searchDebounce);
      self.searchDebounce = setTimeout(function() {
        self.currentPage = 1;
      }, 150);
    },
    filterType: function() {
      this.currentPage = 1;
    }
  },
  mounted: function() {
    var self = this;
    this.fetchAll();
    this.carregarCategoriesUsuari();
    var tryConnect = function() {
      var nuxtApp = useNuxtApp();
      if (nuxtApp.$socket && nuxtApp.$socket.connected) {
        nuxtApp.$socket.on("clan_created", function(data) {
          self.clans.unshift(data);
        });
        nuxtApp.$socket.on("clan_deleted", function(data) {
          self.clans = self.clans.filter(function(c) { return Number(c.id) !== Number(data.clan_id); });
        });
        nuxtApp.$socket.on("clan_member_joined", function(data) {
          var clanItem = self.clans.find(function(c) { return Number(c.id) === Number(data.clan_id); });
          if (clanItem) {
            clanItem.members_count = (clanItem.members_count || clanItem.membres_count || 0) + 1;
          }
        });
      } else {
        setTimeout(tryConnect, 1000);
      }
    };
    tryConnect();
  },
  methods: {
    toggleSearch: function() {
      var self = this;
      this.searchVisible = !this.searchVisible;
      if (this.searchVisible) {
        this.$nextTick(function() {
          if (self.$refs.searchInput) self.$refs.searchInput.focus();
        });
      } else {
        this.searchQuery = "";
      }
    },
    obrirSheet: function() {
      this.clanForm.nom = "";
      this.clanForm.categoria_id = null;
      this.clanForm.es_public = true;
      this.clanForm.max_membres = 10;
      this.clanForm.userCategoriaEtiqueta = null;
      this.clanForm.userCategoriaId = null;
      this.clanForm.categoriaIcona = "";
      this.clanFormError = null;
      this.sheetObert = true;
    },
    tancarSheet: function() {
      this.sheetObert = false;
      this.clanFormError = null;
    },
    submitClan: async function() {
      var self = this;
      self.clanFormLoading = true;
      self.clanFormError = null;
      var store = useClanStore();
      try {
        var result = await store.createClan(self.clanForm);
        if (result) {
          self.tancarSheet();
          self.$emit("clan-created", result);
        } else {
          self.clanFormError = store.error || "Error al crear el clan";
        }
      } catch (e) {
        self.clanFormError = e.message;
      } finally {
        self.clanFormLoading = false;
      }
    },
    fetchAll: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        await store.fetchClans("", 1);
        this.clans = store.clans;
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    changePage: function(page) {
      if (page < 1 || page > this.clansLastPage) return;
      this.currentPage = page;
    },
    onClanClick: async function(clan) {
      this.selectedClan = clan;
      this.selectedClanMembers = [];
      this.loadingMembers = true;
      try {
        var store = useClanStore();
        var members = await store.fetchMembers(clan.id);
        this.selectedClanMembers = (members || []).slice().sort(function(a, b) {
          return (b.nivell || 0) - (a.nivell || 0);
        });
      } catch(e) {
        this.selectedClanMembers = [];
      } finally {
        this.loadingMembers = false;
      }
    },
    getMemberMonsterImg: function(member) {
      if (!member.monstre_tipus) return null;
      var skinKey = member.skin_key || null;
      return getMonsterImageFromUser({ monstre_tipus: member.monstre_tipus, nivell: member.nivell }, skinKey);
    },
    getMemberAvatarBgStyle: function(member) {
      var fonsKey = member ? member.fons_key : null;
      var bg = bosqueImg;
      if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
      else if (fonsKey === "fons_casa") bg = fonsCasaImg;
      return { backgroundImage: "url(" + bg + ")" };
    },
    toggleMemberExpand: function(member) {
      var id = member.usuari_id || member.id;
      var authStore = useAuthStore();
      if (authStore.user && Number(id) === Number(authStore.user.id)) return;
      this.expandedMemberId = this.expandedMemberId === id ? null : id;
    },
    openMemberChat: function(member) {
      var id = member.usuari_id || member.id;
      this.chatMemberId = id;
      this.chatMemberName = member.nom || "Usuari";
      this.chatMemberMonstreTipus = member.monstre_tipus || null;
      this.chatMemberNivell = member.nivell || 1;
      this.expandedMemberId = null;
    },
    closeMemberChat: function() {
      this.chatMemberId = null;
      this.chatMemberName = "";
      this.chatMemberMonstreTipus = null;
      this.chatMemberNivell = 1;
    },
    openMemberProfile: function(member) {
      var id = member.usuari_id || member.id;
      this.$router.push("/user/" + id);
    },
    onClanCategorySelect: function(id) {
      this.clanForm.categoria_id = id;
      this.clanForm.userCategoriaEtiqueta = null;
      this.clanForm.userCategoriaId = null;
      var cat = this.clanCategories.find(function(c) { return Number(c.id) === Number(id); });
      if (cat) {
        this.clanForm.categoriaIcona = cat.icona;
      }
    },
    onClanCategorySelectUser: function(payload) {
      if (!payload || payload.baseCategoryId == null) return;
      this.clanForm.categoria_id = parseInt(String(payload.baseCategoryId), 10);
      this.clanForm.categoriaIcona = payload.icona || "📁";
      this.clanForm.userCategoriaEtiqueta = payload.nom;
      this.clanForm.userCategoriaId = payload.id;
    },
    onClanCategoryAdd: function(payload) {
      var nom = "";
      var icona = "✨";
      var baseId = 8;
      if (typeof payload === "string") {
        nom = String(payload || "").trim();
        baseId = (this.userCategories.length % 8) + 1;
      } else if (payload && typeof payload === "object") {
        nom = String(payload.nom || "").trim();
        icona = payload.icona && String(payload.icona).trim() ? String(payload.icona).trim() : "✨";
        if (payload.baseCategoryId != null) {
          var b = parseInt(String(payload.baseCategoryId), 10);
          if (b >= 1 && b <= 8) baseId = b;
        }
      }
      if (!nom) return;
      var maxId = this.userCategories.reduce(function(m, c) { return Math.max(m, Number(c.id) || 0); }, 9000);
      var nextId = maxId + 1;
      var entry = { id: nextId, nom: nom, icona: icona, baseCategoryId: baseId };
      this.userCategories = this.userCategories.concat([entry]);
      try { localStorage.setItem("loopy_user_habit_categories", JSON.stringify(this.userCategories)); } catch(e) {}
    },
    carregarCategoriesUsuari: function() {
      try {
        var raw = localStorage.getItem("loopy_user_habit_categories");
        if (!raw) { this.userCategories = []; return; }
        var parsed = JSON.parse(raw);
        this.userCategories = Array.isArray(parsed) ? parsed : [];
      } catch(e) { this.userCategories = []; }
    },
    onJoinSelectedClan: function() {
      if (!this.selectedClan) return;
      if (this.selectedClan.es_public) {
        this.joinClan(this.selectedClan.id);
      } else {
        this.requestJoinClan(this.selectedClan.id);
      }
      this.selectedClan = null;
    },
    joinClan: async function(id) {
      var self = this;
      var store = useClanStore();
      var authStore = useAuthStore();
      var result = await store.joinPublic(id);
      if (result) {
        await this.$loopyModal.success("Clan", "T'has unit al clan amb èxit!");
        var nuxtApp = useNuxtApp();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
          nuxtApp.$socket.emit("join_clan_room", { clan_id: id });
          nuxtApp.$socket.emit("clan_member_joined", {
            clan_id: id,
            user_id: authStore.user.id,
            user_nom: authStore.user.nom
          });
        }
        var clanItem = this.clans.find(function(c) { return Number(c.id) === Number(id); });
        if (clanItem) {
          clanItem.members_count = (clanItem.members_count || clanItem.membres_count || 0) + 1;
        }
        this.$router.push('/clans/' + id);
      } else {
        await this.$loopyModal.error("Error", store.error || "Error al unir-se al clan");
      }
    },
    requestJoinClan: async function(id) {
      var store = useClanStore();
      var authStore = useAuthStore();
      var clan = this.clans.find(function(c) { return c.id === id; });
      var result = await store.requestJoin(id);
      if (result) {
        await this.$loopyModal.success("Sol·licitud", "S'ha enviat la sol·licitud per unir-se al clan.");
        var nuxtApp = useNuxtApp();
        if (nuxtApp.$socket && nuxtApp.$socket.connected && clan) {
          nuxtApp.$socket.emit("clan_request_notify", {
            clan_id: id,
            clan_nom: clan.nom,
            leader_id: clan.lider_id,
            usuari_nom: authStore.user.nom
          });
        }
      } else {
        await this.$loopyModal.error("Error", store.error || "Error en enviar la sol·licitud");
      }
    }
  }
}
</script>

<style scoped>
.clan-catalog-panel {
  display: flex;
  flex-direction: column;
  gap: 4px;
  animation: clan-panel-in 0.22s ease-out;
}

@keyframes clan-panel-in {
  from { transform: translateY(12px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* --- Filter bar (estilo Plantilles) --- */
.clan-filter-wrap {
  width: 100%;
  margin-bottom: 8px;
}

.clan-filter-row {
  display: flex;
  align-items: stretch;
  gap: 10px;
  width: 100%;
  flex-wrap: wrap;
}

.clan-filter-search {
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

.clan-filter-search--active {
  width: 100%;
}

.clan-filter-decor {
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

.clan-filter-search-input {
  flex: 1;
  border: 0;
  background: transparent;
  padding: 0 20px 0 0;
  color: #5e5e5e;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 16px;
  outline: none;
}

.clan-filter-card {
  position: relative;
  flex: 1 1 0;
  min-width: 200px;
  height: 58px;
  border-radius: 10px;
  background: #faf9f9;
  overflow: hidden;
  transition: width 0.3s ease, flex 0.3s ease;
}

.clan-filter-wrap--searching .clan-filter-card {
  flex: 0 0 100%;
}

.clan-filter-select {
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
  cursor: pointer;
}

.clan-filter-chevron {
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

/* --- Separadores de sección --- */
.clan-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;
  margin: 14px 0 8px;
}

.clan-divider__text {
  flex-shrink: 0;
  color: #faf9f9;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 15px;
  line-height: 1.2;
  white-space: nowrap;
}

.clan-divider__line {
  flex: 1 1 0;
  min-width: 0;
  height: 3px;
  background: #faf9f9;
  border-radius: 999px;
}

/* --- Lista de clanes --- */
.clan-catalog-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.clan-catalog-loading {
  display: flex;
  justify-content: center;
  padding: 24px 0;
}

.clan-catalog-spinner {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 3px solid rgba(250, 249, 249, 0.3);
  border-top-color: #79D45D;
  animation: clan-spin 0.7s linear infinite;
}

@keyframes clan-spin {
  to { transform: rotate(360deg); }
}

.clan-catalog-empty {
  text-align: center;
  padding: 20px 0;
  color: #ffffff;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  opacity: 0.85;
}

.clan-catalog-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 12px 14px;
  border: 0;
  border-radius: 10px;
  background: rgba(250, 249, 249, 0.95);
  cursor: pointer;
  transition: transform 0.12s, box-shadow 0.12s;
  text-align: left;
}

.clan-catalog-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.clan-catalog-item:active {
  transform: scale(0.98);
}

.clan-catalog-item__info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.clan-catalog-item__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #2b2d42;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.clan-catalog-item__meta {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 8px;
}

.clan-catalog-badge {
  display: inline-block;
  padding: 1px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.clan-catalog-badge--public {
  background: #dcfce7;
  color: #16a34a;
}

.clan-catalog-badge--private {
  background: #f3e8ff;
  color: #7c3aed;
}

.clan-catalog-item__arrow {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

/* --- Paginación --- */
.clan-catalog-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-top: 8px;
}

.clan-catalog-page-btn {
  width: 32px;
  height: 32px;
  border: 0;
  border-radius: 8px;
  background: rgba(250, 249, 249, 0.2);
  color: #faf9f9;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.clan-catalog-page-btn:hover:not(:disabled) {
  background: rgba(250, 249, 249, 0.35);
}

.clan-catalog-page-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.clan-catalog-page-info {
  color: rgba(250, 249, 249, 0.85);
  font-size: 13px;
  font-weight: 600;
}

/* --- Botón crear (estilo habit +) --- */
.clan-create-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-height: 64px;
  margin: 0;
  padding: 0;
  border-radius: 10px;
  background: rgba(250, 249, 249, 0.5);
  border: 2px dashed #ffffff;
  box-shadow: none;
  cursor: pointer;
  transition: background 0.15s;
}

.clan-create-trigger:hover {
  background: rgba(250, 249, 249, 0.65);
}

.clan-create-trigger__icon {
  width: 33px;
  height: 33px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* --- Bottom sheet transitions --- */
.clan-sheet-backdrop-enter-active,
.clan-sheet-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.clan-sheet-backdrop-enter-from,
.clan-sheet-backdrop-leave-to {
  opacity: 0;
}

.clan-sheet-panel-enter-active,
.clan-sheet-panel-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.clan-sheet-panel-enter-from,
.clan-sheet-panel-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}

/* ===== CLAN INFO PANEL ===== */
.clan-info-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  background: rgba(0, 0, 0, 0.5);
}

.clan-info-sheet {
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
  border-radius: 24px 24px 0 0;
  background: #fff;
  box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
  animation: clan-info-slide-up 0.3s ease-out forwards;
}

.clan-info-sheet__handle {
  display: flex;
  justify-content: center;
  padding: 14px 0 8px;
}

.clan-info-sheet__bar {
  width: 48px;
  height: 5px;
  background: #e5e7eb;
  border-radius: 999px;
}

.clan-info-sheet__body {
  padding: 8px 24px 28px;
  text-align: center;
}

.clan-info-sheet__avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #79D45D, #4ea832);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 12px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 26px;
  font-weight: 800;
  color: #fff;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
}

.clan-info-sheet__name {
  margin: 0 0 6px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: #2b2d42;
}

.clan-info-sheet__desc {
  margin: 0 0 14px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.4;
}

.clan-info-sheet__badges {
  display: flex;
  gap: 8px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.clan-info-badge {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 8px;
}

.clan-info-badge--public {
  background: #dcfce7;
  color: #16a34a;
}

.clan-info-badge--private {
  background: #f3e8ff;
  color: #7c3aed;
}

.clan-info-badge--count {
  background: #f3f4f6;
  color: #6b7280;
}

.clan-info-sheet__actions {
  display: flex;
  gap: 10px;
  margin-top: 18px;
}

.clan-info-sheet__actions .confirm-modal__btn {
  flex: 1;
  border: 0;
  border-radius: 12px;
  padding: 13px 10px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.15s;
}

.clan-info-sheet__actions .confirm-modal__btn--confirm {
  background: #79D45D;
  color: #fff;
}

.clan-info-sheet__actions .confirm-modal__btn--confirm:hover {
  filter: brightness(0.93);
}

.clan-info-sheet__actions .confirm-modal__btn--cancel {
  background: #f3f4f6;
  color: #6b7280;
}

.clan-info-sheet__actions .confirm-modal__btn--cancel:hover {
  background: #e5e7eb;
}

.clan-info-members {
  margin-top: 16px;
  text-align: left;
}

.clan-info-members__loading,
.clan-info-members__empty {
  text-align: center;
  font-size: 13px;
  color: #9ca3af;
  padding: 12px 0;
}

.clan-info-members__list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 200px;
  overflow-y: auto;
}

.clan-info-member-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #f0f0f0;
}

.clan-info-member-card--lider {
  background: #FFF8E1;
  border: 2px solid #FFB300;
}

.clan-info-member-card__avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 2px solid #e0e0e0;
}

.clan-info-member-card--lider .clan-info-member-card__avatar {
  border-color: #FFB300;
}

.clan-info-member-card__avatar-img {
  width: 26px;
  height: 26px;
  object-fit: contain;
  filter: drop-shadow(0 1px 2px rgba(0,0,0,0.15));
}

.clan-info-member-card__info {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.clan-info-member-card__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #2b2d42;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.clan-info-member-card__level {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  color: #6b7280;
}

.clan-info-member-card__badge {
  display: flex;
  align-items: center;
  gap: 3px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 700;
  color: #E6B800;
}

.clan-info-member-card-wrap {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.clan-info-member-card {
  cursor: pointer;
}

.clan-info-member-actions {
  display: flex;
  gap: 6px;
  padding: 6px 12px 10px;
  background: #f9fafb;
  border-radius: 0 0 12px 12px;
  margin-top: -4px;
  border: 1px solid #f0f0f0;
  border-top: 0;
}

.clan-info-member-actions .template-expand-btn {
  flex: 1;
  padding: 8px 6px;
  border-radius: 8px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

@keyframes clan-info-slide-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

</style>
