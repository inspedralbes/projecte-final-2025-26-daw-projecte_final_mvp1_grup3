<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-[#1a6b3a]/90 backdrop-blur-sm transition-all duration-500" @click="tancar">
      <div class="mission-modal relative w-full max-w-sm p-8 flex flex-col items-center text-center select-none" @click.stop>

        <div class="absolute inset-0 rounded-full bg-white/10 blur-[80px] pointer-events-none animate-pulse"></div>

        <div class="z-10 mb-6 transform transition-all duration-700" :class="isPopping ? 'scale-100 opacity-100 translate-y-0' : 'scale-90 opacity-0 translate-y-4'">
          <h2 class="mission-modal__title">MISSIÓ COMPLETADA!</h2>
          <p v-if="missioTitol" class="mission-modal__subtitle">{{ missioTitol }}</p>
        </div>

        <div class="z-10 relative mb-8 transition-all duration-700 cubic-pop" :class="isPopping ? 'scale-100 opacity-100' : 'scale-50 opacity-0'">
          <div class="mission-modal__glow"></div>
          <div class="mission-modal__badge">
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="32" cy="32" r="30" fill="#79D45D" stroke="#fff" stroke-width="3"/>
              <path d="M20 33L28 41L44 25" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>

        <div class="z-10 w-full flex items-center justify-center gap-5 mb-8 transition-all duration-700 delay-200" :class="isPopping ? 'scale-100 opacity-100 translate-y-0' : 'scale-90 opacity-0 translate-y-4'">
          <div class="mission-modal__reward">
            <img :src="xpIcon" alt="XP" class="mission-modal__reward-icon" />
            <span class="mission-modal__reward-value">+{{ recompensaXp }} XP</span>
          </div>
          <div class="mission-modal__reward">
            <img :src="coinIcon" alt="Monedes" class="mission-modal__reward-icon" />
            <span class="mission-modal__reward-value">+{{ recompensaMonedes }}</span>
          </div>
        </div>

        <button
          type="button"
          class="z-10 mission-modal__btn transition-all duration-700 delay-300"
          :class="isPopping ? 'scale-100 opacity-100' : 'scale-90 opacity-0'"
          @click="tancar"
        >
          Genial!
        </button>

        <span v-if="isPopping" class="mission-modal__burst mission-modal__burst--1"></span>
        <span v-if="isPopping" class="mission-modal__burst mission-modal__burst--2"></span>
        <span v-if="isPopping" class="mission-modal__burst mission-modal__burst--3"></span>
        <span v-if="isPopping" class="mission-modal__burst mission-modal__burst--4"></span>
      </div>
    </div>
  </Teleport>
</template>

<script>
import coinIcon from "~/assets/img/Icones/Icona_Moneda.png";
import xpIcon from "~/assets/img/Icones/Icona_Experiencia.png";

export default {
  name: "MissionCompletedModal",
  props: {
    isOpen: { type: Boolean, required: true },
    missioTitol: { type: String, default: "" },
    recompensaXp: { type: Number, default: 150 },
    recompensaMonedes: { type: Number, default: 5 }
  },
  emits: ["close"],
  data: function () {
    return {
      coinIcon: coinIcon,
      xpIcon: xpIcon,
      isPopping: false,
      timeoutId: null
    };
  },
  watch: {
    isOpen: function (newVal) {
      var self = this;
      if (newVal) {
        self.isPopping = false;
        if (self.timeoutId) clearTimeout(self.timeoutId);
        self.timeoutId = setTimeout(function () {
          self.isPopping = true;
        }, 150);
      } else {
        if (self.timeoutId) clearTimeout(self.timeoutId);
        self.isPopping = false;
      }
    }
  },
  methods: {
    tancar: function () {
      this.$emit("close");
    }
  }
};
</script>

<style scoped>
.mission-modal {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
}

.mission-modal__title {
  font-size: 1.75rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  text-shadow: 0 3px 12px rgba(0, 0, 0, 0.25);
  margin: 0;
}

.mission-modal__subtitle {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 0.85rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.85);
  margin: 8px 0 0;
  max-width: 260px;
  margin-left: auto;
  margin-right: auto;
}

.mission-modal__glow {
  position: absolute;
  inset: -20px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(121, 212, 93, 0.4) 0%, transparent 70%);
  animation: glowPulse 2s ease-in-out infinite;
}

.mission-modal__badge {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: badgeBounce 1.5s ease-in-out infinite;
}

.mission-modal__reward {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.15);
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  border-radius: 14px;
  padding: 12px 18px;
  backdrop-filter: blur(4px);
}

.mission-modal__reward-icon {
  width: 36px;
  height: 36px;
  object-fit: contain;
}

.mission-modal__reward-value {
  font-size: 1rem;
  font-weight: 800;
  color: #fff;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.mission-modal__btn {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #1a6b3a;
  background: #fff;
  border: none;
  border-radius: 12px;
  padding: 12px 48px;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  transition: transform 0.15s, box-shadow 0.15s;
}

.mission-modal__btn:hover {
  transform: scale(1.04);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.mission-modal__btn:active {
  transform: scale(0.97);
}

.cubic-pop {
  transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.mission-modal__burst {
  position: absolute;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  pointer-events: none;
  animation: burstOut 1s ease-out forwards;
}

.mission-modal__burst--1 {
  background: #FFE066;
  top: 30%;
  left: 10%;
  animation-delay: 0.1s;
}

.mission-modal__burst--2 {
  background: #79D45D;
  top: 20%;
  right: 12%;
  animation-delay: 0.25s;
}

.mission-modal__burst--3 {
  background: #fff;
  bottom: 35%;
  left: 8%;
  animation-delay: 0.4s;
}

.mission-modal__burst--4 {
  background: #FFE066;
  bottom: 30%;
  right: 10%;
  animation-delay: 0.55s;
}

@keyframes burstOut {
  0% {
    transform: scale(0) translate(0, 0);
    opacity: 1;
  }
  50% {
    opacity: 1;
  }
  100% {
    transform: scale(1.5) translate(var(--bx, 20px), var(--by, -30px));
    opacity: 0;
  }
}

.mission-modal__burst--1 { --bx: -25px; --by: -35px; }
.mission-modal__burst--2 { --bx: 30px; --by: -25px; }
.mission-modal__burst--3 { --bx: -30px; --by: 20px; }
.mission-modal__burst--4 { --bx: 25px; --by: 25px; }

@keyframes glowPulse {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

@keyframes badgeBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}
</style>
