<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Clans Públics</h2>
      <div class="flex gap-2">
        <input 
          v-model="searchQuery" 
          @keyup.enter="search"
          type="text" 
          placeholder="Cercar clans..." 
          class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button @click="search" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
          Cercar
        </button>
      </div>
    </div>
    
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
    </div>
    <div v-else-if="clans.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat clans.
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="clan in clans" :key="clan.id" class="bg-white rounded-xl shadow p-6 border">
        <h3 class="text-lg font-bold text-gray-800">{{ clan.nom }}</h3>
        <p class="text-sm text-gray-500 mt-1">Membres: {{ clan.membres_count || 0 }} / {{ clan.max_membres }}</p>
        <p v-if="!clan.es_public" class="text-xs text-purple-600 font-medium mt-1">Privat</p>
        <div class="mt-4 flex justify-between items-center">
          <NuxtLink :to="'/clans/' + clan.id" class="text-blue-500 hover:underline text-sm font-medium">Veure Detalls</NuxtLink>
          <button v-if="clan.es_public" @click="joinClan(clan.id)" class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600">
            Unir-se
          </button>
          <button v-else @click="requestJoinClan(clan.id)" class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600">
            Demanar Accés
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";

export default {
  name: "ClanList",
  data: function() {
    return {
      searchQuery: "",
      loading: false,
      clans: []
    }
  },
  mounted: function() {
    this.search();
  },
  methods: {
    search: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        await store.fetchClans(this.searchQuery);
        this.clans = store.clans;
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
joinClan: async function(id) {
       var store = useClanStore();
       var authStore = useAuthStore();
       var result = await store.joinPublic(id);
       if (result) {
          alert("T'has unit al clan amb èxit!");
          var nuxtApp = useNuxtApp();
          if (nuxtApp.$socket && nuxtApp.$socket.connected) {
             nuxtApp.$socket.emit("join_clan_room", { clan_id: id });
          }
           this.$router.push('/clans/' + id);
        } else {
           alert(store.error || "Error al unir-se al clan");
        }
    },
    requestJoinClan: async function(id) {
       var store = useClanStore();
       var result = await store.requestJoin(id);
       if (result) {
          alert("S'ha enviat la sol·licitud per unir-se al clan.");
       } else {
          alert(store.error || "Error en enviar la sol·licitud");
       }
    }
  }
}
</script>
