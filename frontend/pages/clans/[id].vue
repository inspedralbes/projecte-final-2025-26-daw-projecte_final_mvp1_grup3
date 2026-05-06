<template>
  <div class="min-h-screen overflow-x-hidden pb-24 lg:pb-20">
    <div class="w-full max-w-6xl mx-auto min-w-0 box-border px-2 sm:px-4 md:px-6 pt-2 sm:pt-3">
      <div
        class="rounded-2xl sm:rounded-3xl overflow-hidden bg-white shadow-md border border-gray-100"
      >
        <HeaderSocial />
        <div class="px-3 sm:px-5 py-4 sm:py-8 relative">
          <transition name="fade" mode="out-in">
            <ClanSettings v-if="showEdit" :clan="clanData" @cancel="showEdit = false" @saved="onSaved" />
            <ClanDetail v-else :clan-id="clanId" @edit="openEdit" ref="clanDetail" />
          </transition>

          <InvitationModal :show="showInvite" :clan-id="clanId" :clan-name="clanName" @close="showInvite = false" />

          <button
            v-if="!showEdit && isLeader"
            type="button"
            @click="showInvite = true"
            class="fixed bottom-8 right-8 bg-green-500 text-white rounded-full p-4 shadow-lg hover:bg-green-600 transition-transform transform hover:scale-105 group"
            title="Convidar Usuari"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderSocial from "~/components/HeaderSocial.vue";
import ClanDetail from "~/components/clans/ClanDetail.vue";
import ClanSettings from "~/components/clans/ClanSettings.vue";
import InvitationModal from "~/components/clans/InvitationModal.vue";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";
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
        showInvite: false,
        checkInterval: null,
        alreadyExpelled: false
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
     this.checkMembership();
     this.setupSocketListener();
   },
   beforeUnmount: function() {
     this.removeSocketListener();
   },
  methods: {
     setupSocketListener: function() {
       var self = this;
       var tryConnect = function() {
          var nuxtApp = useNuxtApp();
          var authStore = useAuthStore();
          if (nuxtApp.$socket && nuxtApp.$socket.connected) {
             nuxtApp.$socket.on("clan_member_joined", function(data) {
                console.log("Page received clan_member_joined", data);
                if (Number(data.clan_id) === Number(self.clanId)) {
                   if (self.$refs.clanDetail && typeof self.$refs.clanDetail.reload === 'function') {
                      self.$refs.clanDetail.reload();
                   }
                }
             });
             nuxtApp.$socket.on("clan_request_accepted", function(data) {
                console.log("Page received clan_request_accepted", data);
                if (Number(data.clan_id) === Number(self.clanId)) {
                   if (self.$refs.clanDetail && typeof self.$refs.clanDetail.reload === 'function') {
                      self.$refs.clanDetail.reload();
                   }
                }
             });
             nuxtApp.$socket.on("clan_member_left", function(data) {
                console.log("Page received clan_member_left", data);
                if (Number(data.clan_id) === Number(self.clanId)) {
                   if (Number(data.user_id) === Number(authStore.user.id)) {
                      if (!self.alreadyExpelled) {
                         self.alreadyExpelled = true;
                         alert("Has estat expulsat del clan.");
                         self.$router.push("/clans");
                      }
                   } else {
                      if (self.$refs.clanDetail && typeof self.$refs.clanDetail.reload === 'function') {
                         self.$refs.clanDetail.reload();
                      }
                   }
                }
             });
          } else {
             setTimeout(tryConnect, 1000);
          }
       };
       tryConnect();
     },
     removeSocketListener: function() {
       var nuxtApp = useNuxtApp();
       if (nuxtApp.$socket) {
          nuxtApp.$socket.off("clan_member_joined");
          nuxtApp.$socket.off("clan_request_accepted");
          nuxtApp.$socket.off("clan_member_left");
       }
     },
     checkMembership: async function() {
        var store = useClanStore();
        await store.fetchMembers(this.clanId);
        var authStore = useAuthStore();
        var isMember = store.clanMembers.some(function(m) {
           return Number(m.usuari_id) === Number(authStore.user.id);
        });
        var clan = store.currentClan;
        if (clan && Number(clan.lider_id) === Number(authStore.user.id)) {
           isMember = true;
        }
        if (!isMember && !this.alreadyExpelled) {
           this.alreadyExpelled = true;
           alert("Has estat expulsat del clan.");
           this.$router.push("/clans");
        }
     },
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
