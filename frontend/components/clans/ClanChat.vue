<!--
  Component o pagina Nuxt: ClanChat.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="clan-chat-inline">
    <div class="clan-chat-header" @click="$emit('open-info')">
      <div class="clan-chat-header__left">
        <div class="clan-chat-header__avatar">
          <span class="clan-chat-header__initial">{{ clanInitial }}</span>
        </div>
        <div>
          <p class="clan-chat-header__name">{{ clanName }}</p>
          <p class="clan-chat-header__status">{{ memberCount }} membres · Toca per veure info</p>
        </div>
      </div>
      <svg class="clan-chat-header__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </div>

      <div ref="chatContainer" class="clan-chat-messages">
        <div v-if="loading && messages.length === 0" class="clan-chat-messages__empty">Carregant...</div>
        <div v-else-if="messages.length === 0" class="clan-chat-messages__empty">No hi ha missatges</div>
        <template v-else>
          <div
            v-for="msg in messages"
            :key="msg.id"
            :class="['clan-chat-msg', isMine(msg) ? 'clan-chat-msg--mine' : 'clan-chat-msg--theirs']"
          >
            <button v-if="!isMine(msg) && !msg.is_system" type="button" class="clan-chat-msg__avatar" :style="avatarBackgroundStyle" @click="openProfile(msg.usuari_id)">
              <img
                v-if="getMonsterImage(msg)"
                :src="getMonsterImage(msg)"
                alt=""
                class="clan-chat-msg__avatar-img"
                decoding="async"
                draggable="false"
              />
            </button>
            <div v-if="isMine(msg)" class="clan-chat-msg__avatar" :style="avatarBackgroundStyle">
              <img
                v-if="myMonsterImg"
                :src="myMonsterImg"
                alt=""
                class="clan-chat-msg__avatar-img"
                decoding="async"
                draggable="false"
              />
            </div>
            <div :class="['clan-chat-bubble', isMine(msg) ? 'clan-chat-bubble--mine' : msg.is_system ? 'clan-chat-bubble--system' : 'clan-chat-bubble--theirs']">
              <p v-if="!msg.is_system" class="clan-chat-bubble__name">{{ isMine(msg) ? myName : (msg.usuari_nom || 'Usuari') }}</p>

              <div v-if="msg.habit_id && msg.habit" class="clan-chat-import-card">
                <span class="clan-chat-import-card__badge">HÀBIT</span>
                <span class="clan-chat-import-card__name">{{ msg.habit.titol }}</span>
                <button type="button" class="clan-chat-import-card__btn" @click="importHabit(msg.id)">Importar</button>
              </div>
              <div v-else-if="msg.plantilla_id && msg.plantilla" class="clan-chat-import-card">
                <span class="clan-chat-import-card__badge">PLANTILLA</span>
                <span class="clan-chat-import-card__name">{{ msg.plantilla.nom }}</span>
                <button type="button" class="clan-chat-import-card__btn" @click="importPlantilla(msg.id)">Importar</button>
              </div>
              <div v-else-if="hasAttachments(msg)" class="clan-chat-import-list">
                <p v-if="getPlainText(msg)" class="clan-chat-bubble__text">{{ getPlainText(msg) }}</p>
                <div v-for="(att, ai) in parseAttachments(msg)" :key="ai" class="clan-chat-import-card">
                  <span class="clan-chat-import-card__badge">{{ att.type === 'habit' ? 'HÀBIT' : 'PLANTILLA' }}</span>
                  <span class="clan-chat-import-card__name">{{ att.titol }}</span>
                  <button type="button" class="clan-chat-import-card__btn" @click="importAttachment(att)">Importar</button>
                </div>
              </div>
              <p v-else class="clan-chat-bubble__text">{{ msg.contingut }}</p>

              <p class="clan-chat-bubble__time">{{ formatDate(msg.created_at) }}</p>
            </div>
          </div>
        </template>
      </div>

      <div v-if="attachments.length > 0" class="clan-chat-attachments-preview">
        <span v-for="(att, i) in attachments" :key="i" class="clan-chat-attachment-chip">
          {{ att.titol }}
          <button type="button" class="clan-chat-attachment-chip__remove" @click="removeAttachment(i)">×</button>
        </span>
      </div>

      <div class="clan-chat-input-bar">
        <form class="clan-chat-input-bar__form" @submit.prevent="send">
          <button type="button" class="clan-chat-input-bar__attach" @click="showAttach = true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </button>
          <input
            v-model="newMessage"
            type="text"
            placeholder="Escriu un missatge..."
            class="clan-chat-input-bar__input"
          />
          <button
            type="submit"
            class="clan-chat-input-bar__send"
            :disabled="(!newMessage.trim() && attachments.length === 0) || sending"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </form>
      </div>

      <AttachmentSelector :show="showAttach" @close="showAttach = false" @selected="onAttachSelected" />

    <PlantillaHabitsImportModal
      :show="showPlantillaImport"
      :plantilla-id="plantillaImportId"
      :plantilla-titol="plantillaImportTitol"
      @close="tancarImportPlantilla"
      @imported="onPlantillaImportada"
    />
  </div>
