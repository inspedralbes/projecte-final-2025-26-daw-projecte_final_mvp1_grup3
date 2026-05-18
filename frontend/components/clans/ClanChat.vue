<template>
<div class="flex flex-col h-full bg-white rounded-xl shadow border h-[600px] max-h-screen">
  <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
    <h3 class="font-bold text-gray-800">Xat del Clan</h3>
  </div>
  <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="chatContainer">
    <div v-if="loading && messages.length === 0" class="text-center text-gray-500 py-4">
       Carregant missatges...
    </div>
    <div v-for="msg in messages" :key="msg.id" class="flex flex-col gap-1">
      <div class="flex items-center gap-2 pb-1">
        <div class="w-8 h-8 rounded-full overflow-hidden shadow-inner" :style="avatarBackgroundStyle">
          <div class="w-full h-full rounded-full border border-gray-200 bg-white/20 p-0.5 flex items-center justify-center">
              <img
                v-if="getMonsterImage(msg)"
                :src="getMonsterImage(msg)"
                alt="Monstre del perfil"
                class="w-full h-full object-contain"
                :style="getMonsterStyle(msg)"
                decoding="async"
                draggable="false"
              />
          </div>
        </div>
        <span class="font-semibold text-sm text-gray-800">{{ msg.usuari_nom || msg.usuari?.nom || 'Usuari' }}</span>
         <span class="text-xs text-gray-400">{{ formatDate(msg.created_at) }}</span>
      </div>
<div v-if="msg.habit_id && msg.habit" class="bg-blue-50 border border-blue-100 rounded p-4 inline-block max-w-[80%]">
          <p class="text-sm text-blue-800 font-medium mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Ha compartit un hàbit: {{ msg.habit.titol }}
          </p>
          <button @click="importHabit(msg.id)" class="text-xs px-3 py-1.5 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors font-medium">Importar Hàbit al Meu Perfil</button>
       </div>
       <div v-else-if="msg.plantilla_id && msg.plantilla" class="bg-purple-50 border border-purple-100 rounded p-4 inline-block max-w-[80%]">
          <p class="text-sm text-purple-800 font-medium mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Ha compartit una plantilla: {{ msg.plantilla.nom }}
          </p>
          <button @click="importPlantilla(msg.id)" class="text-xs px-3 py-1.5 bg-purple-500 text-white rounded hover:bg-purple-600 transition-colors font-medium">Copiar Plantilla</button>
       </div>
      <div v-else class="text-gray-700 bg-gray-50 p-3 rounded-lg rounded-tl-none border inline-block max-w-[80%] whitespace-pre-wrap">
        {{ msg.contingut }}
      </div>
    </div>
  </div>
  <div class="p-4 border-t bg-gray-50 rounded-b-xl">
    <form @submit.prevent="send" class="flex gap-2">
      <input v-model="newMessage" type="text" placeholder="Escriu un missatge..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
      <button type="submit" :disabled="!newMessage.trim() || sending" class="px-6 py-2 bg-blue-500 text-white rounded-lg disabled:opacity-50 font-medium hover:bg-blue-600 transition-colors shadow-sm">
         {{ sending ? '...' : 'Enviar' }}
      </button>
    </form>
  </div>
</div>
</template>

<script>
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Bosque.png";
import { useClanChatStore } from "~/stores/useClanChatStore.js";
import { useClanStore } from "~/stores/useClanStore.js";

export default {
  name: "ClanChat",
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
        messages: [],
        newMessage: "",
        loading: true,
        sending: false,
        lastMemberCount: 0
     }
   },
  computed: {
    avatarBackgroundStyle: function() {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center"
      };
    }
  },
