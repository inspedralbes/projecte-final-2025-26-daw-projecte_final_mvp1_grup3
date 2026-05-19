<!--
  Component o pagina Nuxt: eggReveal.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="egg-reveal-container" :style="{ backgroundImage: `url(${fonsAplicacio})` }" @click="handleClick" @keydown.enter.prevent="handleClick" tabindex="0" role="button" aria-label="Continuar a la home">
    <div class="egg-reveal-content">
      <Transition name="fade" mode="out-in">
        <div v-if="currentStage === 'closed'" :key="'closed'" class="image-wrapper">
          <img :src="eggClosedImage" alt="Huevo cerrado" class="reveal-image" decoding="async" :class="{ shake: isShaking }" />
        </div>
        <div v-else-if="currentStage === 'open'" :key="'open'" class="image-wrapper">
          <img :src="eggOpenImage" alt="Huevo abierto" class="reveal-image" decoding="async" :class="{ shake: isShaking }" />
        </div>
        <div v-else-if="currentStage === 'monster'" :key="'monster'" class="image-wrapper monster">
          <img :src="monsterImage" alt="Tu monstruo" class="reveal-image" decoding="async" :class="{ shake: isShaking }" />
        </div>
      </Transition>
      <Transition name="fade-slow">
        <div v-if="showHint" class="click-hint">
          <p>Feu click per continuar</p>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getEggImage, getEggOpenImage, getMonsterImage } from '~/utils/monsterImage.js';
import fonsAplicacio from '~/assets/img/Fons/Fons_Aplicacio.png';

definePageMeta({ layout: false });

const route = useRoute();
const router = useRouter();

const monsterType = computed(() => {
  return route.query.type || 'VV';
});

const colorLetter = computed(() => monsterType.value.charAt(1).toUpperCase());

const eggClosedImage = computed(() => getEggImage(colorLetter.value));
const eggOpenImage = computed(() => getEggOpenImage(colorLetter.value));
const monsterImage = computed(() => getMonsterImage(monsterType.value, 1));

const currentStage = ref('');
const isShaking = ref(false);
const showHint = ref(false);

function triggerShake(duration = 300) {
  isShaking.value = true;
  setTimeout(() => {
    isShaking.value = false;
    setTimeout(() => {
      isShaking.value = true;
      setTimeout(() => {
        isShaking.value = false;
      }, duration);
    }, 100);
  }, duration);
}

function handleClick() {
  if (currentStage.value === 'monster') {
    router.push('/home');
  }
}

onMounted(() => {
  setTimeout(() => {
    currentStage.value = 'closed';
    setTimeout(() => triggerShake(300), 800);
  }, 500);

  setTimeout(() => {
    currentStage.value = 'open';
    setTimeout(() => triggerShake(300), 800);
  }, 2800);

  setTimeout(() => {
    currentStage.value = 'monster';
    setTimeout(() => triggerShake(300), 800);
  }, 4600);

  setTimeout(() => {
    showHint.value = true;
  }, 5500);
});
</script>

<style scoped>
.egg-reveal-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  box-sizing: border-box;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.egg-reveal-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: 500px;
  position: relative;
  min-height: 60vh;
}

.image-wrapper {
  width: 280px;
  height: 320px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-wrapper.monster {
  width: 300px;
  height: 350px;
}

.reveal-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
}

.image-wrapper.monster .reveal-image {
  filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.3));
}

.shake {
  animation: shake 0.6s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0) rotate(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-8px) rotate(-3deg); }
  20%, 40%, 60%, 80% { transform: translateX(8px) rotate(3deg); }
}

.click-hint {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  font-family: 'Comfortaa', sans-serif;
  font-size: 16px;
  color: #64748b;
  font-weight: 600;
  text-align: center;
  white-space: nowrap;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-slow-enter-active {
  transition: opacity 1s ease;
}

.fade-slow-enter-from {
  opacity: 0;
}

.fade-slow-enter-to {
  opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
  .shake {
    animation: none;
  }
  .fade-enter-active,
  .fade-leave-active,
  .fade-slow-enter-active {
    transition: none;
  }
}

@media (max-width: 480px) {
  .image-wrapper {
    width: 220px;
    height: 250px;
  }

  .image-wrapper.monster {
    width: 240px;
    height: 280px;
  }

  .click-hint {
    font-size: 14px;
  }
}
</style>