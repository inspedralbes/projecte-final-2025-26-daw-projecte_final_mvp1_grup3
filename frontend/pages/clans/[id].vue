<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <HeaderSocial />
    <div class="max-w-6xl mx-auto px-4 py-8 relative">
       <NuxtLink to="/clans" class="inline-flex items-center text-blue-500 hover:text-blue-700 mb-6 font-medium transition-colors">
         <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
         Tornar als Clans
       </NuxtLink>
       
       <transition name="fade" mode="out-in">
          <ClanSettings v-if="showEdit" :clan="clanData" @cancel="showEdit = false" @saved="onSaved" />
          <ClanDetail v-else :clan-id="clanId" @edit="openEdit" ref="clanDetail" />
       </transition>
       
       <InvitationModal :show="showInvite" :clan-id="clanId" :clan-name="clanName" @close="showInvite = false" />
       
       <button v-if="!showEdit && isLeader" @click="showInvite = true" class="fixed bottom-8 right-8 bg-green-500 text-white rounded-full p-4 shadow-lg hover:bg-green-600 transition-transform transform hover:scale-105 group" title="Convidar Usuari">
         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
       </button>
    </div>
  </div>
</template>

<script>
import HeaderSocial from "~/components/HeaderSocial.vue";
import ClanDetail from "~/components/clans/ClanDetail.vue";
import ClanSettings from "~/components/clans/ClanSettings.vue";
import InvitationModal from "~/components/clans/InvitationModal.vue";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useClanStore } from "~/stores/useClanStore.js";

export default {
  name: "ClanDetailPage",
  middleware: ["auth"],
  components: {
    HeaderSocial,
    ClanDetail,
    ClanSettings,
    InvitationModal
  },
  data: function() {
     return {
        showEdit: false,
        showInvite: false
     }
  },
  computed: {
     clanId: function() {
        return this.$route.params.id;
     },
     clanData: function() {
        var store = useClanStore();
        return store.currentClan;
     },
     isLeader: function() {
        var authStore = useAuthStore();
        return this.clanData && this.clanData.lider_id == authStore.user.id;
     },
     clanName: function() {
        return this.clanData ? this.clanData.nom : 'Clan';
     }
  },
  mounted: function() {
     var authStore = useAuthStore();
     if (authStore.user && authStore.user.nivell < 5) {
        alert("Has de ser nivell 5 o superior per accedir als clans.");
        this.$router.push("/social");
     }
  },
  methods: {
     openEdit: function() {
        this.showEdit = true;
     },
     onSaved: function() {
        this.showEdit = false;
        if (this.$refs.clanDetail) {
           this.$refs.clanDetail.loadClan();
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
