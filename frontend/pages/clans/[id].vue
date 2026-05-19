<!--
  Component o pagina Nuxt: [id].
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="clan-detail-page min-h-screen bg-transparent overflow-x-hidden pb-24 lg:pb-20">
    <div class="max-w-5xl mx-auto px-3 sm:px-6 pt-2 sm:pt-3">
      <HeaderSocial />

      <div class="clan-detail-area">
        <div v-if="loading" class="text-center py-8 clan-loading-text">Carregant...</div>

        <div v-else-if="insufficientLevel" class="max-w-md mx-auto mt-10">
          <div class="text-center bg-white p-8 rounded-[32px] border-4 border-gray-100 shadow-xl">
            <img src="~/assets/img/Icones/Icona_Logo_Perfil.png" class="w-32 h-auto mx-auto mb-6 drop-shadow-md pixelated" alt="Loopy" />
            <h2 class="text-2xl font-black text-gray-800 mb-4 tracking-tight font-['Bricolage_Grotesque',sans-serif]">Falta nivell!</h2>
            <p class="text-gray-500 mb-2 font-semibold text-[15px] leading-snug font-['Comfortaa',sans-serif]">
              Has de ser <strong class="text-emerald-500 text-lg">Nivell 5</strong> o superior per poder accedir a l'apartat de Clans.
            </p>
          </div>
        </div>

        <div v-else-if="clan && isMember">
          <!-- VISTA PRINCIPAL: XAT (inline, deixa espai per HeaderSocial) -->
          <div v-if="!panelOpen">
            <ClanChat :clan-id="clanId" :is-leader="isLeader" @open-info="panelOpen = true" />
          </div>

          <!-- PANEL: info clan + membres (s'obre al fer click a la foto del clan) -->
          <div v-else class="clan-panel">
            <button type="button" class="clan-panel__close" @click="panelOpen = false">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="#2b2d42" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Tornar al Xat</span>
            </button>

            <!-- Descripció del clan -->
            <div class="clan-panel-desc">
              <div class="clan-panel-desc__avatar">
                <span>{{ clanInitial }}</span>
              </div>
              <h2 class="clan-panel-desc__name">{{ clan.nom }}</h2>
              <p class="clan-panel-desc__text">{{ clan.descripcio || 'Sense descripció' }}</p>
              <div class="clan-panel-desc__badges">
                <span v-if="clan.es_public" class="clan-panel-badge clan-panel-badge--public">Públic</span>
                <span v-else class="clan-panel-badge clan-panel-badge--private">Privat</span>
                <span class="clan-panel-badge clan-panel-badge--count">{{ memberCount }} / {{ clan.max_membres }}</span>
              </div>
            </div>

            <!-- Solicituds pendents (nomes lider) -->
            <RequestManager v-if="isLeader" :clan-id="clanId" />

            <!-- Accions -->
            <div class="clan-panel-actions">
              <button type="button" class="clan-panel-action clan-panel-action--invite" @click="showInvite = true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M16 21V19C16 16.79 14.21 15 12 15H5C2.79 15 1 16.79 1 19V21M20 8V14M23 11H17M12.5 7C12.5 9.21 10.71 11 8.5 11C6.29 11 4.5 9.21 4.5 7C4.5 4.79 6.29 3 8.5 3C10.71 3 12.5 4.79 12.5 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Convidar Amic</span>
              </button>
              <button type="button" class="clan-panel-action clan-panel-action--leave" @click="leaveClan">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H9M16 17L21 12M21 12L16 7M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Sortir del Clan</span>
              </button>
            </div>

            <!-- Membres -->
            <div class="clan-divider">
              <span class="clan-divider__line"></span>
              <span class="clan-divider__text">membres del clan</span>
              <span class="clan-divider__line"></span>
            </div>

            <div class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="member in members"
                :key="member.usuari_id"
                class="template-expandable"
                :class="isMemberExpandit(member.usuari_id) ? 'template-expandable--active' : ''"
              >
                <div class="template-card w-full text-left relative" :style="member.rol === 'lider' ? 'background-color: #FFF8E1; border: 2px solid #FFB300;' : ''">
                  <button type="button" class="absolute inset-0 w-full h-full" style="z-index: 1;" @click="toggleMemberExpandit(member.usuari_id)" aria-label="Desplegar"></button>
                  <div class="clan-member-avatar cursor-pointer" :class="member.rol === 'lider' ? 'clan-member-avatar--lider' : ''" style="z-index: 2;" :style="getAvatarBgStyle(member)" @click.stop="openProfile(member.usuari_id)">
                    <img v-if="getMonsterImage(member)" :src="getMonsterImage(member)" class="clan-member-avatar__img" />
                  </div>
                  <div class="template-card__content relative" style="z-index: 1; pointer-events: none;">
                    <p class="template-card__title inline-block">{{ member.nom || 'Usuari' }}</p>
                    <div class="template-card__meta">
                      <span class="template-card__meta-item">Nv. {{ member.nivell || 1 }}</span>
                      <span v-if="member.rol === 'lider'" class="template-card__meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#FFD54F" stroke="#E6B800" stroke-width="1.5"><path d="M2 20h20l-2-8-4 4-4-8-4 8-4-4-2 8z"/><path d="M5 20v1h14v-1"/></svg>
                        Líder
                      </span>
                      <span v-else class="template-card__meta-item">Membre</span>
                    </div>
                  </div>
                  <button
                    v-if="isLeader && member.rol !== 'lider' && Number(member.usuari_id) !== currentUserId"
                    type="button"
                    class="clan-member-kick-btn"
                    style="z-index: 3;"
                    @click.stop="removeMember(member.usuari_id)"
                  >Eliminar</button>
                </div>

                <div v-if="isMemberExpandit(member.usuari_id)" class="template-expand-inline">
                  <div class="template-expand-top">
                    <button class="template-expand-close" type="button" @click.stop="tancarMemberExpandit">
                      <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                  </div>
                  <div class="template-expand-panel">
                    <div class="template-spec-card">
                      <div class="grid grid-cols-3 gap-2 w-full">
                        <button v-if="Number(member.usuari_id) !== currentUserId" type="button" class="template-expand-btn w-full text-center px-1" style="background:#5B9CE6; border: 2px solid #4A83C4; color: white;" @click="openChat(member.usuari_id, member.nom)">Missatge</button>
                        <button v-if="Number(member.usuari_id) !== currentUserId" type="button" class="template-expand-btn w-full text-center px-1" style="background:#FF8DA6; border: 2px solid #E6778F; color: white;" @click="reportUser(member.usuari_id)">Reportar</button>
                        <button v-if="Number(member.usuari_id) !== currentUserId" type="button" class="template-expand-btn template-expand-btn--primary w-full text-center px-1" @click="sendFriendRequest(member.usuari_id)">Sol·licitud</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <InvitationModal :show="showInvite" :clan-id="clanId" :clan-name="clanName" @close="showInvite = false" />

    <ChatWindow
      v-if="showPrivateChat"
      :friend-id="privateChatFriendId"
      :friend-name="privateChatFriendName"
      :friend-monstre-tipus="privateChatMonstreTipus"
      :friend-nivell="privateChatNivell"
      @close="showPrivateChat = false"
    />

    <Teleport to="body">
      <ConfirmModal
        :show="showKickConfirm"
        title="Eliminar membre?"
        message="Estàs segur que vols eliminar aquest membre del clan? Aquesta acció no es pot desfer."
        confirm-text="Eliminar"
        @confirm="confirmKickMember"
        @cancel="showKickConfirm = false"
      />
      <ConfirmModal
        :show="showLeaveConfirm"
        title="Sortir del clan?"
        message="Estàs segur que vols sortir del clan? Hauràs de tornar a sol·licitar o unir-te si vols tornar."
        confirm-text="Sortir"
        @confirm="confirmLeaveClan"
        @cancel="showLeaveConfirm = false"
      />
    </Teleport>

    <ReportUserModal
      :show="showReportModal"
      :user-id="reportUserId"
      @close="showReportModal = false"
      @submit="handleReportSubmit"
    />
  </div>
</template>

<script>
import HeaderSocial from "~/components/HeaderSocial.vue";
import RequestManager from "~/components/clans/RequestManager.vue";
import ClanChat from "~/components/clans/ClanChat.vue";
import InvitationModal from "~/components/clans/InvitationModal.vue";
import ConfirmModal from "~/components/user/social/ConfirmModal.vue";
import ReportUserModal from "~/components/user/social/ReportUserModal.vue";
import ChatWindow from "~/components/user/social/ChatWindow.vue";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";
import { useClanStore } from "~/stores/useClanStore.js";
import { authFetch } from "~/composables/useApi.js";

export default {
  name: "ClanDetailPage",
  middleware: ["auth"],
  components: {
    HeaderSocial,
    RequestManager,
    ClanChat,
    InvitationModal,
    ConfirmModal,
    ReportUserModal,
    ChatWindow
  },
  data: function() {
    return {
      bosqueImg: bosqueImg,
      loading: true,
      showInvite: false,
      panelOpen: false,
      memberExpanditId: null,
      alreadyExpelled: false,
      showKickConfirm: false,
      kickMemberId: null,
      showReportModal: false,
      reportUserId: null,
      showLeaveConfirm: false,
      showPrivateChat: false,
      privateChatFriendId: null,
      privateChatFriendName: "",
      privateChatMonstreTipus: null,
      privateChatNivell: null,
      insufficientLevel: false,
      currentUserId: null
    }
  },
  computed: {
    clanId: function() {
      return this.$route.params.id;
    },
    clan: function() {
      return useClanStore().currentClan;
    },
    clanName: function() {
      return this.clan ? this.clan.nom : 'Clan';
    },
    clanInitial: function() {
      return this.clan && this.clan.nom ? this.clan.nom.charAt(0).toUpperCase() : 'C';
    },
    members: function() {
      var list = useClanStore().clanMembers || [];
      return list.slice().sort(function(a, b) {
        return (Number(b.nivell) || 1) - (Number(a.nivell) || 1);
      });
    },
    memberCount: function() {
      return this.members.length;
    },
    isLeader: function() {
      var authStore = useAuthStore();
      return this.clan && this.clan.lider_id == authStore.user.id;
    },
    isMember: function() {
      var authStore = useAuthStore();
      var store = useClanStore();
      return store.clanMembers.some(function(m) {
        return Number(m.usuari_id) === Number(authStore.user.id);
      }) || this.isLeader;
    }
  },
  mounted: function() {
    var authStore = useAuthStore();
    this.currentUserId = authStore.user ? Number(authStore.user.id) : null;
    if (authStore.user && authStore.user.nivell < 5) {
      this.insufficientLevel = true;
      this.loading = false;
      return;
    }
    this.setupSocketListener();
    this.loadClan();
  },
  watch: {
    $route: function() { this.loadClan(); }
  },
  beforeUnmount: function() {
    this.removeSocketListener();
  },
  methods: {
    isMemberExpandit: function(id) {
      return this.memberExpanditId === Number(id);
    },
    toggleMemberExpandit: function(id) {
      if (Number(id) === this.currentUserId) return;
      this.memberExpanditId = this.memberExpanditId === Number(id) ? null : Number(id);
    },
    tancarMemberExpandit: function() {
      this.memberExpanditId = null;
    },
    loadClan: function() {
      var self = this;
      this.loading = true;
      var tryLoad = function() {
        var authStore = useAuthStore();
        if (!authStore.isAuthenticated || !authStore.user) {
          setTimeout(tryLoad, 300);
          return;
        }
        self.doLoadClan();
      };
      tryLoad();
    },
    doLoadClan: function() {
      var self = this;
      this.loading = true;
      var store = useClanStore();
      var authStore = useAuthStore();
      var clanId = this.clanId;
      store.getClan(clanId).then(function() {
        return store.fetchMembers(clanId);
      }).then(async function() {
        var isMember = store.clanMembers.some(function(m) {
          return Number(m.usuari_id) === Number(authStore.user.id);
        });
        if (store.currentClan && Number(store.currentClan.lider_id) === Number(authStore.user.id)) {
          isMember = true;
        }
        if (!isMember && !self.alreadyExpelled) {
          self.alreadyExpelled = true;
          await self.$loopyModal.warning("Clan", "Has estat expulsat del clan.");
          self.$router.push("/clans");
          return;
        }
        var nuxtApp = useNuxtApp();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
          nuxtApp.$socket.emit("join_clan_room", { clan_id: clanId });
        }
        self.loading = false;
      }).catch(function(e) {
        console.error(e);
        self.loading = false;
      });
    },
    leaveClan: function() {
      this.showLeaveConfirm = true;
    },
    confirmLeaveClan: async function() {
      this.showLeaveConfirm = false;
      var store = useClanStore();
      var authStore = useAuthStore();
      var result = await store.leaveClan(this.clanId);
      if (result) {
        var nuxtApp = useNuxtApp();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
          nuxtApp.$socket.emit("clan_member_left", { clan_id: this.clanId, user_id: authStore.user.id, user_nom: authStore.user.nom });
        }
        this.$router.push("/clans");
      } else {
        await this.$loopyModal.error("Error", store.error || "Error al sortir del clan");
      }
    },
    removeMember: function(userId) {
      this.kickMemberId = userId;
      this.showKickConfirm = true;
    },
    confirmKickMember: async function() {
      this.showKickConfirm = false;
      var userId = this.kickMemberId;
      if (!userId) return;
      var store = useClanStore();
      var m = this.members.find(function(x) { return Number(x.usuari_id) === Number(userId); });
      var success = await store.removeMember(this.clanId, userId);
      if (success) {
        var nuxtApp = useNuxtApp();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
          nuxtApp.$socket.emit("clan_member_left", { clan_id: this.clanId, user_id: userId, user_nom: m ? m.nom : 'Usuari' });
        }
        this.memberExpanditId = null;
        await store.fetchMembers(this.clanId);
      } else {
        await this.$loopyModal.error("Error", store.error || "Error al expulsar membre");
      }
      this.kickMemberId = null;
    },
    openProfile: function(userId) {
      var authStore = useAuthStore();
      if (Number(userId) === Number(authStore.user?.id)) {
        this.$router.push('/perfil');
      } else {
        this.$router.push('/user/' + userId);
      }
    },
    openChat: function(userId, nom) {
      var member = this.members.find(function(m) { return Number(m.usuari_id) === Number(userId); });
      this.privateChatFriendId = Number(userId);
      this.privateChatFriendName = nom || (member ? member.nom : "Usuari");
      this.privateChatMonstreTipus = member ? member.monstre_tipus : null;
      this.privateChatNivell = member ? member.nivell : null;
      this.showPrivateChat = true;
    },
    reportUser: function(userId) {
      this.reportUserId = userId;
      this.showReportModal = true;
    },
    handleReportSubmit: async function() {
      this.showReportModal = false;
      await this.$loopyModal.success("Report", "Gràcies! L'usuari ha sigut reportat i ho revisarem.");
    },
    sendFriendRequest: async function(userId) {
      try {
        var resp = await authFetch("/api/friends/request", { method: "POST", body: JSON.stringify({ addressee_id: userId }) });
        if (resp.ok) {
          await this.$loopyModal.success("Amistat", "Sol·licitud d'amistat enviada!");
        } else {
          var data = await resp.json();
          await this.$loopyModal.error("Error", data.error || data.message || "Error enviant sol·licitud");
        }
      } catch(e) {
        await this.$loopyModal.error("Error", "Error de connexio");
      }
    },
    setupSocketListener: function() {
      var self = this;
      var tryConnect = function() {
        var nuxtApp = useNuxtApp();
        var authStore = useAuthStore();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
          nuxtApp.$socket.on("clan_member_left", async function(data) {
            if (Number(data.clan_id) === Number(self.clanId)) {
              if (Number(data.user_id) === Number(authStore.user.id) && !self.alreadyExpelled) {
                self.alreadyExpelled = true;
                await self.$loopyModal.warning("Clan", "Has estat expulsat del clan.");
                self.$router.push("/clans");
              } else {
                useClanStore().fetchMembers(self.clanId);
              }
            }
          });
          nuxtApp.$socket.on("clan_member_joined", function(data) {
            if (Number(data.clan_id) === Number(self.clanId)) {
              useClanStore().fetchMembers(self.clanId);
            }
          });
        } else {
          setTimeout(tryConnect, 1000);
        }
      };
      tryConnect();
    },
    removeSocketListener: function() {
      var nuxtApp = useNuxtApp();
      if (nuxtApp.$socket) {
        nuxtApp.$socket.off("clan_member_left");
        nuxtApp.$socket.off("clan_member_joined");
      }
    },
    getMonsterImage: function(member) {
      if (!member) return null;
      var skinKey = member.skin_key || null;
      return getMonsterImageFromUser({ monstre_tipus: member.monstre_tipus, nivell: member.nivell }, skinKey);
    },
    getAvatarBgStyle: function(member) {
      var fonsKey = member ? member.fons_key : null;
      var bg = bosqueImg;
      if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
      else if (fonsKey === "fons_casa") bg = fonsCasaImg;
      return { backgroundImage: "url(" + bg + ")" };
    }
  }
}
</script>

