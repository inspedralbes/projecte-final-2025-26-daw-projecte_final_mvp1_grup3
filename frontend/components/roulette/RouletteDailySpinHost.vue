<!--
  Component o pagina Nuxt: RouletteDailySpinHost.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <RouletteSpinVideoOverlay
    :visible="mostraVideoRuleta"
    :src="videoRuletaUrl"
    @ended="onVideoRuletaAcabat"
    @error="onVideoRuletaAcabat"
  />

  <Teleport to="body">
    <div
      v-if="modalObert"
      class="ruleta-modal-backdrop"
      :class="modalTipus === 'error' ? 'ruleta-modal-backdrop--error' : ''"
      @click="tancarModal"
    >
      <div
        class="ruleta-modal"
        :class="isPopping ? 'ruleta-modal--visible' : ''"
        @click.stop
      >
        <div class="ruleta-modal__glow"></div>

        <div class="ruleta-modal__header" :class="isPopping ? 'ruleta-modal__header--visible' : ''">
          <div v-if="modalTipus === 'success'" class="ruleta-modal__badge">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="28" cy="28" r="26" fill="#FFE066" stroke="#fff" stroke-width="3"/>
              <text x="28" y="34" text-anchor="middle" font-size="24" fill="#c88a00" font-weight="bold">★</text>
            </svg>
          </div>
          <div v-else class="ruleta-modal__badge ruleta-modal__badge--error">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="28" cy="28" r="26" fill="#FF6B8A" stroke="#fff" stroke-width="3"/>
              <text x="28" y="35" text-anchor="middle" font-size="26" fill="#fff" font-weight="bold">!</text>
            </svg>
          </div>
          <h2 class="ruleta-modal__title">{{ modalTitol }}</h2>
        </div>

        <div
          v-if="modalTipus === 'success' && (modalPremiType === 'xp' || modalPremiType === 'coins')"
          class="ruleta-modal__reward-wrap"
          :class="isPopping ? 'ruleta-modal__reward-wrap--visible' : ''"
        >
          <div class="ruleta-modal__reward">
            <img
              :src="modalPremiType === 'xp' ? xpIcon : coinIcon"
              :alt="modalPremiType === 'xp' ? 'XP' : 'Monedes'"
              class="ruleta-modal__reward-icon"
            />
            <span class="ruleta-modal__reward-value">+{{ modalPremiAmount }}</span>
            <span class="ruleta-modal__reward-label">{{ modalPremiType === 'xp' ? 'XP' : 'Monedes' }}</span>
          </div>
        </div>

        <p v-if="modalText" class="ruleta-modal__text" :class="isPopping ? 'ruleta-modal__text--visible' : ''">{{ modalText }}</p>

        <button
          type="button"
          class="ruleta-modal__btn"
          :class="[
            isPopping ? 'ruleta-modal__btn--visible' : '',
            modalTipus === 'error' ? 'ruleta-modal__btn--error' : ''
          ]"
          @click="tancarModal"
        >
          {{ modalTipus === 'success' ? 'Genial!' : 'Entesos' }}
        </button>

        <template v-if="isPopping && modalTipus === 'success'">
          <span class="ruleta-modal__burst ruleta-modal__burst--1"></span>
          <span class="ruleta-modal__burst ruleta-modal__burst--2"></span>
          <span class="ruleta-modal__burst ruleta-modal__burst--3"></span>
          <span class="ruleta-modal__burst ruleta-modal__burst--4"></span>
        </template>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRouletteDailySpin } from '~/composables/useRouletteDailySpin.js';
import RouletteSpinVideoOverlay from '~/components/roulette/RouletteSpinVideoOverlay.vue';
import coinIcon from '~/assets/img/Icones/Icona_Moneda.png';
import xpIcon from '~/assets/img/Icones/Icona_Experiencia.png';

const {
  videoRuletaUrl,
  mostraVideoRuleta,
  modalObert,
  modalTitol,
  modalText,
  modalTipus,
  modalPremiType,
  modalPremiAmount,
  tancarModal,
  iniciarTirada,
  onVideoRuletaAcabat,
  reiniciarFluxTirada,
  registrarSocket,
  desregistrarSocket,
} = useRouletteDailySpin();

const isPopping = ref(false);
var popTimeout = null;

