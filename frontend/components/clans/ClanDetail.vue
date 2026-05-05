<template>
  <div class="space-y-6">
    <div v-if="loading" class="text-center py-12">
       <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
       <p class="text-gray-500 mt-2">Carregant clan...</p>
    </div>
    <div v-else-if="clan">
       <div class="bg-white rounded-xl shadow p-6 border flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
         <div>
           <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
             {{ clan.nom }}
             <span v-if="clan.es_public" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Públic</span>
             <span v-else class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full font-medium">Privat</span>
           </h2>
           <p class="text-sm text-gray-500 mt-2">
             Líder: {{ getLeaderName() }} | Membres: {{ clan.members_count || clan.membres_count || 0 }} / {{ clan.max_membres }}
           </p>
         </div>
         <div class="flex gap-2">
            <button v-if="isMember" @click="leave" class="px-4 py-2 border border-red-500 text-red-500 font-medium rounded-lg hover:bg-red-50 transition-colors">Abandonar Clan</button>
            <button v-if="!isMember && clan.es_public" @click="joinPublic" class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition-colors shadow-sm">Unir-se al Clan</button>
            <button v-if="!isMember && !clan.es_public" @click="requestJoin" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 transition-colors shadow-sm">Demanar Accés</button>
            <button v-if="isLeader" @click="$emit('edit')" class="px-4 py-2 border border-blue-500 text-blue-500 font-medium rounded-lg hover:bg-blue-50 transition-colors">Editar Settings</button>
         </div>
       </div>

       <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 flex flex-col min-h-[500px]">
             <ClanChat v-if="isMember" :clan-id="clan.id" />
             <div v-else class="bg-gray-50 rounded-xl p-12 border text-center flex-1 flex flex-col items-center justify-center">
               <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
               <h3 class="text-xl font-medium text-gray-700">Contingut Privat</h3>
               <p class="text-gray-500 mt-2">Has de ser membre per veure el xat i compartir hàbits.</p>
             </div>
          </div>
          <div class="space-y-6">
             <MemberList :clan-id="clan.id" :is-leader="isLeader" @member-removed="reload" />
             <RequestManager v-if="isLeader" :clan-id="clan.id" @request-handled="reload" />
          </div>
       </div>
    </div>
    <div v-else class="text-center py-12 text-red-500">
       No s'ha pogut carregar el clan o no existeix.
    </div>
  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";
import MemberList from "~/components/clans/MemberList.vue";
import RequestManager from "~/components/clans/RequestManager.vue";
import ClanChat from "~/components/clans/ClanChat.vue";