<style scoped>
.clan-detail-page {
  font-family: "Comfortaa", system-ui, sans-serif;
}

.clan-loading-text {
  color: #b0b0b0;
  font-size: 14px;
}


/* ===== PANEL (info + membres) ===== */
.clan-panel {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-bottom: 80px;
}

.clan-panel__close {
  display: flex;
  align-items: center;
  gap: 6px;
  border: 0;
  background: rgba(255, 255, 255, 0.9);
  color: #2b2d42;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  padding: 8px 14px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.clan-panel__close:hover {
  background: #ffffff;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

/* Descripció */
.clan-panel-desc {
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  border: 2px solid #f3f4f6;
}

.clan-panel-desc__avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #79D45D, #4ea832);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 10px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #fff;
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
}

.clan-panel-desc__name {
  margin: 0 0 4px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #2b2d42;
}

.clan-panel-desc__text {
  margin: 0 0 10px;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.4;
}

.clan-panel-desc__badges {
  display: flex;
  gap: 8px;
  justify-content: center;
  flex-wrap: wrap;
}

.clan-panel-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 8px;
}

.clan-panel-badge--public {
  background: #dcfce7;
  color: #16a34a;
}

.clan-panel-badge--private {
  background: #f3e8ff;
  color: #7c3aed;
}

