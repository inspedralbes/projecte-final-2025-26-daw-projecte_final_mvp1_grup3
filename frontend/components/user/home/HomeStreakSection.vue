<template>
  <div class="text-right">
    <p class="text-2xl font-bold">{{ $t('home.streak') }}: {{ ratxa }}</p>
    <p class="text-xs font-bold text-yellow-600 mb-1">{{ $t('home.max_streak') }}: {{ ratxaMaxima }}</p>
    <p class="text-sm text-green-600">{{ $t('home.xp_total') }}: {{ xpTotal }}</p>
    <p class="text-sm text-amber-600">{{ $t('home.coins') }}: {{ monedes }}</p>
  </div>
</template>

<script>
export default {
  name: 'HomeStreakSection',
  props: {
    ratxa: { type: Number, default: 0 },
    ratxaMaxima: { type: Number, default: 0 },
    xpTotal: { type: Number, default: 0 },
    monedes: { type: Number, default: 0 }
  }
};
</script>
