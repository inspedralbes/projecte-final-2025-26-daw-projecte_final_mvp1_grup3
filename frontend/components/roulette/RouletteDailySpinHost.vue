<template>
  <RouletteSpinVideoOverlay
    :visible="mostraVideoRuleta"
    :src="videoRuletaUrl"
    @ended="onVideoRuletaAcabat"
    @error="onVideoRuletaAcabat"
  />
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { useRouletteDailySpin } from '~/composables/useRouletteDailySpin.js';
import RouletteSpinVideoOverlay from '~/components/roulette/RouletteSpinVideoOverlay.vue';

const {
  videoRuletaUrl,
  mostraVideoRuleta,
  iniciarTirada,
  onVideoRuletaAcabat,
  reiniciarFluxTirada,
  registrarSocket,
  desregistrarSocket,
} = useRouletteDailySpin();

onMounted(function () {
  registrarSocket();
});

onBeforeUnmount(function () {
  desregistrarSocket();
  reiniciarFluxTirada();
});

defineExpose({
  iniciarTirada,
});
</script>
