<template>
  <div class="bg-white rounded-xl shadow p-6 border">
    <h3 class="text-lg font-bold mb-4">Sol·licituds Pendents ({{ requests.length }})</h3>
    <div v-if="loading" class="text-center py-4 text-gray-500">Carregant...</div>
    <div v-else-if="requests.length === 0" class="text-sm text-gray-500 p-4 text-center bg-gray-50 rounded-lg border">Cap sol·licitud pendent.</div>
    <ul v-else class="space-y-3">
      <li v-for="req in requests" :key="req.id" class="flex flex-col gap-2 p-3 bg-gray-50 rounded-lg border">
        <div class="text-sm">
           <span class="font-medium text-gray-800">{{ req.usuari_nom || 'Usuari' }}</span>
           <span class="text-xs text-gray-500 ml-2 bg-gray-200 px-2 py-1 rounded">{{ req.tipus }}</span>
        </div>
        <div class="flex gap-2 w-full mt-1">
          <button @click="accept(req.id)" class="flex-1 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm font-medium">Acceptar</button>
          <button @click="reject(req.id)" class="flex-1 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm font-medium">Rebutjar</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";
import { useNuxtApp } from "#app";

export default {
  name: "RequestManager",
  props: {
    clanId: {
      type: [Number, String],
      required: true
    }
  },
  data: function() {
    return {
      requests: [],
      loading: false,
      refreshInterval: null
    }
  },
  mounted: function() {
    this.loadRequests();
    var self = this;
    this.refreshInterval = setInterval(function() {
      self.loadRequests();
    }, 3000);
  },
  beforeDestroy: function() {
    if (this.refreshInterval) clearInterval(this.refreshInterval);
  },
  methods: {
    loadRequests: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        await store.fetchPendingRequests(this.clanId);
        this.requests = store.pendingRequests;
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    accept: async function(id) {
       try {
         var store = useClanStore();
         var result = await store.acceptRequest(id);
         if (result) {
            var req = this.requests.find(function(r) { return r.id === id; });
            if (req && req.usuari_id) {
               var nuxtApp = useNuxtApp();
               if (nuxtApp.$socket && nuxtApp.$socket.connected) {
                  nuxtApp.$socket.emit("clan_request_accepted", {
                     clan_id: this.clanId,
                     usuari_id: req.usuari_id
                  });
               }
            }
            this.$emit('request-handled');
            this.loadRequests();
         } else {
            alert(store.error || "Error al acceptar sol·licitud");
         }
       } catch(e) {
         console.error(e);
       }
    },
    reject: async function(id) {
       try {
         var store = useClanStore();
         var result = await store.rejectRequest(id);
         if (result) {
            var req = this.requests.find(function(r) { return r.id === id; });
            if (req && req.usuari_id) {
               var nuxtApp = useNuxtApp();
               if (nuxtApp.$socket && nuxtApp.$socket.connected) {
                  nuxtApp.$socket.emit("clan_request_rejected", {
                     clan_id: this.clanId,
                     usuari_id: req.usuari_id
                  });
               }
            }
            this.$emit('request-handled');
            this.loadRequests();
         } else {
            alert(store.error || "Error al rebutjar sol·licitud");
         }
       } catch(e) {
         console.error(e);
       }
    }
  }
}
</script>
