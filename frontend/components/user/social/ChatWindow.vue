<template>
  <div class="fixed inset-0 bg-black/50 flex items-end z-50" @click.self="$emit('close')">
    <div class="bg-white rounded-t-2xl shadow-xl max-w-lg w-full mx-auto max-h-[80vh] flex flex-col">
      <div class="p-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
            <span class="text-blue-600 font-semibold">{{ friendName.charAt(0) }}</span>
          </div>
          <div>
            <p class="font-semibold text-gray-800">{{ friendName }}</p>
            <p class="text-xs text-gray-500">En línia</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[300px]">
        <div v-if="loading" class="text-center py-8 text-gray-500">Carregant...</div>
        <div v-else-if="!messages || messages.length === 0" class="text-center py-8 text-gray-500">
          No tens missatges
        </div>
        <div
          v-for="msg in messages"
          :key="msg.id || msg.created_at"
          :class="['max-w-[80%] rounded-2xl px-4 py-2', msg.sender_id === currentUserId ? 'ml-auto bg-blue-500 text-white' : 'bg-gray-100 text-gray-800']"
        >
          <p class="text-sm">{{ msg.contingut || '...' }}</p>
          <p :class="['text-xs mt-1', msg.sender_id === currentUserId ? 'text-blue-100' : 'text-gray-400']">
            {{ formatTime(msg.created_at) }}
          </p>
        </div>
      </div>

      <div class="p-4 border-t border-gray-100">
        <form @submit.prevent="sendMessage" class="flex gap-2">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Escriu un missatge..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:border-blue-500"
          />
          <button
            type="submit"
            :disabled="!newMessage.trim() || sending"
            class="p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 disabled:opacity-50"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { useChatStore } from "~/stores/useChatStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { authFetch } from "~/utils/authFetch.js";

export default {
  name: "ChatWindow",
  props: {
    friendId: { type: Number, required: true },
    friendName: { type: String, required: true },
  },
  emits: ["close"],
  data() {
    return {
      newMessage: "",
      sending: false,
      pollInterval: null,
    };
  },
  computed: {
    messages() {
      var chatStore = useChatStore();
      if (!chatStore.messages) return [];
      var msgs = chatStore.messages[this.friendId];
      if (!msgs) return [];
      if (!Array.isArray(msgs)) return [];
      return msgs;
    },
    loading() {
      return useChatStore().loading;
    },
    currentUserId() {
      var auth = useAuthStore();
      return auth && auth.user ? auth.user.id : null;
    },
  },
  async mounted() {
    await this.loadMessages();
    this.startPolling();
  },
  beforeUnmount() {
    this.stopPolling();
  },
  methods: {
    async loadMessages() {
      var chatStore = useChatStore();
      try {
        var url = "/api/chat/" + this.friendId;
        if (this.currentUserId) url += "?user_id=" + this.currentUserId;
        var resposta = await authFetch(url, {});
        if (resposta.ok) {
          var dades = await resposta.json();
          var msgs = [];
          if (Array.isArray(dades)) msgs = dades;
          else if (Array.isArray(dades.data)) msgs = dades.data;
          else if (Array.isArray(dades.messages)) msgs = dades.messages;
          
          chatStore.messages[this.friendId] = msgs;
          this.$nextTick(() => this.scrollToBottom());
        }
      } catch (e) {
        console.error("Error carregant missatges:", e);
      }
    },
    startPolling() {
      var self = this;
      this.loadMessages();
      this.pollInterval = setInterval(function() {
        self.loadMessages();
      }, 500);
    },
    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
        this.pollInterval = null;
      }
    },
    async sendMessage() {
      if (!this.newMessage.trim() || !this.currentUserId) return;
      this.sending = true;
      var text = this.newMessage;
      var userId = this.currentUserId;
      this.newMessage = "";
      
      try {
        var resposta = await authFetch("/api/chat/" + this.friendId, {
          method: "POST",
          body: JSON.stringify({ contingut: text, sender_id: userId })
        });
        if (!resposta.ok) {
          var err = await resposta.json();
          throw new Error(err.error || "Error enviant missatge");
        }
        await this.loadMessages();
      } catch (e) {
        alert(e.message);
      } finally {
        this.sending = false;
      }
    },
    scrollToBottom() {
      var container = this.$refs.messagesContainer;
      if (container) container.scrollTop = container.scrollHeight;
    },
    formatTime(dateStr) {
      if (!dateStr) return "";
      var date = new Date(dateStr);
      return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    },
  },
};
</script>