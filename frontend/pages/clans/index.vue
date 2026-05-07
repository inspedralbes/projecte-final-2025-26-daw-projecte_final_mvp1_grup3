<template>
  <!-- Mateixa caixa blanca arrodonida que /social i /friends (HeaderSocial dins la card) -->
  <div class="min-h-screen overflow-x-hidden pb-24 lg:pb-8">
    <div class="w-full max-w-5xl mx-auto min-w-0 box-border px-2 sm:px-4 md:px-6 pt-2 sm:pt-3">
      <div
        class="rounded-2xl sm:rounded-3xl overflow-hidden bg-white shadow-md border border-gray-100"
      >
        <HeaderSocial />
        <div class="px-3 sm:px-5 py-4 sm:py-6">
          <div v-if="loading" class="text-center py-10">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="text-gray-500 mt-2">Carregant...</p>
          </div>
          <template v-else>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ $t('nav.clans') }}</h1>
              <button
                v-if="!userClanId"
                type="button"
                @click="showCreate = !showCreate"
                class="px-5 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 shadow transition-colors font-medium shrink-0"
              >
                {{ showCreate ? 'Tornar als Clans' : 'Crear Nou Clan' }}
              </button>
              <button
                v-else
                type="button"
                @click="leaveClan"
                class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 shadow transition-colors font-medium shrink-0"
              >
                Abandonar Clan
              </button>
            </div>

            <transition name="fade" mode="out-in">
              <ClanSettings v-if="showCreate" @cancel="showCreate = false" @saved="onClanCreated" />
              <ClanList v-else-if="!userClanId" />
              <div v-else class="text-center py-12">
                <p class="text-gray-600 mb-4">Ja estas en un clan. Redirigint...</p>
              </div>
            </transition>
          </template>
        </div>
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
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
