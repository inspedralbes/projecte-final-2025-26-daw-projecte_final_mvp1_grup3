<template>
  <div class="chat-overlay" @click.self="$emit('close')">
    <div class="chat-window">
      <div class="chat-header">
        <div class="chat-header__left">
          <div class="chat-header__avatar-ring" :style="avatarBgStyle">
            <div class="chat-header__avatar-inner">
              <img v-if="friendMonsterImg" :src="friendMonsterImg" alt="" class="chat-header__avatar-img" decoding="async" draggable="false" />
              <span v-else class="chat-header__initial">{{ friendName.charAt(0) }}</span>
            </div>
          </div>
          <div>
            <p class="chat-header__name">{{ friendName }}</p>
            <p :class="['chat-header__status', isOnline ? '' : 'chat-header__status--offline']">{{ isOnline ? 'En línia' : 'Fora de línia' }}</p>
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
          :class="['chat-msg-row', msg.sender_id === currentUserId ? 'chat-msg-row--mine' : 'chat-msg-row--theirs']"
        >
          <div v-if="msg.sender_id !== currentUserId" class="chat-msg-avatar-ring" :style="avatarBgStyle">
            <div class="chat-msg-avatar-inner">
              <img v-if="friendMonsterImg" :src="friendMonsterImg" alt="" class="chat-msg-avatar-img" decoding="async" draggable="false" />
              <span v-else class="chat-msg-avatar__letter">{{ friendName.charAt(0) }}</span>
            </div>
          </div>
          <div v-if="msg.sender_id === currentUserId" class="chat-msg-avatar-ring" :style="avatarBgStyle">
            <div class="chat-msg-avatar-inner">
              <img v-if="myMonsterImg" :src="myMonsterImg" alt="" class="chat-msg-avatar-img" decoding="async" draggable="false" />
              <span v-else class="chat-msg-avatar__letter">{{ myName.charAt(0) }}</span>
            </div>
          </div>
          <div :class="['chat-bubble', msg.sender_id === currentUserId ? 'chat-bubble--mine' : 'chat-bubble--theirs']">
            <p class="chat-bubble__name">{{ msg.sender_id === currentUserId ? myName : friendName }}</p>
            <button v-if="isClanInvite(msg)" type="button" class="chat-import-card" @click="goToClan(msg)">
              <span class="chat-import-card__badge chat-import-card__badge--clan">CLAN</span>
              <span class="chat-import-card__name">{{ getClanInviteText(msg) }}</span>
              <span class="chat-import-card__btn">Veure clan</span>
            </button>
            <div v-else-if="hasAttachments(msg)" class="chat-import-list">
              <p v-if="getPlainText(msg)" class="chat-bubble__text">{{ getPlainText(msg) }}</p>
              <div v-for="(att, ai) in parseAttachments(msg)" :key="ai" class="chat-import-card">
                <span class="chat-import-card__badge">{{ att.type === 'habit' ? 'HÀBIT' : 'PLANTILLA' }}</span>
                <span class="chat-import-card__name">{{ att.titol }}</span>
                <button type="button" class="chat-import-card__btn" @click="importAttachment(att)">Importar</button>
              </div>
            </div>
            <p v-else class="chat-bubble__text">{{ msg.contingut || '...' }}</p>
            <p class="chat-bubble__time">{{ formatTime(msg.created_at) }}</p>
          </div>
        </div>
      </div>

      <div v-if="attachments.length > 0" class="chat-attachments-preview">
        <span v-for="(att, i) in attachments" :key="i" class="chat-attachment-chip">
          {{ att.titol }}
          <button type="button" class="chat-attachment-chip__remove" @click="removeAttachment(i)">×</button>
        </span>
      </div>

      <div class="chat-input-bar">
        <form class="chat-input-bar__form" @submit.prevent="sendMessage">
          <button type="button" class="chat-input-bar__attach" @click="showAttach = true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </button>
          <input
            v-model="newMessage"
            type="text"
            placeholder="Escriu un missatge..."
            class="chat-input-bar__input"
          />
          <button
            type="submit"
            class="chat-input-bar__send"
            :disabled="(!newMessage.trim() && attachments.length === 0) || sending"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </form>
      </div>

      <AttachmentSelector :show="showAttach" @close="showAttach = false" @selected="onAttachSelected" />
    </div>
  </div>
