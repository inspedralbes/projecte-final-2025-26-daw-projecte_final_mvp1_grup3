<template>
  <div class="bg-white rounded-2xl shadow-lg p-6">
    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4">
      {{ $t('home.latest_achievements') }}
    </h3>
    <div class="flex justify-around items-center relative group min-h-[48px]">
      <template v-if="ultimsLogros.length > 0">
        <div
          v-for="logro in ultimsLogros"
          :key="logro.id"
          class="w-12 h-12 rounded-full flex items-center justify-center text-lg hover:scale-110 transition shadow-inner"
          :class="logro.obtingut ? 'bg-orange-100' : 'bg-gray-100 opacity-40'"
          :title="logro.nom"
        >
          {{ logro.obtingut ? '🏅' : '🔒' }}
        </div>
      </template>
      <template v-else>
        <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-xs text-gray-300 border border-dashed border-gray-200">?</div>
        <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-xs text-gray-300 border border-dashed border-gray-200">?</div>
        <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-xs text-gray-300 border border-dashed border-gray-200">?</div>
      </template>
      <button
        class="absolute -right-2 -bottom-2 w-8 h-8 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 hover:scale-110 transition flex items-center justify-center font-bold text-xl"
        :title="$t('home.see_all')"
        @click="$emit('obrir-modal-logros')"
      >
        +
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeLogrosCard',
  props: {
    ultimsLogros: { type: Array, default: function () { return []; } }
  }
};
</script>
