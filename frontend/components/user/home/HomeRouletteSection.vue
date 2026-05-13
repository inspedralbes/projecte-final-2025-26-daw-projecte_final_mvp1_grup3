<template>
  <div
    class="flex items-center justify-center cursor-pointer transition-transform hover:scale-105 active:scale-95"
    :class="[
      compact
        ? 'w-full h-full min-h-[4rem] sm:min-h-[4.25rem]'
        : 'p-2',
      classeIconaRuleta
    ]"
    @click="$emit('obrir-modal-ruleta')"
    :title="$t('home.roulette_daily')"
    role="button"
    tabindex="0"
    @keydown.enter="$emit('obrir-modal-ruleta')"
    @keydown.space.prevent="$emit('obrir-modal-ruleta')"
  >
    <img
      :src="rouletteIcon"
      alt="Ruleta"
      :class="compact ? 'w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] object-contain select-none' : 'w-24 h-24 object-contain select-none'"
      decoding="async"
      draggable="false"
    />
  </div>
</template>

<script>
import rouletteIcon from '~/assets/img/Icones/Icona_Ruleta.png'

export default {
  name: 'HomeRouletteSection',
  props: {
    classeIconaRuleta: { type: String, default: '' },
    compact: { type: Boolean, default: false }
  },
  data: function () {
    return {
      rouletteIcon: rouletteIcon
    };
  }
};
</script>