</template>

<script>
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import { useClanChatStore } from "~/stores/useClanChatStore.js";
import { useClanStore } from "~/stores/useClanStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import AttachmentSelector from "~/components/user/social/AttachmentSelector.vue";
import PlantillaHabitsImportModal from "~/components/user/social/PlantillaHabitsImportModal.vue";

export default {
  name: "ClanChat",
  components: { AttachmentSelector, PlantillaHabitsImportModal },
  props: {
    clanId: { type: [Number, String], required: true },
    isLeader: { type: Boolean, default: false }
  },
  emits: ["open-info"],
  data: function() {
    return {
      messages: [],
      newMessage: "",
      loading: true,
      sending: false,
      showAttach: false,
      attachments: [],
      showPlantillaImport: false,
      plantillaImportId: null,
      plantillaImportTitol: ""
    }
  },
  computed: {
    avatarBackgroundStyle: function() {
      return { backgroundImage: "url(" + bosqueImg + ")", backgroundSize: "cover", backgroundPosition: "center" };
    },
    currentUserId: function() {
      var auth = useAuthStore();
      return auth && auth.user ? Number(auth.user.id) : null;
    },
    myMonsterImg: function() {
      var auth = useAuthStore();
      if (!auth || !auth.user) return null;
      return getMonsterImageFromUser({ monstre_tipus: auth.user.monstre_tipus, nivell: auth.user.nivell });
    },
    myName: function() {
      var auth = useAuthStore();
      return auth && auth.user ? auth.user.nom : "Tu";
    },
    clanName: function() {
      var store = useClanStore();
      return store.currentClan ? store.currentClan.nom : 'Clan';
    },
    clanInitial: function() {
      return this.clanName.charAt(0).toUpperCase();
    },
    memberCount: function() {
      var store = useClanStore();
      return (store.clanMembers || []).length;
    }
  },
  mounted: function() {
    this.loadMessages();
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
  beforeUnmount: function() {
    var nuxtApp = useNuxtApp();
    if (nuxtApp.$socket && nuxtApp.$socket.connected) {
      nuxtApp.$socket.emit("leave_clan_room", { clan_id: this.clanId });
      nuxtApp.$socket.off("new_clan_message", this.onMessageReceived);
      nuxtApp.$socket.off("clan_member_joined", this.onMemberJoined);
      nuxtApp.$socket.off("clan_member_left", this.onMemberLeft);
    }
  },
  methods: {
    isMine: function(msg) {
      return Number(msg.usuari_id) === this.currentUserId;
    },
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
    onAttachSelected: function(att) {
      var exists = this.attachments.some(function(a) { return a.type === att.type && a.id === att.id; });
      if (!exists) {
        this.attachments.push(att);
      }
    },
    removeAttachment: function(index) {
      this.attachments.splice(index, 1);
    },
    send: async function() {
      if ((!this.newMessage.trim() && this.attachments.length === 0) || this.sending) return;
      this.sending = true;
      try {
        var store = useClanChatStore();
        var authStore = useAuthStore();
        var contingut = this.newMessage;
        if (this.attachments.length > 0) {
          var attText = this.attachments.map(function(a) {
            return (a.type === "habit" ? "📋 Hàbit: " : "📁 Plantilla: ") + a.titol + " [" + a.type + ":" + a.id + "]";
          }).join("\n");
          contingut = contingut ? contingut + "\n\n" + attText : attText;
          this.attachments = [];
        }
        var msg = await store.sendMessage(this.clanId, contingut, null, null);
        if (msg) {
          this.newMessage = "";
          this.messages.push(msg);
          this.scrollToBottom();
          var nuxtApp = useNuxtApp();
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
          await this.$loopyModal.error("Error", store.error || "Error al enviar missatge");
        }
      } catch(e) {
        console.error(e);
      } finally {
        this.sending = false;
      }
    },
    onMessageReceived: function(message) {
      if (Number(message.clan_id) !== Number(this.clanId)) return;
      var authStore = useAuthStore();
      if (message.sender_id && Number(message.sender_id) === Number(authStore.user.id)) return;
      var exists = this.messages.some(function(m) { return m.id === message.id; });
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
    },
    onMemberJoined: function(data) {
      if (Number(data.clan_id) !== Number(this.clanId)) return;
      this.messages.push({ id: Date.now(), usuari_nom: "Sistema", contingut: (data.user_nom || 'Usuari') + " s'ha unit al clan", created_at: new Date().toISOString(), is_system: true });
      this.scrollToBottom();
    },
    onMemberLeft: function(data) {
      if (Number(data.clan_id) !== Number(this.clanId)) return;
      this.messages.push({ id: Date.now(), usuari_nom: "Sistema", contingut: (data.user_nom || 'Usuari') + " ha sortit del clan", created_at: new Date().toISOString(), is_system: true });
      this.scrollToBottom();
    },
    scrollToBottom: function() {
      this.$nextTick(function() {
        var c = this.$refs.chatContainer;
        if (c) c.scrollTop = c.scrollHeight;
      }.bind(this));
    },
    formatDate: function(dateStr) {
      if (!dateStr) return "";
      try { var d = new Date(dateStr); return String(d.getHours()).padStart(2,'0') + ":" + String(d.getMinutes()).padStart(2,'0'); } catch(e) { return ""; }
    },
    importHabit: async function(msgId) {
      var store = useClanChatStore();
      var result = await store.importHabit(msgId);
      if (result) {
        await this.$loopyModal.success("Importat", "Hàbit importat!");
      } else {
        await this.$loopyModal.error("Error", store.error || "Error");
      }
    },
    importPlantilla: function (msgId) {
      var msg = null;
      var i;
      for (i = 0; i < this.messages.length; i++) {
        if (this.messages[i].id === msgId) {
          msg = this.messages[i];
          break;
        }
      }
      if (!msg) {
        return;
      }
      var plantillaId = msg.plantilla_id;
      var titol = msg.plantilla && msg.plantilla.nom ? msg.plantilla.nom : "";
      if (!plantillaId) {
        return;
      }
      this.plantillaImportId = plantillaId;
      this.plantillaImportTitol = titol;
      this.showPlantillaImport = true;
    },
    getMonsterImage: function(msg) {
      if (!msg || msg.is_system) return null;
      var skinKey = msg.skin_key || null;
      return getMonsterImageFromUser({ monstre_tipus: msg.monstre_tipus, nivell: msg.nivell }, skinKey);
    },
    getMonsterStyle: function(msg) {
      var n = Number(msg.nivell) || 1;
      var s = n < 5 ? 1.1 : n < 15 ? 1.2 : n < 30 ? 1.35 : 1.5;
      return { transform: "scale(" + s + ") translateY(5%)", filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.15))" };
    },
    openProfile: function(userId) {
      if (userId) {
        this.$router.push("/social/profile/" + userId);
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
    importAttachment: function (att) {
      if (att.type === "habit") {
        this.$router.push("/habits?import=" + att.id);
        return;
      }
      this.plantillaImportId = att.id;
      this.plantillaImportTitol = att.titol || "";
      this.showPlantillaImport = true;
    },
    tancarImportPlantilla: function () {
      this.showPlantillaImport = false;
      this.plantillaImportId = null;
      this.plantillaImportTitol = "";
    },
    onPlantillaImportada: async function () {
      this.tancarImportPlantilla();
      if (this.$loopyModal && typeof this.$loopyModal.success === "function") {
        await this.$loopyModal.success("Importat", this.$t("social.import_success"));
      }
    }
  }
}
</script>

<style scoped>
.clan-chat-inline {
  position: fixed;
  top: 100px;
  left: 0;
  right: 0;
  bottom: 72px;
  background: #fff;
  border-radius: 18px 18px 0 0;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.12);
  max-width: 480px;
  width: 100%;
  margin: 0 auto;
  height: calc(100vh - 100px - 72px);
  max-height: calc(100vh - 100px - 72px);
  display: flex;
  flex-direction: column;
  font-family: "Comfortaa", system-ui, sans-serif;
  z-index: 50;
}

.clan-chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background 0.15s;
}

