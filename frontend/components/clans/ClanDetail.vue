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
             Líder: {{ getLeaderName() }} | Membres: {{ clan.membres_count || 0 }} / {{ clan.max_membres }}
           </p>
         </div>
         <div class="flex gap-2">
            <button v-if="isMember && !isLeader" @click="leave" class="px-4 py-2 border border-red-500 text-red-500 font-medium rounded-lg hover:bg-red-50 transition-colors">Abandonar</button>
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
      clan: null
    }
  },
  computed: {
    isLeader: function() {
      var authStore = useAuthStore();
      return this.clan && this.clan.lider_id == authStore.user.id;
    },
    isMember: function() {
      var authStore = useAuthStore();
      if (!this.clan) return false;
      // We check if current user is in `clanMembers` or has `clan_id`.
      // The fastest way if `members` is not returned with clan is checking store.clanMembers
      var store = useClanStore();
      var members = store.clanMembers;
      if (this.isLeader) return true;
      var i;
      for (i = 0; i < members.length; i++) {
         if (members[i].usuari_id == authStore.user.id) return true;
      }
      return false;
    }
  },
  mounted: function() {
    this.loadClan();
  },
  methods: {
    loadClan: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        // Load members first so we know if they are a member
        await store.fetchMembers(this.clanId);
        await store.getClan(this.clanId);
        this.clan = store.currentClan;
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    reload: function() {
       this.loadClan();
    },
    getLeaderName: function() {
       if (!this.clan || !this.clan.lider) return "Líder desconegut";
       return this.clan.lider.nom;
    },
    joinPublic: async function() {
       var store = useClanStore();
       var result = await store.joinPublic(this.clan.id);
       if (result) {
          alert("T'has unit al clan amb èxit!");
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
          this.$router.push('/clans');
       } else {
          alert(store.error || "Error al abandonar el clan");
       }
    }
  }
}
</script>
