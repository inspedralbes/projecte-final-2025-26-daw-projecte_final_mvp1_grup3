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
      <div class="flex items-baseline gap-2">
         <span class="font-semibold text-sm text-gray-800">{{ msg.usuari ? msg.usuari.nom : 'Usuari' }}</span>
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
import { useClanChatStore } from "~/stores/useClanChatStore.js";
import { format } from "date-fns";

export default {
  name: "ClanChat",
  props: {
    clanId: {
       type: [Number, String],
       required: true
    }
  },
  data: function() {
    return {
       messages: [],
       newMessage: "",
       loading: true,
       sending: false
    }
  },
  mounted: function() {
     this.loadMessages();
     // Set up socket listener here if needed
     if (this.$nuxt.$socket) {
        this.$nuxt.$socket.on("clan_message", this.onMessageReceived);
     }
  },
  beforeDestroy: function() {
     if (this.$nuxt.$socket) {
        this.$nuxt.$socket.off("clan_message", this.onMessageReceived);
     }
  },
  methods: {
    loadMessages: async function() {
       this.loading = true;
       try {
         var store = useClanChatStore();
         await store.fetchMessages(this.clanId, 1);
         this.messages = store.messages;
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
          var contingut = this.newMessage;
          var msg = await store.sendMessage(this.clanId, contingut, null, null);
          if (msg) {
             this.newMessage = "";
             store.handleNewMessage(msg);
             this.messages = store.messages;
             this.scrollToBottom();
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
       // Only add it if it belongs to this clan
       if (message.clan_id == this.clanId) {
          var store = useClanChatStore();
          store.handleNewMessage(message);
          this.messages = store.messages;
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
          return format(new Date(dateStr), "HH:mm");
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
    }
  }
}
</script>