watch(modalObert, function (open) {
  if (open) {
    isPopping.value = false;
    if (popTimeout) clearTimeout(popTimeout);
    popTimeout = setTimeout(function () { isPopping.value = true; }, 100);
  } else {
    if (popTimeout) clearTimeout(popTimeout);
    isPopping.value = false;
  }
});

onMounted(function () {
  registrarSocket();
});

onBeforeUnmount(function () {
  desregistrarSocket();
  reiniciarFluxTirada();
  if (popTimeout) clearTimeout(popTimeout);
});

defineExpose({
  iniciarTirada,
});
</script>

<style scoped>
.ruleta-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: rgba(26, 107, 58, 0.92);
  backdrop-filter: blur(6px);
  animation: ruletaFadeIn 0.3s ease both;
}

.ruleta-modal-backdrop--error {
  background: rgba(120, 30, 50, 0.92);
}

.ruleta-modal {
  position: relative;
  width: 100%;
  max-width: 340px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 36px 28px 28px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  user-select: none;
}

.ruleta-modal__glow {
  position: absolute;
  inset: -30px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 224, 102, 0.25) 0%, transparent 70%);
  animation: ruletaGlow 2s ease-in-out infinite;
  pointer-events: none;
}

.ruleta-modal__header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  transform: scale(0.9) translateY(10px);
  opacity: 0;
  transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.5s ease;
}

.ruleta-modal__header--visible {
  transform: scale(1) translateY(0);
  opacity: 1;
}

.ruleta-modal__badge {
  position: relative;
  animation: ruletaBadgeBounce 1.5s ease-in-out infinite;
}

.ruleta-modal__badge--error {
  animation: none;
}

.ruleta-modal__title {
  font-size: 1.6rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: 0.02em;
  text-shadow: 0 3px 12px rgba(0, 0, 0, 0.25);
  margin: 0;
}

.ruleta-modal__reward-wrap {
  margin-bottom: 16px;
  transform: scale(0.5);
  opacity: 0;
  transition: transform 0.6s 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.5s 0.15s ease;
}

.ruleta-modal__reward-wrap--visible {
  transform: scale(1);
  opacity: 1;
}

.ruleta-modal__reward {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.15);
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  padding: 16px 32px;
  backdrop-filter: blur(4px);
}

.ruleta-modal__reward-icon {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.ruleta-modal__reward-value {
  font-size: 1.75rem;
  font-weight: 900;
  color: #fff;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  line-height: 1;
}

.ruleta-modal__reward-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.ruleta-modal__text {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.85);
  margin: 0 0 20px;
  max-width: 280px;
  line-height: 1.4;
  transform: translateY(8px);
  opacity: 0;
  transition: transform 0.5s 0.25s ease, opacity 0.5s 0.25s ease;
}

.ruleta-modal__text--visible {
  transform: translateY(0);
  opacity: 1;
}

.ruleta-modal__btn {
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
  transition: transform 0.15s, box-shadow 0.15s, opacity 0.5s 0.3s ease;
  transform: scale(0.9);
  opacity: 0;
}

.ruleta-modal__btn--visible {
  transform: scale(1);
  opacity: 1;
}

.ruleta-modal__btn--error {
  color: #781e32;
}

.ruleta-modal__btn:hover {
  transform: scale(1.04);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.ruleta-modal__btn:active {
  transform: scale(0.97);
}

.ruleta-modal__burst {
  position: absolute;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  pointer-events: none;
  animation: ruletaBurst 1s ease-out forwards;
}

.ruleta-modal__burst--1 { background: #FFE066; top: 25%; left: 8%; animation-delay: 0.1s; --bx: -25px; --by: -35px; }
.ruleta-modal__burst--2 { background: #79D45D; top: 15%; right: 10%; animation-delay: 0.25s; --bx: 30px; --by: -25px; }
.ruleta-modal__burst--3 { background: #fff; bottom: 30%; left: 6%; animation-delay: 0.4s; --bx: -30px; --by: 20px; }
.ruleta-modal__burst--4 { background: #FFE066; bottom: 25%; right: 8%; animation-delay: 0.55s; --bx: 25px; --by: 25px; }

@keyframes ruletaFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes ruletaGlow {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.1); }
}

@keyframes ruletaBadgeBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

@keyframes ruletaBurst {
  0% { transform: scale(0) translate(0, 0); opacity: 1; }
  50% { opacity: 1; }
  100% { transform: scale(1.5) translate(var(--bx, 20px), var(--by, -30px)); opacity: 0; }
}
</style>
