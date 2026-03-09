<template>
  <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-400">
    <div class="flex items-center gap-2 mb-4">
      <div class="w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center">
        <span class="text-white text-sm">✓</span>
      </div>
      <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">
        {{ $t('home.daily_missions') }}
      </h2>
    </div>
    <div class="space-y-3">
      <div class="bg-gray-50 rounded-lg p-3">
        <p class="text-gray-700 font-semibold text-sm">
          <template v-if="missioDiaria && missioDiaria.titol">{{ missioDiaria.titol }}</template>
          <template v-else>{{ $t('home.loading') }}</template>
        </p>
        <p class="text-2xl font-bold text-orange-500">
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
