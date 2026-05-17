<template>
  <div class="profile-overlay" @click.self="$emit('close')">
    <div class="profile-modal">
      <div class="profile-modal__body">
        <button type="button" class="profile-modal__close" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <div v-if="loading" class="profile-loading">
          <div class="profile-loading__spinner"></div>
          <p>{{ $t('home.loading') }}</p>
        </div>

        <div v-else-if="profile" class="profile-content">
          <div class="profile-hero">
            <div class="profile-hero__avatar" :style="avatarBackgroundStyle">
              <div class="profile-hero__avatar-inner">
                <img
                  v-if="monsterImage"
                  :src="monsterImage"
                  alt="Monstre del perfil"
                  class="profile-hero__avatar-img"
                  :style="monsterStyle"
                  decoding="async"
                  draggable="false"
                />
              </div>
            </div>
            <h2 class="profile-hero__name">{{ profile.nom }}</h2>
            <p class="profile-hero__level">{{ $t('home.level') || 'Nivell' }} {{ profile.nivell }}</p>
          </div>

          <div class="profile-stats">
            <div class="profile-stat profile-stat--streak">
              <p class="profile-stat__value">🔥 {{ profile.streak }}</p>
              <p class="profile-stat__label">{{ $t('home.streak') || 'Ratxa actual' }}</p>
            </div>
            <div class="profile-stat profile-stat--medals">
              <p class="profile-stat__value">🏅</p>
              <p class="profile-stat__label">Medallas</p>
              <div v-if="profile.logros_showcase && profile.logros_showcase.length > 0" class="profile-stat__logros">
                <p
                  v-for="logro in profile.logros_showcase.slice(0,4)"
                  :key="logro.id"
                  class="profile-stat__logro-name"
                >
                  {{ logro.nom }}
                </p>
                <p v-if="profile.logros_showcase.length > 4" class="profile-stat__logro-more">+{{ profile.logros_showcase.length - 4 }} más</p>
              </div>
              <p v-else class="profile-stat__logro-empty">Sin logros</p>
            </div>
          </div>

          <div class="profile-monster" :style="avatarBackgroundStyle">
            <div class="profile-monster__frame">
              <img v-if="monsterImage" :src="monsterImage" alt="Monstre" class="profile-monster__img" :style="monsterStyleBig" />
            </div>
          </div>
        </div>

        <div v-else class="profile-error">
          {{ $t('profile.error') || 'Error carregant el perfil' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authFetch } from "~/utils/authFetch.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Bosque.png";

export default {
  name: "PublicProfileView",
  props: {
    userId: {
      type: [Number, String],
      required: true,
    },
  },
  emits: ["close"],
  data: function () {
    return {
      profile: null,
      loading: true,
    };
  },
  computed: {
    monsterImage: function () {
      if (!this.profile) return null;
      return getMonsterImageFromUser(this.profile);
    },
    monsterStyle: function () {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.15))"
      };
    },
    monsterStyleBig: function () {
      return {
        transform: "scale(1.1) translateY(5%)",
        filter: "drop-shadow(0 4px 8px rgba(0,0,0,0.2))"
      };
    },
    avatarBackgroundStyle: function () {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    }
  },
  mounted: async function () {
    await this.fetchProfile();
  },
  methods: {
    fetchProfile: async function () {
      this.loading = true;
      try {
        var resposta = await authFetch("/api/users/" + this.userId + "/profile", {});
        if (resposta.ok) {
          this.profile = await resposta.json();
        }
      } catch (e) {
        console.error("Error carregant perfil:", e);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.profile-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.profile-modal {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  max-width: 380px;
  width: 100%;
  margin: 0 16px;
  overflow: hidden;
  font-family: "Comfortaa", system-ui, sans-serif;
}

.profile-modal__body {
  padding: 24px;
  position: relative;
}

.profile-modal__close {
  position: absolute;
  top: 16px;
  right: 16px;
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
  z-index: 2;
}

.profile-modal__close:hover {
  background: #e8e8e8;
}

.profile-loading {
  text-align: center;
  padding: 48px 0;
  color: #b0b0b0;
  font-size: 13px;
}

.profile-loading__spinner {
  display: inline-block;
  width: 32px;
  height: 32px;
  border: 3px solid #f0f0f0;
  border-top-color: #79D45D;
  border-radius: 50%;
  animation: profile-spin 0.7s linear infinite;
  margin-bottom: 8px;
}

@keyframes profile-spin {
  to { transform: rotate(360deg); }
}

.profile-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.profile-hero {
  text-align: center;
}

.profile-hero__avatar {
  width: 96px;
  height: 96px;
  margin: 0 auto 12px;
  border-radius: 50%;
  overflow: hidden;
}

.profile-hero__avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.2);
  padding: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-hero__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.profile-hero__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #2b2d42;
}

.profile-hero__level {
  margin: 4px 0 0;
  font-size: 14px;
  font-weight: 700;
  color: #79D45D;
}

.profile-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.profile-stat {
  border-radius: 12px;
  padding: 14px 12px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 110px;
}

.profile-stat--streak {
  background: #FFF8F0;
  border: 1px solid #FFE8CC;
}

.profile-stat--medals {
  background: #FFFBF0;
  border: 1px solid #FFEEBB;
}

.profile-stat__value {
  margin: 0;
  font-size: 22px;
  font-weight: 800;
}

.profile-stat--streak .profile-stat__value {
  color: #E67E22;
}

.profile-stat__label {
  margin: 0;
  font-size: 11px;
  color: #707070;
}

.profile-stat__logros {
  margin-top: 6px;
  max-height: 70px;
  overflow-y: auto;
  width: 100%;
}

.profile-stat__logro-name {
  margin: 2px 0;
  font-size: 12px;
  font-weight: 700;
  color: #D4A017;
}

.profile-stat__logro-more {
  margin: 2px 0;
  font-size: 10px;
  color: #b0b0b0;
}

.profile-stat__logro-empty {
  margin: 6px 0 0;
  font-size: 12px;
  font-weight: 700;
  color: #D4A017;
}

.profile-monster {
  border-radius: 14px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.profile-monster__frame {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-monster__img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.profile-error {
  text-align: center;
  padding: 32px 0;
  color: #FF6B8A;
  font-size: 14px;
}
</style>
