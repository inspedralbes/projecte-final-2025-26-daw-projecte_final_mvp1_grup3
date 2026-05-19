<!--
  Component o pagina Nuxt: roulette.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="roulette-page-root">
    <RouletteDailySpinHost ref="ruletaDailySpin" />

    <div class="roulette-page min-h-screen overflow-x-hidden pb-24 lg:pb-12">
      <div class="max-w-lg mx-auto px-4 sm:px-6 pt-6 sm:pt-8">

        <div class="flex items-center mb-6">
          <button type="button" class="roulette-back-btn" @click="tornarHome">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>{{ $t('home.back') || 'Tornar' }}</span>
          </button>
        </div>

        <header class="roulette-heading">
          <img
            :src="rouletteIcon"
            alt=""
            class="roulette-heading__icon"
            width="80"
            height="80"
            decoding="async"
            draggable="false"
          />
          <h1 class="roulette-heading__title">{{ $t('home.roulette') || 'Ruleta' }}</h1>
          <p class="roulette-heading__subtitle">{{ $t('home.roulette_daily') || 'Ruleta diària' }}</p>
        </header>

        <div class="roulette-panel">
          <p v-if="canSpin" class="roulette-panel__hint">
            {{ $t('home.roulette_video_hint') || 'Prem el botó per veure la tirada i descobrir la teva recompensa.' }}
          </p>

          <div v-if="!canSpin" class="roulette-status roulette-status--done" role="status">
            <p class="roulette-status__text">
              {{ $t('home.roulette_not_available') || 'Ja has tirat la ruleta avui. Torna demà!' }}
            </p>
          </div>

          <button
            v-if="canSpin"
            type="button"
            class="roulette-spin-btn"
            :disabled="isSpinning"
            @click="girarRuleta"
          >
            <span v-if="isSpinning">{{ $t('home.roulette_video_playing') || 'Reproduint...' }}</span>
            <span v-else>{{ $t('home.roulette_spin_text') || 'Fer tirada diària' }}</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useGameStore } from '~/stores/gameStore.js';
import RouletteDailySpinHost from '~/components/roulette/RouletteDailySpinHost.vue';
import rouletteIcon from '~/assets/img/Icones/Icona_Ruleta.png';

const gameStore = useGameStore();
const ruletaDailySpin = ref(null);

const canSpin = computed(function () {
  return gameStore.canSpinRoulette;
});

const isSpinning = computed(function () {
  return gameStore.ruletaAnimant;
});

function tornarHome() {
  if (gameStore.ruletaAnimant) {
    return;
  }
  navigateTo('/home');
}

function girarRuleta() {
  if (ruletaDailySpin.value && typeof ruletaDailySpin.value.iniciarTirada === 'function') {
    ruletaDailySpin.value.iniciarTirada();
  }
}
</script>

<style scoped>
.roulette-page {
  background: transparent;
}

.roulette-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border-radius: 10px;
  border: 2px solid transparent;
  background-color: #faf9f9;
  color: #2b2d42;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  cursor: pointer;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.roulette-back-btn:hover {
  transform: translateY(-1px);
  background-color: #ffffff;
}

.roulette-heading {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
}

.roulette-heading__icon {
  width: 5rem;
  height: 5rem;
  object-fit: contain;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.12));
}

.roulette-heading__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: clamp(1.5rem, 4vw, 1.75rem);
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.roulette-heading__subtitle {
  margin: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 500;
  color: #707070;
}

.roulette-panel {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
  width: 100%;
  max-width: 20rem;
  margin: 0 auto;
}

.roulette-panel__hint {
  margin: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.45;
  color: #568039;
  text-align: center;
}

.roulette-spin-btn {
  width: 100%;
  padding: 1.15rem 2rem;
  border-radius: 10px;
  border: 2px solid #6fbc58;
  background-color: #79d45d;
  color: #ffffff;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  cursor: pointer;
  box-shadow: 0 4px 0 0 #6fbc58;
  transition: background-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}

.roulette-spin-btn:hover:not(:disabled) {
  background-color: #85dc6a;
}

.roulette-spin-btn:active:not(:disabled) {
  transform: translateY(2px);
  box-shadow: 0 2px 0 0 #6fbc58;
}

.roulette-spin-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.roulette-status {
  width: 100%;
  padding: 1rem 1.25rem;
  border-radius: 10px;
  text-align: center;
}

.roulette-status--done {
  background-color: #ecfdf3;
  border: 2px solid #79d45d;
}

.roulette-status__text {
  margin: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 600;
  line-height: 1.35;
  color: #568039;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
</style>
