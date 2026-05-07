<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3">
      <div class="flex gap-2 w-full sm:w-auto sm:min-w-[280px]">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cercar clans..."
          class="flex-1 min-w-0 px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          @keyup.enter="search"
        />
        <button type="button" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 shrink-0" @click="search">
          Cercar
        </button>
      </div>
    </div>

    <div class="flex gap-2">
      <button @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'" class="px-4 py-2 rounded-lg font-medium transition-colors">
        Tots
      </button>
      <button @click="filterType = 'public'" :class="filterType === 'public' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700'" class="px-4 py-2 rounded-lg font-medium transition-colors">
        Públicos
      </button>
      <button @click="filterType = 'private'" :class="filterType === 'private' ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-700'" class="px-4 py-2 rounded-lg font-medium transition-colors">
        Privats
      </button>
    </div>
    
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
    </div>
    <div v-else-if="filteredClans.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat clans.
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="clan in filteredClans" :key="clan.id" class="bg-white rounded-xl shadow p-6 border">
        <h3 class="text-lg font-bold text-gray-800">{{ clan.nom }}</h3>
        <p class="text-sm text-gray-500 mt-1">Membres: {{ clan.members_count || clan.membres_count || 0 }} / {{ clan.max_membres }}</p>
        <p v-if="!clan.es_public" class="text-xs text-purple-600 font-medium mt-1">Privat</p>
        <p v-else class="text-xs text-green-600 font-medium mt-1">Públic</p>
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
      clans: [],
      filterType: "all"
    }
  },
  computed: {
    filteredClans: function() {
      var clans = this.clans;
      if (this.filterType === "public") {
        clans = clans.filter(function(c) { return c.es_public === true; });
      } else if (this.filterType === "private") {
        clans = clans.filter(function(c) { return c.es_public === false; });
      }
      if (this.searchQuery && this.searchQuery.trim()) {
        var query = this.searchQuery.toLowerCase();
        clans = clans.filter(function(c) {
          return c.nom && c.nom.toLowerCase().indexOf(query) !== -1;
        });
      }
      return clans;
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
       var authStore = useAuthStore();
       var clan = this.clans.find(function(c) { return c.id === id; });
       var result = await store.requestJoin(id);
       console.log(">>> requestJoinClan result:", result, "clan:", clan);
       if (result) {
          alert("S'ha enviat la sol·licitud per unir-se al clan.");
          var nuxtApp = useNuxtApp();
          console.log(">>> Emitint clan_request_notifydesde ClanList, socket:", nuxtApp.$socket && nuxtApp.$socket.connected);
          if (nuxtApp.$socket && nuxtApp.$socket.connected && clan) {
             nuxtApp.$socket.emit("clan_request_notify", {
                clan_id: id,
                clan_nom: clan.nom,
                leader_id: clan.lider_id,
                usuari_nom: authStore.user.nom
             });
          }
       } else {
          alert(store.error || "Error en enviar la sol·licitud");
       }
    }
  }
}
</script>