</template>

<script>
import { useChatStore } from "~/stores/useChatStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { authFetch } from "~/utils/authFetch.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import AttachmentSelector from "~/components/user/social/AttachmentSelector.vue";

export default {
  name: "ChatWindow",
  props: {
    friendId: { type: Number, required: true },
    friendName: { type: String, required: true },
    friendMonstreTipus: { type: [Number, String], default: null },
    friendNivell: { type: [Number, String], default: null },
  },
  emits: ["close"],
  components: { AttachmentSelector },
  data: function () {
    return {
      newMessage: "",
      sending: false,
      pollInterval: null,
      showAttach: false,
      attachments: [],
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
    isOnline: function () {
      var chatStore = useChatStore();
      return chatStore.onlineUsers.includes(parseInt(this.friendId));
    },
    friendMonsterImg: function () {
      if (!this.friendMonstreTipus) return null;
      return getMonsterImageFromUser({ monstre_tipus: this.friendMonstreTipus, nivell: this.friendNivell });
    },
    myMonsterImg: function () {
      var auth = useAuthStore();
      if (!auth || !auth.user) return null;
      return getMonsterImageFromUser({ monstre_tipus: auth.user.monstre_tipus, nivell: auth.user.nivell });
    },
    myName: function () {
      var auth = useAuthStore();
      return auth && auth.user ? auth.user.nom : "Tu";
    },
    avatarBgStyle: function () {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center"
      };
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
      if ((!this.newMessage.trim() && this.attachments.length === 0) || !this.currentUserId) return;
      this.sending = true;
      var text = this.newMessage;
      var userId = this.currentUserId;
      var atts = this.attachments.slice();
      this.newMessage = "";
      this.attachments = [];

      try {
        if (atts.length > 0) {
          var attText = atts.map(function(a) {
            return (a.type === "habit" ? "📋 Hàbit: " : "📁 Plantilla: ") + a.titol + " [" + a.type + ":" + a.id + "]";
          }).join("\n");
          text = text ? text + "\n\n" + attText : attText;
        }
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
    onAttachSelected: function(att) {
      var exists = this.attachments.some(function(a) { return a.type === att.type && a.id === att.id; });
      if (!exists) {
        this.attachments.push(att);
      }
    },
    removeAttachment: function(index) {
      this.attachments.splice(index, 1);
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
    isClanInvite: function(msg) {
      return msg.contingut && msg.contingut.indexOf("→ /clans/") !== -1;
    },
    getClanInviteText: function(msg) {
      var text = msg.contingut || "";
      var idx = text.indexOf("→");
      return idx > 0 ? text.substring(0, idx).trim() : text;
    },
    goToClan: function(msg) {
      var text = msg.contingut || "";
      var match = text.match(/\/clans\/(\d+)/);
      if (match) {
        this.$router.push("/clans/" + match[1]);
      }
    },
    hasAttachments: function(msg) {
      return msg.contingut && /\[(habit|plantilla):\d+\]/.test(msg.contingut);
    },
    parseAttachments: function(msg) {
      var text = msg.contingut || "";
      var results = [];
      var regex = /(📋 Hàbit|📁 Plantilla): (.+?) \[(habit|plantilla):(\d+)\]/g;
      var m;
      while ((m = regex.exec(text)) !== null) {
        results.push({ type: m[3], titol: m[2], id: parseInt(m[4]) });
      }
      return results;
    },
    getPlainText: function(msg) {
      var text = msg.contingut || "";
      return text.replace(/(📋 Hàbit|📁 Plantilla): .+? \[(habit|plantilla):\d+\]/g, "").trim();
    },
    importAttachment: function(att) {
      if (att.type === 'habit') {
        this.$router.push("/habits?import=" + att.id);
      } else {
        this.$router.push("/Plantilles?import=" + att.id);
      }
    },
  },
};
</script>

<style scoped>
.chat-overlay {
  position: fixed;
  top: 100px;
  left: 0;
  right: 0;
  bottom: 72px;
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
  height: 100%;
  max-height: 100%;
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

.chat-header__avatar-ring {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid #e5e7eb;
}

.chat-header__avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-header__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.chat-header__initial {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #fff;
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
  transition: color 0.2s ease;
  font-size: 11px;
  color: #79D45D;
  font-weight: 600;
}

.chat-header__status--offline {
  color: #e74c3c;
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
  gap: 12px;
  min-height: 280px;
}

.chat-messages__empty {
  text-align: center;
  padding: 32px 0;
  color: #b0b0b0;
  font-size: 13px;
}

.chat-msg-row {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.chat-msg-row--mine {
  flex-direction: row-reverse;
}

.chat-msg-row--theirs {
  flex-direction: row;
}

.chat-msg-avatar-ring {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  margin-top: 2px;
  border: 1.5px solid #e5e7eb;
}

.chat-msg-avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-msg-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.chat-msg-avatar__letter {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: #fff;
}

.chat-bubble {
  max-width: 75%;
  border-radius: 14px;
  padding: 8px 12px;
  word-break: break-word;
}

.chat-bubble--mine {
  background: #79D45D;
  color: #fff;
  border-bottom-right-radius: 4px;
}

.chat-bubble--theirs {
  background: #5B9CE6;
  color: #fff;
  border-bottom-left-radius: 4px;
}

.chat-bubble__name {
  margin: 0 0 2px;
  font-size: 11px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.85);
}

.chat-bubble__text {
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
}

.chat-bubble__time {
  margin: 3px 0 0;
  font-size: 10px;
  opacity: 0.6;
  text-align: right;
}

.chat-input-bar__attach {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  border: 0;
  border-radius: 50%;
  background: #f3f4f6;
  color: #6b7280;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s, color 0.15s;
}

.chat-input-bar__attach:hover {
  background: #79D45D;
  color: #fff;
}

.chat-attachments-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 8px 16px 0;
}

.chat-attachment-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #ecfdf3;
  border: 1px solid #bbf7d0;
  border-radius: 99px;
  padding: 4px 10px 4px 12px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 600;
  color: #2b7a4b;
}

.chat-attachment-chip__remove {
  border: none;
  background: transparent;
  color: #888;
  cursor: pointer;
  font-size: 14px;
  line-height: 1;
  padding: 0;
  margin-left: 2px;
}

.chat-attachment-chip__remove:hover {
  color: #ff6b8a;
}

.chat-import-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
}

.chat-import-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: #fff;
  border: 1.5px solid #e0f5d8;
  border-radius: 12px;
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: box-shadow 0.15s;
}

.chat-import-card:hover {
  box-shadow: 0 2px 8px rgba(121, 212, 93, 0.15);
}

.chat-import-card__badge {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 10px;
  font-weight: 800;
  color: #fff;
  background: #79D45D;
  border-radius: 6px;
  padding: 4px 8px;
  white-space: nowrap;
  flex-shrink: 0;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.chat-import-card__badge--clan {
  background: #5B9CE6;
}

.chat-import-card__name {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #2b2d42;
  flex: 1;
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-import-card__btn {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 700;
  color: #fff;
  background: #79D45D;
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  cursor: pointer;
  white-space: nowrap;
  flex-shrink: 0;
  transition: opacity 0.15s;
}

.chat-import-card__btn:hover {
  opacity: 0.85;
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
