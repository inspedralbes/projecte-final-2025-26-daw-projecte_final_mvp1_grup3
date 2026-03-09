<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
      <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📂</div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.category') }}</h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <button 
        v-for="cat in categories" 
        :key="cat.id" 
        type="button" 
        @click="$emit('select', cat.id)" 
        :class="['p-6 rounded-2xl flex flex-col items-center justify-center gap-3 transition-all transform hover:scale-105 active:scale-95 border-2', selectedId === cat.id ? 'border-green-500 bg-green-50 shadow-md ring-4 ring-green-500/5' : 'bg-gray-50/50 border-gray-100 hover:border-green-200']"
      >
        <span class="text-4xl shadow-sm">{{ cat.icona }}</span>
        <span class="text-sm font-bold text-gray-700">{{ $t('habits.categories.' + cat.key) }}</span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormCategory',
  props: {
    categories: { type: Array, required: true },
    selectedId: { type: [Number, String], default: '' }
  },
  emits: ['select']
};
</script>
