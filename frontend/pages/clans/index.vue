<template>
  <div class="clans-page min-h-screen bg-transparent overflow-x-hidden pb-24 lg:pb-8">
    <div class="max-w-5xl mx-auto px-3 sm:px-6 pt-2 sm:pt-3">
      <HeaderSocial />

      <div class="clans-content-area mt-4">
        <div v-if="loading" class="text-center py-10">
          <div class="clans-spinner"></div>
          <p class="clans-loading-text">Carregant...</p>
        </div>
        <template v-else>
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <h1 class="clans-page-title">{{ $t('nav.clans') }}</h1>
            <button
              v-if="!userClanId"
              type="button"
              @click="showCreate = !showCreate"
              class="clans-btn clans-btn--primary"
            >
              {{ showCreate ? 'Tornar als Clans' : 'Crear Nou Clan' }}
            </button>
            <button
              v-else
              type="button"
              @click="leaveClan"
              class="clans-btn clans-btn--danger"
            >
              Abandonar Clan
            </button>
          </div>

          <transition name="fade" mode="out-in">
            <ClanSettings v-if="showCreate" @cancel="showCreate = false" @saved="onClanCreated" />
            <ClanList v-else-if="!userClanId" />
            <div v-else class="text-center py-12">
              <p class="clans-redirect-text">Ja estas en un clan. Redirigint...</p>
            </div>
          </transition>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderSocial from "~/components/HeaderSocial.vue";
import ClanList from "~/components/clans/ClanList.vue";
import ClanSettings from "~/components/clans/ClanSettings.vue";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useClanStore } from "~/stores/useClanStore.js";
import { useNuxtApp } from "#app";

export default {
  name: "ClansIndexPage",
  middleware: ["auth"],
  components: {
    HeaderSocial,
    ClanList,
    ClanSettings
  },
  data: function() {
     return {
        showCreate: false,
        userClanId: null,
        loading: true
     }
  },
async mounted() {
     var self = this;
     var authStore = useAuthStore();
     if (authStore.user && authStore.user.nivell < 5) {
        alert("Has de ser nivell 5 o superior per accedir als clans.");
        this.$router.push("/social");
        return;
     }
     var setupSocketListeners = function() {
        var nuxtApp = useNuxtApp();
        if (nuxtApp.$socket && nuxtApp.$socket.connected) {
           nuxtApp.$socket.on("clan_request_accepted", function(data) {
              if (Number(data.usuari_id) === Number(authStore.user.id)) {
                 alert("La teva sol·licitud d'unió al clan ha estat acceptada!");
                 var store = useClanStore();
                 store.getMyClan().then(function() {
                    if (store.currentClan && store.currentClan.id) {
                       self.userClanId = store.currentClan.id;
                       self.$router.push('/clans/' + store.currentClan.id);
                    } else {
                       self.$router.push('/clans/' + data.clan_id);
                    }
                 });
              }
           });
           nuxtApp.$socket.on("clan_request_rejected", function(data) {
              if (Number(data.usuari_id) === Number(authStore.user.id)) {
                 alert("La teva sol·licitud d'unió al clan ha estat rebutjada.");
              }
           });
        } else {
           setTimeout(setupSocketListeners, 1000);
        }
     };
     setupSocketListeners();
     var store = useClanStore();
     var myClan = await store.getMyClan();
     this.loading = false;
     if (myClan && myClan.id) {
        this.userClanId = myClan.id;
        this.$router.push('/clans/' + myClan.id);
     }
   },
  methods: {
     onClanCreated: function (payload) {
        var store = useClanStore();
        this.showCreate = false;
        var clan = payload && (payload.clan || payload.data || payload);
        var id = clan && clan.id;
        if (id) {
           store.currentClan = clan;
           this.userClanId = id;
           this.$router.push('/clans/' + id);
           return;
        }
        if (store.currentClan && store.currentClan.id) {
           this.$router.push('/clans/' + store.currentClan.id);
        }
     },
     leaveClan: async function() {
        if (!confirm("Vols abandonar el clan?")) return;
        var store = useClanStore();
        if (this.userClanId) {
           var result = await store.leaveClan(this.userClanId);
           if (result) {
              this.userClanId = null;
              window.location.reload();
           }
        }
     }
  }
}
</script>

<style scoped>
.clans-page {
  font-family: "Comfortaa", system-ui, sans-serif;
}

.clans-page-title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: #faf9f9;
}

.clans-spinner {
  display: inline-block;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 3px solid #e5e5e5;
  border-top-color: #79D45D;
  animation: clans-spin 0.7s linear infinite;
}

@keyframes clans-spin {
  to { transform: rotate(360deg); }
}

.clans-loading-text {
  margin: 8px 0 0;
  color: #b0b0b0;
  font-size: 13px;
}

.clans-redirect-text {
  color: #b0b0b0;
  font-size: 14px;
}

.clans-btn {
  padding: 8px 20px;
  border: 0;
  border-radius: 10px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: filter 0.15s;
}

.clans-btn:hover {
  filter: brightness(0.97);
}

.clans-btn--primary {
  border: 2px solid #6FBC58;
  background: #79D45D;
  color: #ffffff;
}

.clans-btn--danger {
  background: #ff6b8a;
  color: #ffffff;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
