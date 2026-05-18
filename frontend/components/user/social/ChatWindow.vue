<template>
  <div class="chat-overlay" @click.self="$emit('close')">
    <div class="chat-window">
      <div class="chat-header">
        <div class="chat-header__left">
          <div class="chat-header__avatar">
            <span class="chat-header__initial">{{ friendName.charAt(0) }}</span>
          </div>
          <div>
            <p class="chat-header__name">{{ friendName }}</p>
            <p class="chat-header__status">En línia</p>
          </div>
        </div>
        <button type="button" class="chat-header__close" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <div ref="messagesContainer" class="chat-messages">
        <div v-if="loading" class="chat-messages__empty">Carregant...</div>
        <div v-else-if="!messages || messages.length === 0" class="chat-messages__empty">
          No tens missatges
        </div>
        <div
          v-for="msg in messages"
          :key="msg.id || msg.created_at"
          :class="['chat-bubble', msg.sender_id === currentUserId ? 'chat-bubble--mine' : 'chat-bubble--theirs']"
        >
          <p class="chat-bubble__text">{{ msg.contingut || '...' }}</p>
          <p class="chat-bubble__time">{{ formatTime(msg.created_at) }}</p>
        </div>
      </div>

      <div class="chat-input-bar">
        <form class="chat-input-bar__form" @submit.prevent="sendMessage">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Escriu un missatge..."
            class="chat-input-bar__input"
          />
          <button
            type="submit"
            class="chat-input-bar__send"
            :disabled="!newMessage.trim() || sending"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
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
  data: function () {
    return {
      newMessage: "",
      sending: false,
      pollInterval: null,
    };
  },
  computed: {
    messages: function () {
      var chatStore = useChatStore();
      if (!chatStore.messages) return [];
      var msgs = chatStore.messages[this.friendId];
      if (!msgs) return [];
      if (!Array.isArray(msgs)) return [];
      return msgs;
    },
    loading: function () {
      return useChatStore().loading;
    },
    currentUserId: function () {
      var auth = useAuthStore();
      return auth && auth.user ? auth.user.id : null;
    },
  },
  mounted: async function () {
    await this.loadMessages();
    this.startPolling();
  },
  beforeUnmount: function () {
    this.stopPolling();
  },
  methods: {
    loadMessages: async function () {
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
          var self = this;
          this.$nextTick(function () { self.scrollToBottom(); });
        }
      } catch (e) {
        console.error("Error carregant missatges:", e);
      }
    },
    startPolling: function () {
      var self = this;
      this.loadMessages();
      this.pollInterval = setInterval(function () {
        self.loadMessages();
      }, 500);
    },
    stopPolling: function () {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
        this.pollInterval = null;
      }
    },
    sendMessage: async function () {
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
    scrollToBottom: function () {
      var container = this.$refs.messagesContainer;
      if (container) container.scrollTop = container.scrollHeight;
    },
    formatTime: function (dateStr) {
      if (!dateStr) return "";
      var date = new Date(dateStr);
      return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    },
  },
};
</script>

<style scoped>
.chat-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: flex-end;
  z-index: 50;
}

.chat-window {
  background: #fff;
  border-radius: 18px 18px 0 0;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.12);
  max-width: 480px;
  width: 100%;
  margin: 0 auto;
  height: 90vh;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  font-family: "Comfortaa", system-ui, sans-serif;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid #f0f0f0;
}

.chat-header__left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.chat-header__avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(121, 212, 93, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.chat-header__initial {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #79D45D;
}

.chat-header__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #2b2d42;
}

.chat-header__status {
  margin: 0;
  font-size: 11px;
  color: #79D45D;
  font-weight: 600;
}

.chat-header__close {
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 10px;
  background: #f5f5f5;
  color: #707070;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease;
}

.chat-header__close:hover {
  background: #e8e8e8;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 280px;
}

.chat-messages__empty {
  text-align: center;
  padding: 32px 0;
  color: #b0b0b0;
  font-size: 13px;
}

.chat-bubble {
  max-width: 78%;
  border-radius: 14px;
  padding: 10px 14px;
  word-break: break-word;
}

.chat-bubble--mine {
  align-self: flex-end;
  background: #79D45D;
  color: #fff;
}

.chat-bubble--theirs {
  align-self: flex-start;
  background: #FAF9F9;
  color: #2b2d42;
}

.chat-bubble__text {
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
}

.chat-bubble__time {
  margin: 4px 0 0;
  font-size: 10px;
  opacity: 0.65;
}

.chat-input-bar {
  padding: 12px 16px;
  border-top: 1px solid #f0f0f0;
}

.chat-input-bar__form {
  display: flex;
  gap: 8px;
  align-items: center;
}

.chat-input-bar__input {
  flex: 1;
  border: 1px solid #e5e5e5;
  border-radius: 999px;
  padding: 10px 18px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  color: #2b2d42;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.chat-input-bar__input::placeholder {
  color: #b0b0b0;
}

.chat-input-bar__input:focus {
  border-color: #79D45D;
  box-shadow: 0 0 0 3px rgba(121, 212, 93, 0.15);
}

.chat-input-bar__send {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border: 0;
  border-radius: 50%;
  background: #79D45D;
  color: #fff;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: filter 0.15s ease;
}

.chat-input-bar__send:hover:not(:disabled) {
  filter: brightness(0.95);
}

.chat-input-bar__send:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
