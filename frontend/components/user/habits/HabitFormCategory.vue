<template>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <div class="flex items-center gap-3 mb-4">
      <div class="bg-orange-100 p-2 rounded-lg"><span class="text-xl">{{ $t('habits.category') }}</span></div>
      <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.category') }}</h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <button
        v-for="cat in categories"
        :key="cat.id"
        type="button"
        @click="$emit('select', cat.id)"
        :class="[
          'p-4 rounded-xl flex flex-col items-center justify-center gap-2 transition-all',
          selectedId === cat.id ? 'ring-2 ring-green-500 bg-green-50' : 'bg-white border border-gray-200 hover:border-green-300'
        ]"
      >
        <span class="text-2xl">{{ cat.icona }}</span>
        <span class="text-sm font-medium text-gray-700">{{ $t('habits.categories.' + cat.key) }}</span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormCategory',
  props: {
    categories: { type: Array, default: function () { return []; } },
    selectedId: { type: String, default: '' }
  }
};
</script>
