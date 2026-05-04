<template>
  <div class="min-h-screen bg-gray-50">
    <HeaderSocial />
    <div v-if="loading" class="max-w-5xl mx-auto px-4 py-8 text-center">
       <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
       <p class="text-gray-500 mt-2">Carregant...</p>
    </div>
    <div v-else class="max-w-5xl mx-auto px-4 py-8">
       <div class="flex justify-between items-center mb-6">
         <h1 class="text-3xl font-bold text-gray-800">Clans</h1>
         <button v-if="!userClanId" @click="showCreate = !showCreate" class="px-5 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 shadow transition-colors font-medium">
            {{ showCreate ? 'Tornar als Clans' : 'Crear Nou Clan' }}
         </button>
         <button v-else @click="leaveClan" class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 shadow transition-colors font-medium">
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
     onClanCreated: function() {
        var store = useClanStore();
        this.showCreate = false;
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