export default {
  name: "ClanDetail",
  components: {
    MemberList,
    RequestManager,
    ClanChat
  },
  props: {
    clanId: {
      type: [Number, String],
      required: true
    }
  },
  data: function() {
    return {
      loading: true,
      clan: null,
      memberCheckInterval: null,
      previousMemberCount: 0,
      showShareModal: false,
      shareType: 'habit',
      habits: [],
      plantillas: [],
      loadingHabits: false,
      loadingPlantillas: false,
      modalLoaded: false
    }
  },
  watch: {
    showShareModal: function(newVal) {
       if (newVal && !this.modalLoaded) {
          this.loadHabits();
          this.loadPlantillas();
          this.modalLoaded = true;
       }
    }
  },
  computed: {
    isLeader: function() {
      var authStore = useAuthStore();
      if (!this.clan || !authStore.user) return false;
      return Number(this.clan.lider_id) === Number(authStore.user.id);
    },
    isMember: function() {
      var authStore = useAuthStore();
      if (!this.clan || !authStore.user) return false;
      if (Number(this.clan.lider_id) === Number(authStore.user.id)) return true;
      var store = useClanStore();
      var members = store.clanMembers;
      for (var i = 0; i < members.length; i++) {
         if (Number(members[i].usuari_id) === Number(authStore.user.id)) return true;
      }
      return false;
    }
  },
mounted: function() {
     this.loadClan();
     this.setupSocketListeners();
     var self = this;
     this.memberCheckInterval = setInterval(function() {
        var store = useClanStore();
        store.fetchMembers(self.clanId);
     }, 3000);
   },
   beforeDestroy: function() {
     this.removeSocketListeners();
     if (this.memberCheckInterval) clearInterval(this.memberCheckInterval);
   },
  methods: {
    setupSocketListeners: function() {
       var self = this;
       var tryConnect = function() {
          var nuxtApp = useNuxtApp();
          if (nuxtApp.$socket && nuxtApp.$socket.connected) {
             nuxtApp.$socket.on("clan_member_joined", function(data) {
                console.log("Received clan_member_joined", data);
                if (Number(data.clan_id) === Number(self.clanId)) {
                   self.reload();
                }
             });
             nuxtApp.$socket.on("clan_member_left", function(data) {
                console.log("Received clan_member_left", data);
                if (Number(data.clan_id) === Number(self.clanId)) {
                   self.reload();
                }
             });
          } else {
             setTimeout(tryConnect, 1000);
          }
       };
       tryConnect();
    },
    removeSocketListeners: function() {
       var nuxtApp = useNuxtApp();
       if (nuxtApp.$socket) {
          nuxtApp.$socket.off("clan_member_joined");
          nuxtApp.$socket.off("clan_member_left");
       }
    },
    loadClan: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        await store.getClan(this.clanId);
        this.clan = store.currentClan;
        var previousCount = this.previousMemberCount;
        await store.fetchMembers(this.clanId);
        this.previousMemberCount = store.clanMembers.length;
        if (previousCount > 0 && store.clanMembers.length > previousCount) {
           var nuxtApp = useNuxtApp();
           if (nuxtApp.$socket && nuxtApp.$socket.connected) {
              nuxtApp.$socket.emit("clan_member_joined", {
                 clan_id: this.clanId,
                 user_id: 0,
                 user_nom: "Nou membre"
              });
           }
        }
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    reload: function() {
       this.loadClan();
    },
    shareHabit: async function(habitId) {
       try {
          var store = useClanStore();
          var authStore = useAuthStore();
          var result = await store.shareHabit(this.clan.id, habitId);
          if (result) {
             var nuxtApp = useNuxtApp();
             if (nuxtApp.$socket && nuxtApp.$socket.connected) {
                nuxtApp.$socket.emit("clan_member_joined", {
                   clan_id: this.clan.id,
                   user_id: authStore.user.id,
                   user_nom: authStore.user.nom + " (hàbit compartit)"
                });
             }
             alert("Hàbit compartit amb èxit!");
             this.showShareModal = false;
          } else {
             alert(store.error || "Error al compartir hàbit");
          }
       } catch(e) {
          console.error(e);
          alert("Error al compartir hàbit");
       }
    },
    sharePlantilla: async function(plantillaId) {
       try {
          var store = useClanStore();
          var authStore = useAuthStore();
          var result = await store.sharePlantilla(this.clan.id, plantillaId);
          if (result) {
             var nuxtApp = useNuxtApp();
             if (nuxtApp.$socket && nuxtApp.$socket.connected) {
                nuxtApp.$socket.emit("clan_member_joined", {
                   clan_id: this.clan.id,
                   user_id: authStore.user.id,
                   user_nom: authStore.user.nom + " (plantilla compartida)"
                });
             }
             alert("Plantilla compartida amb èxit!");
             this.showShareModal = false;
          } else {
             alert(store.error || "Error al compartir plantilla");
          }
       } catch(e) {
          console.error(e);
          alert("Error al compartir plantilla");
       }
    },
    loadHabits: function() {
       var self = this;
       this.loadingHabits = true;
       var authStore = useAuthStore();
       fetch('http://localhost:8000/api/habits', {
          headers: {
             'Authorization': 'Bearer ' + authStore.token,
             'Content-Type': 'application/json'
          }
       }).then(function(res) { return res.json(); })
       .then(function(data) {
          self.habits = data.data || data;
       })
       .catch(function(e) { console.error(e); })
       .finally(function() { self.loadingHabits = false; });
    },
    loadPlantillas: function() {
       var self = this;
       this.loadingPlantillas = true;
       var authStore = useAuthStore();
       fetch('http://localhost:8000/api/plantillas', {
          headers: {
             'Authorization': 'Bearer ' + authStore.token,
             'Content-Type': 'application/json'
          }
       }).then(function(res) { return res.json(); })
       .then(function(data) {
          self.plantillas = data.data || data;
       })
       .catch(function(e) { console.error(e); })
       .finally(function() { self.loadingPlantillas = false; });
    },
    getLeaderName: function() {
       if (!this.clan || !this.clan.lider) return "Líder desconegut";
       return this.clan.lider.nom;
    },
joinPublic: async function() {
        var store = useClanStore();
        var authStore = useAuthStore();
        var self = this;
        var result = await store.joinPublic(this.clan.id);
        if (result) {
           alert("T'has unit al clan amb èxit!");
           var nuxtApp = useNuxtApp();
           if (nuxtApp.$socket && nuxtApp.$socket.connected) {
              nuxtApp.$socket.emit("join_clan_room", { clan_id: this.clan.id });
              setTimeout(function() {
                 nuxtApp.$socket.emit("clan_member_joined", {
                    clan_id: self.clan.id,
                    user_id: authStore.user.id,
                    user_nom: authStore.user.nom
                 });
              }, 500);
           }
           await store.fetchMembers(this.clan.id);
           this.reload();
        } else {
           alert(store.error || "Error al unir-se al clan");
        }
     },
     requestJoin: async function() {
        var store = useClanStore();
        var result = await store.requestJoin(this.clan.id);
        if (result) {
           alert("S'ha enviat la sol·licitud per unir-se al clan.");
        } else {
           alert(store.error || "Error en enviar la sol·licitud");
        }
     },
    leave: async function() {
       if (!confirm("Vols abandonar el clan?")) return;
       var store = useClanStore();
       var result = await store.leaveClan(this.clan.id);
       if (result) {
          var authStore = useAuthStore();
          var nuxtApp = useNuxtApp();
          if (nuxtApp.$socket && nuxtApp.$socket.connected) {
             nuxtApp.$socket.emit("clan_member_left", {
                clan_id: this.clan.id,
                user_id: authStore.user.id,
                user_nom: authStore.user.nom
             });
          }
          this.$router.push('/clans');
       } else {
          alert(store.error || "Error al abandonar el clan");
       }
    }
  }
}
</script>
