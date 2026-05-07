<template>
  <div class="rounded-lg lg:rounded-2xl p-1.5 sm:p-2 lg:p-6 border-l-[3px] lg:border-l-4 border-orange-400 bg-transparent lg:bg-white lg:shadow-lg">
    <div class="flex items-center gap-1 lg:gap-2 mb-1 lg:mb-4">
      <div class="w-4 h-4 lg:w-6 lg:h-6 bg-orange-400 rounded-full flex items-center justify-center shrink-0">
        <span class="text-white text-[9px] lg:text-sm leading-none">✓</span>
      </div>
      <h2 class="text-[10px] sm:text-[11px] lg:text-sm font-bold text-gray-900 lg:text-gray-800 uppercase tracking-wide leading-tight">
        {{ $t('home.daily_missions') }}
      </h2>
    </div>
    <div class="space-y-1 lg:space-y-3">
      <div class="bg-white/85 backdrop-blur-sm lg:bg-gray-50 rounded-md lg:rounded-lg p-1.5 lg:p-3 shadow-sm lg:shadow-none">
        <p class="text-gray-800 font-semibold text-[11px] lg:text-sm leading-snug line-clamp-3 lg:line-clamp-2">
          <template v-if="missioDiaria && missioDiaria.titol">{{ missioDiaria.titol }}</template>
          <template v-else>{{ $t('home.loading') }}</template>
        </p>
        <p class="text-base lg:text-2xl font-bold text-orange-500 mt-0.5 tabular-nums leading-none">
          {{ missioProgres }}/{{ missioObjectiu }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeMissionCard',
  props: {
    missioDiaria: { type: Object, default: null },
    missioCompletada: { type: Boolean, default: false },
    missioProgres: { type: Number, default: 0 },
    missioObjectiu: { type: Number, default: 1 }
  }
};
</script>
