<template>
  <div class="clan-detail-page min-h-screen bg-transparent overflow-x-hidden pb-24 lg:pb-20">
    <div class="max-w-6xl mx-auto px-3 sm:px-6 pt-2 sm:pt-3">
      <HeaderSocial />

      <div class="clan-detail-area mt-4">
        <div v-if="loading" class="text-center py-8 clan-loading-text">Carregant...</div>
        
        <div v-else-if="insufficientLevel" class="max-w-md mx-auto mt-10">
          <div class="text-center bg-white p-8 rounded-[32px] border-4 border-gray-100 shadow-xl font-['Outfit',sans-serif]">
            <img src="~/assets/img/Icones/Icona_Logo_Perfil.png" class="w-32 h-auto mx-auto mb-6 drop-shadow-md pixelated" alt="Loopy" />
            <h2 class="text-2xl font-black text-gray-800 mb-4 tracking-tight">Falta nivell!</h2>
            <p class="text-gray-500 mb-8 font-semibold text-[15px] leading-snug">
              Has de ser <strong class="text-emerald-500 text-lg">Nivell 5</strong> o superior per poder accedir a l'apartat de Clans. Segueix completant hàbits per pujar de nivell!
            </p>
            <NuxtLink to="/home" class="inline-block px-8 py-3 bg-[#FF6B8A] text-white rounded-[16px] font-extrabold text-base transition-transform active:translate-y-[2px] shadow-[0_4px_0_#D14D6B] border-none cursor-pointer">
              Seguir jugant
            </NuxtLink>
          </div>
        </div>

        <div v-else-if="clan">
          <div class="clan-detail-header">
            <h1 class="clan-detail-title">{{ clan.nom }}</h1>
            <p class="clan-detail-desc">{{ clan.descripcio || 'Sense descripció' }}</p>
            <div class="flex gap-2 mt-2">
              <span v-if="clan.es_public" class="clan-badge clan-badge--public">Públic</span>
              <span v-else class="clan-badge clan-badge--private">Privat</span>
              <span class="clan-badge clan-badge--members">{{ memberCount }} membres</span>
            </div>
          </div>

          <div v-if="isMember" class="flex gap-2 mb-4">
            <button @click="showInvite = true" class="clan-btn clan-btn--primary">
              Convidar Amic
            </button>
            <button @click="leaveClan" class="clan-btn clan-btn--danger">
              Sortir del Clan
            </button>
          </div>

          <MemberList :clan-id="clanId" :is-leader="isLeader" @view-profile="openProfile" />
          
          <RequestManager v-if="isLeader" :clan-id="clanId" />

          <ClanChat v-if="isMember" :clan-id="clanId" />
        </div>
      </div>
    </div>

    <InvitationModal :show="showInvite" :clan-id="clanId" :clan-name="clanName" @close="showInvite = false" />
  </div>
</template>

<script>
import HeaderSocial from "~/components/HeaderSocial.vue";
import MemberList from "~/components/clans/MemberList.vue";
import RequestManager from "~/components/clans/RequestManager.vue";
import ClanChat from "~/components/clans/ClanChat.vue";
import InvitationModal from "~/components/clans/InvitationModal.vue";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";
import { useClanStore } from "~/stores/useClanStore.js";