mounted: function() {
       this.loadMessages();
       this.lastMemberCount = 0;
       var self = this;
       var tryConnect = function() {
          var nuxtApp = useNuxtApp();
          if (nuxtApp.$socket && nuxtApp.$socket.connected) {
             nuxtApp.$socket.emit("join_clan_room", { clan_id: self.clanId });
             nuxtApp.$socket.on("new_clan_message", self.onMessageReceived);
             nuxtApp.$socket.on("clan_member_joined", self.onMemberJoined);
             nuxtApp.$socket.on("clan_member_left", self.onMemberLeft);
          } else {
             setTimeout(tryConnect, 1000);
          }
       };
       tryConnect();
    },
   beforeDestroy: function() {
      var nuxtApp = useNuxtApp();
      if (nuxtApp.$socket && nuxtApp.$socket.connected) {
         nuxtApp.$socket.emit("leave_clan_room", { clan_id: this.clanId });
         nuxtApp.$socket.off("new_clan_message", this.onMessageReceived);
         nuxtApp.$socket.off("clan_member_joined", this.onMemberJoined);
         nuxtApp.$socket.off("clan_member_left", this.onMemberLeft);
      }
   },
  methods: {
loadMessages: async function() {
       this.loading = true;
       try {
         var clanStore = useClanStore();
         await clanStore.fetchMembers(this.clanId);
         this.lastMemberCount = clanStore.clanMembers ? clanStore.clanMembers.length : 0;
         var store = useClanChatStore();
         await store.fetchMessages(this.clanId, 1);
         this.messages = store.messages;
         if (this.messages.length > 0 && this.lastMemberCount === 0) {
            this.lastMemberCount = this.messages.length;
         }
         this.scrollToBottom();
       } catch(e) {
         console.error(e);
       } finally {
         this.loading = false;
       }
     },
    send: async function() {
       if (!this.newMessage.trim() && !this.sending) return;
       this.sending = true;
       try {
          var store = useClanChatStore();
          var authStore = useAuthStore();
          var contingut = this.newMessage;
var msg = await store.sendMessage(this.clanId, contingut, null, null);
           if (msg) {
              this.newMessage = "";
              this.messages.push(msg);
              this.scrollToBottom();
             var nuxtApp = useNuxtApp();
             var authStore = useAuthStore();
             if (nuxtApp.$socket && nuxtApp.$socket.connected) {
                nuxtApp.$socket.emit("clan_message", {
                   clan_id: this.clanId,
                   message: contingut,
                   usuari_id: authStore.user.id,
                   usuari_nom: authStore.user.nom,
                   monstre_tipus: authStore.user.monstre_tipus,
                   nivell: authStore.user.nivell,
                   created_at: new Date().toISOString()
                });
             }
          } else {
             alert(store.error || "Error al enviar missatge");
          }
       } catch (e) {
          console.error(e);
       } finally {
          this.sending = false;
       }
    },
onMessageReceived: function(message) {
       if (Number(message.clan_id) === Number(this.clanId)) {
          var authStore = useAuthStore();
          if (message.sender_id && Number(message.sender_id) === Number(authStore.user.id)) {
             return;
          }
          var exists = this.messages.some(function(m) {
             return m.id === message.id || (m.contingut === message.message && m.created_at === message.created_at);
          });
          if (!exists) {
             this.messages.push({
                id: message.id || Date.now(),
                clan_id: message.clan_id,
                usuari_id: message.sender_id || message.usuari_id,
                usuari_nom: message.usuari_nom,
                monstre_tipus: message.monstre_tipus,
                nivell: message.nivell,
                contingut: message.message,
                created_at: message.created_at
             });
             this.scrollToBottom();
          }
       }
    },
     onMemberJoined: function(data) {
        if (Number(data.clan_id) === Number(this.clanId)) {
           this.messages.push({
              id: Date.now(),
              clan_id: data.clan_id,
              usuari_id: data.user_id,
              usuari_nom: "Sistema",
              contingut: data.user_nom + " s'ha unit al clan",
              created_at: new Date().toISOString(),
              is_system: true
           });
           this.scrollToBottom();
        }
     },
     onMemberLeft: function(data) {
        if (Number(data.clan_id) === Number(this.clanId)) {
           this.messages.push({
              id: Date.now(),
              clan_id: data.clan_id,
              usuari_id: data.user_id,
              usuari_nom: "Sistema",
              contingut: data.user_nom + " ha estat expulsat del clan",
              created_at: new Date().toISOString(),
              is_system: true
           });
           this.scrollToBottom();
        }
     },
     scrollToBottom: function() {
       this.$nextTick(function() {
          var container = this.$refs.chatContainer;
          if (container) {
             container.scrollTop = container.scrollHeight;
          }
       }.bind(this));
    },
     formatDate: function(dateStr) {
        if (!dateStr) return "";
        try {
           var d = new Date(dateStr);
           var hours = String(d.getHours()).padStart(2, '0');
           var minutes = String(d.getMinutes()).padStart(2, '0');
           return hours + ":" + minutes;
        } catch(e) {
           return dateStr;
        }
     },
    importHabit: async function(msgId) {
       var store = useClanChatStore();
       var result = await store.importHabit(msgId);
       if (result) {
          alert("Hàbit importat amb èxit!");
       } else {
          alert(store.error || "Error al importar hàbit");
       }
    },
    importPlantilla: async function(msgId) {
       var store = useClanChatStore();
       // Import all habits of plantilla usually
       var result = await store.importPlantilla(msgId);
       if (result) {
          alert("Plantilla importada amb èxit!");
       } else {
          alert(store.error || "Error al importar plantilla");
       }
    },
    getMonsterImage: function(msg) {
      if (!msg || msg.is_system) return null;
      // Per missatges de sistema no mostrem monstre
      if (msg.usuari_nom === 'Sistema') return null;
      
      return getMonsterImageFromUser({
        monstre_tipus: msg.monstre_tipus,
        nivell: msg.nivell
      });
    },
    getMonsterStyle: function(msg) {
      var n = Number(msg.nivell) || 1;
      var scale = 1;
      if (n < 5) scale = 1.1;
      else if (n < 15) scale = 1.2;
      else if (n < 30) scale = 1.35;
      else scale = 1.5;
      
      return {
        transform: "scale(" + scale + ") translateY(5%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.15))"
      };
    }
  }
}
</script>
