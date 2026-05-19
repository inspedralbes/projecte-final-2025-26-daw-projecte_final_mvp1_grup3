<!--
  Component o pagina Nuxt: RouletteSpinVideoOverlay.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="ruleta-video-root"
      role="dialog"
      aria-modal="true"
      :aria-label="$t('home.roulette_video_playing') || 'Ruleta en curs'"
      @contextmenu.prevent
    >
      <video
        ref="videoRef"
        class="ruleta-video-el"
        :src="src"
        tabindex="-1"
        muted
        playsinline
        autoplay
        disablepictureinpicture
        disableremoteplayback
        controlslist="nodownload noplaybackrate noremoteplayback"
        @ended="onEnded"
        @error="onError"
      />
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
  visible: { type: Boolean, default: false },
  src: { type: String, required: true },
});

const emit = defineEmits(['ended', 'error']);

const videoRef = ref(null);

function reproduir() {
  nextTick(function () {
    var el = videoRef.value;
    if (!el) {
      emit('error');
      return;
    }
    el.muted = true;
    el.volume = 0;
    el.currentTime = 0;
    var playPromise = el.play();
    if (playPromise && typeof playPromise.catch === 'function') {
      playPromise.catch(function () {
        emit('error');
      });
    }
  });
}

function onEnded() {
  emit('ended');
}

function onError() {
  emit('error');
}

watch(
  function () {
    return props.visible;
  },
  function (show) {
    if (show) {
      reproduir();
    }
  }
);
</script>

<style scoped>
.ruleta-video-root {
  position: fixed;
  inset: 0;
  z-index: 99990;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000;
  pointer-events: auto;
}

.ruleta-video-el {
  width: 100%;
  height: 100%;
  max-width: 100vw;
  max-height: 100vh;
  object-fit: cover;
  pointer-events: none;
  user-select: none;
}
</style>
