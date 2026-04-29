<template>
  <div class="bg-white rounded-xl shadow p-6 border">
    <h3 class="text-lg font-bold mb-4">Membres ({{ members.length }})</h3>
    <div v-if="loading" class="text-center py-4 text-gray-500">Carregant...</div>
    <ul v-else class="space-y-3">
      <li v-for="member in members" :key="member.usuari_id" class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border">
        <div class="flex items-center gap-3">
          <div class="font-medium text-gray-800">{{ member.usuari ? member.usuari.nom : 'Usuari' }}</div>
          <span v-if="member.rol === 'Lider'" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Líder</span>
        </div>
        <button v-if="isLeader && member.rol !== 'Lider'" @click="removeMember(member.usuari_id)" class="text-sm text-red-500 hover:text-red-700 font-medium px-2 py-1">
          Expulsar
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";

export default {
  name: "MemberList",
  props: {
    clanId: {
      type: [Number, String],
      required: true
    },
    isLeader: {
      type: Boolean,
      default: false
    }
  },
  data: function() {
    return {
      members: [],
      loading: false
    }
  },
  mounted: function() {
    this.loadMembers();
  },
  methods: {
    loadMembers: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        await store.fetchMembers(this.clanId);
        this.members = store.clanMembers;
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    removeMember: async function(userId) {
      if (!confirm("Vols expulsar aquest membre?")) return;
      
      try {
        var store = useClanStore();
        var success = await store.removeMember(this.clanId, userId);
        if (success) {
           this.$emit('member-removed');
           this.loadMembers();
        } else {
           alert(store.error || "Error al expulsar membre");
        }
      } catch(e) {
        console.error(e);
      }
    }
  }
}
</script>
