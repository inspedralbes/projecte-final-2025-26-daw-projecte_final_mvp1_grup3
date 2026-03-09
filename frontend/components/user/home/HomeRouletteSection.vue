<template>
  <div
    class="bg-white rounded-2xl shadow-lg p-4 flex items-center justify-center cursor-pointer transition"
    :class="classeIconaRuleta"
    @click="$emit('obrir-modal-ruleta')"
    :title="$t('home.roulette_daily')"
  >
    <span class="text-sm font-bold">{{ $t('home.roulette') }}</span>
  </div>
</template>

<script>
export default {
  name: 'HomeRouletteSection',
  props: {
    classeIconaRuleta: { type: String, default: '' }
  }
};
</script>
