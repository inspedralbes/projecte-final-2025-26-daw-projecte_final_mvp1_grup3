<template>
  <div class="text-right">
    <p class="text-lg lg:text-2xl font-bold">{{ $t('home.streak') }}: {{ ratxa }}</p>
    <p class="text-[10px] lg:text-xs font-bold text-yellow-600 mb-1">{{ $t('home.max_streak') }}: {{ ratxaMaxima }}</p>
    <p class="text-xs lg:text-sm text-green-600">{{ $t('home.xp_total') }}: {{ xpTotal }}</p>
    <p class="flex items-center justify-end gap-1.5 text-xs lg:text-sm text-amber-600">
      <img :src="coinIcon" alt="" class="h-4 w-4 lg:h-5 lg:w-5 object-contain shrink-0 coin-pixel" width="20" height="20" aria-hidden="true" />
      <span>{{ $t('home.coins') }}: {{ monedes }}</span>
    </p>
  </div>
</template>

<script>
import coinIcon from "~/assets/img/coin-loopy.png";

export default {
  name: 'HomeStreakSection',
  props: {
    ratxa: { type: Number, default: 0 },
    ratxaMaxima: { type: Number, default: 0 },
    xpTotal: { type: Number, default: 0 },
    monedes: { type: Number, default: 0 }
  },
  data: function () {
    return { coinIcon: coinIcon };
  }
};
</script>

<style scoped>
.coin-pixel {
  image-rendering: pixelated;
}
</style>
