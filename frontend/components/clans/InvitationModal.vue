<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
       <button @click="$emit('close')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
       </button>
       
       <h3 class="text-xl font-bold mb-4 text-gray-800">Convidar a {{ clanName }}</h3>
       
       <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Cercar Usuari</label>
          <div class="flex gap-2">
            <input 
              v-model="searchQuery" 
              @keyup.enter="search"
              type="text" 
              placeholder="Nom o formatge..." 
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500" 
            />
            <button @click="search" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Cercar</button>
          </div>
       </div>

       <div v-if="loading" class="text-center py-4 text-sm text-gray-500">Cercant...</div>
       <div v-else-if="searched && results.length === 0" class="text-center py-4 text-sm text-gray-500">Cap usuari trobat.</div>
       <ul class="space-y-2 max-h-48 overflow-y-auto mt-4" v-else>
          <li v-for="user in results" :key="user.id" class="flex justify-between items-center p-2 hover:bg-gray-50 border rounded-lg">
             <span class="font-medium text-sm">{{ user.nom }}</span>
             <button @click="invite(user.id)" class="px-3 py-1 bg-green-500 text-white text-xs font-medium rounded hover:bg-green-600">Convidar</button>
          </li>
       </ul>
    </div>
  </div>
</template>

<script>
import { authFetch } from "~/utils/authFetch.js";
import { useClanStore } from "~/stores/useClanStore.js";

export default {
  name: "InvitationModal",
  props: {
    show: {
      type: Boolean,
      default: false
    },
    clanId: {
      type: [Number, String],
      required: true
    },
    clanName: {
      type: String,
      default: "Clan"
    }
  },
  data: function() {
    return {
       searchQuery: "",
       loading: false,
       searched: false,
       results: []
    }
  },
  methods: {
     search: async function() {
        if (!this.searchQuery.trim()) return;
        this.loading = true;
        this.searched = true;
        try {
           var res = await authFetch("/api/users/search?q=" + encodeURIComponent(this.searchQuery));
           if (res.ok) {
              var data = await res.json();
              this.results = data.data || data;
           }
        } catch (e) {
           console.error(e);
        } finally {
           this.loading = false;
        }
     },
     invite: async function(userId) {
        var store = useClanStore();
        var result = await store.inviteUser(this.clanId, userId);
        if (result) {
           alert("Invitació enviada amb èxit!");
        } else {
           alert(store.error || "Error en enviar la invitació o l'usuari ja és al clan.");
        }
     }
  }
}
</script>
