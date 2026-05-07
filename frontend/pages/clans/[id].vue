<template>
  <div class="min-h-screen overflow-x-hidden pb-24 lg:pb-20">
    <div class="w-full max-w-6xl mx-auto min-w-0 box-border px-2 sm:px-4 md:px-6 pt-2 sm:pt-3">
      <div
        class="rounded-2xl sm:rounded-3xl overflow-hidden bg-white shadow-md border border-gray-100"
      >
        <HeaderSocial />
        <div class="px-3 sm:px-5 py-4 sm:py-8 relative">
          <div v-if="loading" class="text-center py-8">Carregant...</div>
          <div v-else-if="clan">
            <div class="mb-6">
              <h1 class="text-2xl font-bold text-gray-800">{{ clan.nom }}</h1>
              <p class="text-sm text-gray-500 mt-1">{{ clan.descripcio || 'Sense descripció' }}</p>
              <div class="flex gap-2 mt-2">
                <span v-if="clan.es_public" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Públic</span>
                <span v-else class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">Privat</span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ memberCount }} membres</span>
              </div>
            </div>

            <div v-if="isMember" class="flex gap-2 mb-4">
              <button @click="showInvite = true" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium">
                Convidar Amic
              </button>
              <button v-if="!isLeader" @click="leaveClan" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium">
                Sortir del Clan
              </button>
            </div>

            <MemberList :clan-id="clanId" :is-leader="isLeader" />
            
            <RequestManager v-if="isLeader" :clan-id="clanId" />

            <ClanChat v-if="isMember" :clan-id="clanId" />
          </div>
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
      alreadyExpelled: false
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
        alert("Has de ser nivell 5 o superior per accedir als clans.");
        self.$router.push("/social");
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
          nuxtApp.$socket.on("clan_member_joined", function(data) {
            console.log("Page received clan_member_joined", data);
            if (Number(data.clan_id) === Number(self.clanId)) {
              self.loadClan();
            }
          });
          nuxtApp.$socket.on("clan_request_accepted", function(data) {
            console.log("Page received clan_request_accepted", data);
            if (Number(data.clan_id) === Number(self.clanId)) {
              self.loadClan();
            }
          });
          nuxtApp.$socket.on("clan_member_left", function(data) {
            console.log("Page received clan_member_left", data);
            if (Number(data.clan_id) === Number(self.clanId)) {
              if (Number(data.user_id) === Number(authStore.user.id)) {
                if (!self.alreadyExpelled) {
                  self.alreadyExpelled = true;
                  alert("Has estat expulsat del clan.");
                  self.$router.push("/clans");
                }
              } else {
                self.loadClan();
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
        nuxtApp.$socket.off("clan_member_joined");
        nuxtApp.$socket.off("clan_request_accepted");
        nuxtApp.$socket.off("clan_member_left");
      }
    }
  }
}
</script>