.clan-panel-badge--count {
  background: #f3f4f6;
  color: #6b7280;
}

/* Accions */
.clan-panel-actions {
  display: flex;
  gap: 8px;
}

.clan-panel-action {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px 12px;
  border: 0;
  border-radius: 10px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.15s;
}

.clan-panel-action:hover {
  filter: brightness(0.95);
}

.clan-panel-action--invite {
  background: #79D45D;
  border: 2px solid #6FBC58;
  color: #ffffff;
}

.clan-panel-action--leave {
  background: #FF6B8A;
  border: 2px solid #D14D6B;
  color: #ffffff;
}

/* Separador */
.clan-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;
  margin: 4px 0;
}

.clan-divider__text {
  flex-shrink: 0;
  color: #FAF9F9;
  font-size: 15px;
  line-height: 1.2;
  white-space: nowrap;
}

.clan-divider__line {
  flex: 1 1 0;
  min-width: 0;
  height: 3px;
  background: #FAF9F9;
  border-radius: 999px;
}

/* Botó expulsar visible a la card */
.clan-member-kick-btn {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  border: 2px solid #D14D6B;
  border-radius: 8px;
  background: #D14D6B;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 700;
  transition: background 0.15s;
}

.clan-member-kick-btn:hover {
  background: #B03A55;
}

/* ===== TEMPLATE CARD (igual que amics) ===== */
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

.clan-member-avatar {
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

.clan-member-avatar--lider {
  border-color: #FFB300;
}

.clan-member-avatar__img {
  width: 36px;
  height: 36px;
  object-fit: contain;
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.15));
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

.template-expand-close {
  border: 0;
  background: transparent;
  color: #faf9f9;
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

.template-expand-btn {
  border: 0;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 700;
}

.template-expand-btn--primary {
  background: #79d45d;
  color: #ffffff;
}

@keyframes template-sheet-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