export default {
  name: "ClanDetailPage",
  middleware: ["auth"],
  components: {
    HeaderSocial,
    MemberList,
    RequestManager,
    ClanChat,
    InvitationModal
  },
  data: function() {
    return {
      loading: true,
      showInvite: false,
      alreadyExpelled: false,
      insufficientLevel: false
    }
  },
  computed: {
    clanId: function() {
      return this.$route.params.id;
    },
    clan: function() {
      var store = useClanStore();
      return store.currentClan;
    },
    clanName: function() {
      return this.clan ? this.clan.nom : 'Clan';
    },
    memberCount: function() {
      var store = useClanStore();
      return store.clanMembers.length;
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
    var self = this;
    var authCheck = function() {
      var authStore = useAuthStore();
      if (authStore.user && authStore.user.nivell < 5) {
        self.insufficientLevel = true;
        self.loading = false;
        return;
      }
      self.setupSocketListener();
      self.loadClan();
    };
    if (typeof window !== "undefined") {
      authCheck();
    }
  },
  watch: {
    $route: function() {
      this.loadClan();
    }
  },
  beforeUnmount: function() {
    this.removeSocketListener();
  },
  methods: {
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
      try {
        var store = useClanStore();
        var authStore = useAuthStore();
        var clanId = this.clanId;
        store.getClan(clanId).then(function() {
          return store.fetchMembers(clanId);
        }).then(function() {
          var isMember = store.clanMembers.some(function(m) {
            return Number(m.usuari_id) === Number(authStore.user.id);
          });
          var clan = store.currentClan;
          if (clan && Number(clan.lider_id) === Number(authStore.user.id)) {
            isMember = true;
          }
          if (!isMember && !self.alreadyExpelled) {
            self.alreadyExpelled = true;
            alert("Has estat expulsat del clan.");
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
      } catch(e) {
        console.error(e);
        this.loading = false;
      }
    },
    leaveClan: async function() {
      if (!confirm("Vols sortir del clan?")) return;
      try {
        var store = useClanStore();
        var authStore = useAuthStore();
        console.log(">>> leaveClan: emitting clan_member_left");
        var result = await store.leaveClan(this.clanId);
        if (result) {
          alert("Has sortit del clan.");
          var nuxtApp = useNuxtApp();
          if (nuxtApp.$socket && nuxtApp.$socket.connected) {
            nuxtApp.$socket.emit("clan_member_left", {
              clan_id: this.clanId,
              user_id: authStore.user.id,
              user_nom: authStore.user.nom
            });
          }
          this.$router.push("/clans");
        } else {
          alert(store.error || "Error al sortir del clan");
        }
      } catch(e) {
        console.error(e);
      }
    },
    setupSocketListener: function() {
      var self = this;
      var tryConnect = function() {
        var nuxtApp = useNuxtApp();
        var authStore = useAuthStore();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
          nuxtApp.$socket.on("clan_member_left", function(data) {
            if (Number(data.clan_id) === Number(self.clanId)) {
              if (Number(data.user_id) === Number(authStore.user.id)) {
                if (!self.alreadyExpelled) {
                  self.alreadyExpelled = true;
                  alert("Has estat expulsat del clan.");
                  self.$router.push("/clans");
                }
              }
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
      }
    },
    openProfile: function(userId) {
      if (userId) {
        var authStore = useAuthStore();
        if (Number(userId) === Number(authStore.user?.id)) {
          this.$router.push('/perfil');
        } else {
          this.$router.push('/user/' + userId);
        }
      }
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

.clan-detail-header {
  margin-bottom: 16px;
}

.clan-detail-title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: #faf9f9;
}

.clan-detail-desc {
  margin: 4px 0 0;
  font-size: 13px;
  color: rgba(250, 249, 249, 0.7);
}

.clan-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 8px;
}

.clan-badge--public {
  background: rgba(121, 212, 93, 0.2);
  color: #79D45D;
}

.clan-badge--private {
  background: rgba(148, 190, 240, 0.2);
  color: #94bef0;
}

.clan-badge--members {
  background: rgba(250, 249, 249, 0.1);
  color: rgba(250, 249, 249, 0.7);
}

.clan-btn {
  padding: 8px 16px;
  border: 0;
  border-radius: 10px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s;
}

.clan-btn:hover {
  filter: brightness(0.97);
}

.clan-btn--primary {
  border: 2px solid #6FBC58;
  background: #79D45D;
  color: #ffffff;
}

.clan-btn--danger {
  background: #ff6b8a;
  color: #ffffff;
}
</style>