.clan-chat-header:hover {
  background: #f9fafb;
}

.clan-chat-header__left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.clan-chat-header__avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(121, 212, 93, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.clan-chat-header__initial {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #79D45D;
}

.clan-chat-header__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #2b2d42;
}

.clan-chat-header__status {
  margin: 0;
  font-size: 11px;
  color: #79D45D;
  font-weight: 600;
}

.clan-chat-header__chevron {
  margin-left: auto;
  color: #b0b0b0;
}

/* Messages */
.clan-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-height: 0;
}

.clan-chat-messages__empty {
  text-align: center;
  padding: 32px 0;
  color: #b0b0b0;
  font-size: 13px;
}

.clan-chat-msg {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.clan-chat-msg--mine {
  flex-direction: row-reverse;
}

.clan-chat-msg--theirs {
  flex-direction: row;
}

.clan-chat-msg__avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  flex-shrink: 0;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1.5px solid #e5e7eb;
  margin-top: 2px;
  cursor: pointer;
  padding: 0;
  background: transparent;
  transition: opacity 0.15s;
}

.clan-chat-msg__avatar:hover {
  opacity: 0.8;
}

.clan-chat-msg__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.clan-chat-bubble {
  max-width: 75%;
  border-radius: 14px;
  padding: 8px 12px;
  word-break: break-word;
}

