<!--
  Component o pagina Nuxt: InvitationModal.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div v-if="show" class="invite-overlay" @click.self="$emit('close')">
    <div class="invite-sheet">
      <div class="invite-handle-row"><div class="invite-handle"></div></div>
      <div class="invite-body">
        <h2 class="invite-title">Convidar Amics</h2>
        <p class="invite-subtitle">Selecciona els amics que vols convidar al clan</p>

        <div v-if="loading" class="invite-empty">Carregant amics...</div>
        <div v-else-if="friends.length === 0" class="invite-empty">
          No tens cap amic a qui convidar.
        </div>
        <div v-else class="invite-list">
          <button
            v-for="friend in friends"
            :key="friend.id"
            type="button"
            class="invite-friend-item"
            :class="{ 'invite-friend-item--selected': isSelected(friend.id) }"
            @click="toggleSelect(friend.id)"
          >
            <div class="invite-friend-item__check">
              <div class="invite-check-box" :class="{ 'invite-check-box--active': isSelected(friend.id) }">
                <svg v-if="isSelected(friend.id)" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
            </div>
            <div class="invite-friend-item__avatar" :style="getAvatarBgStyle(friend)">
              <img v-if="getMonsterImage(friend)" :src="getMonsterImage(friend)" class="invite-friend-item__avatar-img" />
              <span v-else>{{ friend.nom ? friend.nom.charAt(0).toUpperCase() : '?' }}</span>
            </div>
            <div class="invite-friend-item__info">
              <span class="invite-friend-item__name">{{ friend.nom }}</span>
              <span class="invite-friend-item__level">Nivell {{ friend.nivell || 1 }}</span>
            </div>
          </button>
        </div>

        <div class="invite-actions">
          <button type="button" class="invite-btn invite-btn--cancel" @click="$emit('close')">Enrere</button>
          <button type="button" class="invite-btn invite-btn--confirm" :disabled="selected.length === 0 || sending" @click="sendInvites">
            Enviar{{ selected.length > 0 ? ' (' + selected.length + ')' : '' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authFetch } from "~/composables/useApi.js";
import { useClanStore } from "~/stores/useClanStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";

export default {
  name: "InvitationModal",
  props: {
    show: { type: Boolean, default: false },
    clanId: { type: [Number, String], required: true },
    clanName: { type: String, default: "Clan" }
  },
  emits: ["close"],
  data: function() {
    return {
      bosqueImg: bosqueImg,
      loading: false,
      sending: false,
      friends: [],
      selected: []
    };
  },
  watch: {
    show: function(val) {
      if (val) {
        this.selected = [];
        this.loadFriends();
      }
    }
  },
  methods: {
    getMonsterImage: function(friend) {
      if (!friend) return null;
      var skinKey = friend.skin_key || null;
      return getMonsterImageFromUser({ monstre_tipus: friend.monstre_tipus, nivell: friend.nivell }, skinKey);
    },
    getAvatarBgStyle: function(friend) {
      var fonsKey = friend ? friend.fons_key : null;
      var bg = bosqueImg;
      if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
      else if (fonsKey === "fons_casa") bg = fonsCasaImg;
      return { backgroundImage: "url(" + bg + ")" };
    },
    isSelected: function(id) {
      return this.selected.indexOf(id) !== -1;
    },
    toggleSelect: function(id) {
      var idx = this.selected.indexOf(id);
      if (idx === -1) {
        this.selected.push(id);
      } else {
        this.selected.splice(idx, 1);
      }
    },
    loadFriends: async function() {
      this.loading = true;
      try {
        var res = await authFetch("/api/friends");
        if (res.ok) {
          var data = await res.json();
          var list = data.data || data;
          this.friends = list.map(function(f) {
            return f.friend || f;
          });
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    sendInvites: async function() {
      if (this.selected.length === 0) return;
      this.sending = true;
      var authStore = useAuthStore();
      var senderId = authStore.user ? authStore.user.id : null;
      var store = useClanStore();
      var clanId = this.clanId;
      var clanName = this.clanName;
      var inviteText = "🏰 T'he convidat al clan \"" + clanName + "\"! Fes click per veure'l → /clans/" + clanId;

      var self = this;
      var errors = 0;
      for (var i = 0; i < self.selected.length; i++) {
        var userId = self.selected[i];
        try {
          await authFetch("/api/chat/" + userId, {
            method: "POST",
            body: JSON.stringify({ contingut: inviteText, sender_id: senderId })
          });
          await store.inviteUser(clanId, userId);
        } catch (e) {
          errors++;
        }
      }

      this.sending = false;
      if (errors === 0) {
        await self.$loopyModal.success("Invitacions", "Invitacions enviades amb èxit!");
      } else {
        await self.$loopyModal.warning(
          "Invitacions",
          "S'han enviat " + (self.selected.length - errors) + " invitacions. " + errors + " han fallat."
        );
      }
      this.$emit("close");
    }
  }
};
</script>

<style scoped>
.invite-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  background: rgba(0, 0, 0, 0.5);
}

.invite-sheet {
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
  border-radius: 24px 24px 0 0;
  background: #fff;
  box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
  max-height: 75vh;
  display: flex;
  flex-direction: column;
  animation: invite-slide-up 0.3s ease-out forwards;
}

.invite-handle-row {
  display: flex;
  justify-content: center;
  padding: 14px 0 8px;
}

.invite-handle {
  width: 48px;
  height: 5px;
  background: #e5e7eb;
  border-radius: 999px;
}

.invite-body {
  padding: 0 24px 28px;
  overflow-y: auto;
  flex: 1;
}

.invite-title {
  margin: 0 0 4px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #2b2d42;
  text-align: center;
}

.invite-subtitle {
  margin: 0 0 16px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  color: #6b7280;
  text-align: center;
}

.invite-empty {
  text-align: center;
  padding: 24px 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  color: #9ca3af;
}

.invite-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  background: #fafafa;
  border: 1.5px solid #ececec;
  border-radius: 14px;
  overflow: hidden;
  max-height: min(40vh, 20rem);
  overflow-y: auto;
  margin-bottom: 16px;
}

.invite-friend-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 14px;
  border: none;
  border-bottom: 1px solid #f0f0f0;
  background: transparent;
  cursor: pointer;
  transition: background 0.15s;
  text-align: left;
}

.invite-friend-item:last-child {
  border-bottom: none;
}

.invite-friend-item:hover {
  background: rgba(121, 212, 93, 0.07);
}

.invite-friend-item--selected {
  background: rgba(121, 212, 93, 0.10);
}

.invite-friend-item__check {
  flex-shrink: 0;
}

.invite-check-box {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid #d8d8d8;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s, border-color 0.15s;
}

.invite-check-box--active {
  background: #79D45D;
  border-color: #6FBC58;
}

.invite-friend-item__avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #fff;
  overflow: hidden;
  border: 2px solid rgba(121, 212, 93, 0.4);
}

.invite-friend-item__avatar-img {
  width: 32px;
  height: 32px;
  object-fit: contain;
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.15));
}

.invite-friend-item__info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.invite-friend-item__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: #2b2d42;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.invite-friend-item__level {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  color: #6b7280;
}

.invite-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.invite-btn {
  border-radius: 14px;
  padding: 13px 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.15s, filter 0.15s;
  border: none;
}

.invite-btn--cancel {
  background: transparent;
  color: #5e5e5e;
}

.invite-btn--cancel:hover {
  opacity: 0.75;
}

.invite-btn--confirm {
  background: #79D45D;
  border: 2px solid #6FBC58;
  color: #fff;
}

.invite-btn--confirm:hover:not(:disabled) {
  filter: brightness(0.97);
}

.invite-btn--confirm:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

@keyframes invite-slide-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
</style>
