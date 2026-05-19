<!--
  Component o pagina Nuxt: MemberList.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="bg-white rounded-xl shadow p-6 border">
    <h3 class="text-lg font-bold mb-4">Membres ({{ members.length }})</h3>
    <div v-if="loading" class="text-center py-4 text-gray-500">Carregant...</div>
    <ul v-else class="space-y-3">
      <li v-for="member in members" :key="member.usuari_id" class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border">
        <div class="flex items-center gap-3 flex-1">
          <div class="w-10 h-10 rounded-full overflow-hidden shadow-inner cursor-pointer" :style="avatarBackgroundStyle" @click="$emit('view-profile', member.usuari_id)">
            <div class="w-full h-full rounded-full border border-gray-200 bg-white/20 p-1 flex items-center justify-center">
              <img
                v-if="getMonsterImage(member)"
                :src="getMonsterImage(member)"
                alt="Monstre del perfil"
                class="w-full h-full object-contain"
                :style="getMonsterStyle(member)"
                decoding="async"
                draggable="false"
              />
            </div>
          </div>
          <div>
            <div class="font-medium text-gray-800">{{ member.nom || 'Usuari' }}</div>
            <span v-if="member.rol === 'lider' || member.rol === 'Líder'" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Líder</span>
          </div>
        </div>
<button v-if="isLeader && member.rol !== 'lider' && member.rol !== 'Líder' && member.usuari_id !== currentUserId" @click="removeMember(member.usuari_id)" class="text-sm text-red-500 hover:text-red-700 font-medium px-2 py-1">
           Expulsar
         </button>
      </li>
    </ul>
  </div>
</template>

<script>
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsAplicacioImg from "~/assets/img/Fons/Fons_Aplicacio.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import { useClanStore } from "~/stores/useClanStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useNuxtApp } from "#app";

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
  emits: ['member-removed', 'view-profile'],
  data: function() {
    return {
      loading: false,
      currentUserId: null
    }
  },
  computed: {
    members: function() {
      var store = useClanStore();
      return store.clanMembers || [];
    },
    avatarBackgroundStyle: function() {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center"
      };
    },
    getAvatarBgForMember: function() {
      return function(member) {
        var fonsKey = member ? member.fons_key : null;
        var bg = bosqueImg;
        if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
        else if (fonsKey === "fons_casa") bg = fonsCasaImg;
        else if (fonsKey === "fons_aplicacio") bg = fonsAplicacioImg;
        return { backgroundImage: "url(" + bg + ")", backgroundSize: "cover", backgroundPosition: "center" };
      };
    }
  },
  mounted: function() {
    var self = this;
    var authStore = useAuthStore();
    this.currentUserId = authStore.user ? authStore.user.id : null;
    this.loadMembers();
    var tryConnect = function() {
      var nuxtApp = useNuxtApp();
      if (nuxtApp.$socket && nuxtApp.$socket.connected) {
        nuxtApp.$socket.on("clan_member_left", function(data) {
          if (Number(data.clan_id) === Number(self.clanId)) {
            self.loadMembers();
          }
        });
        nuxtApp.$socket.on("clan_member_joined", function(data) {
          if (Number(data.clan_id) === Number(self.clanId)) {
            self.loadMembers();
          }
        });
      } else {
        setTimeout(tryConnect, 1000);
      }
    };
    tryConnect();
  },
  beforeUnmount: function() {
    var nuxtApp = useNuxtApp();
    if (nuxtApp.$socket) {
      nuxtApp.$socket.off("clan_member_left");
      nuxtApp.$socket.off("clan_member_joined");
    }
  },
  methods: {
    loadMembers: async function() {
      this.loading = true;
      try {
        var store = useClanStore();
        await store.fetchMembers(this.clanId);
      } catch(e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    removeMember: async function(userId) {
      var ok = await this.$loopyModal.confirm({
        title: "Expulsar membre",
        message: "Vols expulsar aquest membre?"
      });
      if (!ok) return;
      
      try {
        var store = useClanStore();
        var authStore = useAuthStore();
        var memberToRemove = this.members.find(function(m) { return Number(m.usuari_id) === Number(userId); });
        var userNom = memberToRemove ? memberToRemove.nom : 'Usuari';
        var success = await store.removeMember(this.clanId, userId);
        if (success) {
           var nuxtApp = useNuxtApp();
           if (nuxtApp.$socket && nuxtApp.$socket.connected) {
              nuxtApp.$socket.emit("clan_member_left", {
                 clan_id: this.clanId,
                 user_id: userId,
                 user_nom: userNom
              });
           }
           this.$emit('member-removed');
           this.loadMembers();
        } else {
           await this.$loopyModal.error("Error", store.error || "Error al expulsar membre");
        }
      } catch(e) {
        console.error(e);
      }
    },
    getMonsterImage: function(member) {
      if (!member) return null;
      var skinKey = member.skin_key || null;
      return getMonsterImageFromUser({
        monstre_tipus: member.monstre_tipus,
        nivell: member.nivell
      }, skinKey);
    },
    getMonsterStyle: function(member) {
      var n = Number(member.nivell) || 1;
      var scale = 1;
      if (n < 5) scale = 1.1;
      else if (n < 15) scale = 1.2;
      else if (n < 30) scale = 1.35;
      else scale = 1.5;
      
      return {
        transform: "scale(" + scale + ") translateY(5%)",
        filter: "drop-shadow(0 4px 6px rgba(0,0,0,0.2))"
      };
    }
  }
}
</script>