.clan-chat-bubble--mine {
  background: #79D45D;
  color: #fff;
  border-bottom-right-radius: 4px;
}

.clan-chat-bubble--theirs {
  background: #5B9CE6;
  color: #fff;
  border-bottom-left-radius: 4px;
}

.clan-chat-bubble--system {
  background: transparent;
  color: #b0b0b0;
  font-size: 11px;
  font-style: italic;
  text-align: center;
  padding: 4px 0;
  max-width: 100%;
}

.clan-chat-bubble__name {
  margin: 0 0 2px;
  font-size: 11px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.85);
}

.clan-chat-bubble__text {
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
  white-space: pre-wrap;
}

.clan-chat-bubble__time {
  margin: 3px 0 0;
  font-size: 10px;
  opacity: 0.6;
  text-align: right;
}

.clan-chat-import-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
}

.clan-chat-import-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: #fff;
  border: 1.5px solid #e0f5d8;
  border-radius: 12px;
  width: 100%;
  text-align: left;
}

.clan-chat-import-card__badge {
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

.clan-chat-import-card__name {
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

.clan-chat-import-card__btn {
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

.clan-chat-import-card__btn:hover {
  opacity: 0.85;
}

/* Attachments preview */
.clan-chat-attachments-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 8px 16px 0;
}

.clan-chat-attachment-chip {
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

.clan-chat-attachment-chip__remove {
  border: none;
  background: transparent;
  color: #888;
  cursor: pointer;
  font-size: 14px;
  line-height: 1;
  padding: 0;
  margin-left: 2px;
}

.clan-chat-attachment-chip__remove:hover {
  color: #ff6b8a;
}

/* Input bar */
.clan-chat-input-bar {
  padding: 12px 16px;
  border-top: 1px solid #f0f0f0;
}

.clan-chat-input-bar__form {
  display: flex;
  gap: 8px;
  align-items: center;
}

.clan-chat-input-bar__input {
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

.clan-chat-input-bar__input::placeholder {
  color: #b0b0b0;
}

.clan-chat-input-bar__input:focus {
  border-color: #79D45D;
  box-shadow: 0 0 0 3px rgba(121, 212, 93, 0.15);
}

.clan-chat-input-bar__attach {
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

.clan-chat-input-bar__attach:hover {
  background: #79D45D;
  color: #fff;
}

.clan-chat-input-bar__send {
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

.clan-chat-input-bar__send:hover:not(:disabled) {
  filter: brightness(0.95);
}

.clan-chat-input-bar__send:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
