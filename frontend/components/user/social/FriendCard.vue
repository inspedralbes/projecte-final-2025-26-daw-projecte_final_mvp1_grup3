<template>
  <div
    class="friend-card"
    @click="$emit('view-profile', friend.friend.id)"
  >
    <div class="friend-card__left">
      <div class="friend-card__avatar" :style="avatarBackgroundStyle">
        <div class="friend-card__avatar-inner">
          <img
            v-if="monsterImage"
            :src="monsterImage"
            alt="Monstre del perfil"
            class="friend-card__avatar-img"
            :style="monsterStyle"
            decoding="async"
            draggable="false"
          />
        </div>
      </div>
      <div class="friend-card__info">
        <p class="friend-card__name">{{ friend.friend.nom }}</p>
        <p class="friend-card__meta">Nivell {{ friend.friend.nivell }} · {{ friend.friend.xp_total }} XP</p>
      </div>
    </div>
    <button
      type="button"
      class="friend-card__chat-btn"
      :title="$t('friends.chat')"
      @click.stop="$emit('open-chat', friend.friend.id, friend.friend.nom)"
    >
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
      </svg>
    </button>
  </div>
</template>

<script>
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";

export default {
  name: "FriendCard",
  props: {
    friend: {
      type: Object,
      required: true,
    },
  },
  emits: ["open-chat", "view-profile"],
  computed: {
    monsterImage: function () {
      return getMonsterImageFromUser(this.friend.friend);
    },
    monsterStyle: function () {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.15))"
      };
    },
    avatarBackgroundStyle: function () {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    }
  }
};
</script>

<style scoped>
.friend-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #FAF9F9;
  border-radius: 10px;
  padding: 12px 14px;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.friend-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.friend-card__left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.friend-card__avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.friend-card__avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.15);
  padding: 1px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.friend-card__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.friend-card__info {
  min-width: 0;
}

.friend-card__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #2b2d42;
  line-height: 1.2;
}

.friend-card__meta {
  margin: 2px 0 0;
  font-size: 12px;
  color: #707070;
  line-height: 1.2;
}

.friend-card__chat-btn {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 10px;
  background: rgba(121, 212, 93, 0.12);
  color: #79D45D;
  cursor: pointer;
  transition: background 0.15s ease, transform 0.1s ease;
}

.friend-card__chat-btn:hover {
  background: rgba(121, 212, 93, 0.25);
  transform: scale(1.05);
}
</style>
