<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
      <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">🎨</div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.style') }}</h2>
    </div>

    <div>
      <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.color') }}</label>
      <div class="flex flex-wrap gap-3">
        <button 
          v-for="c in colors" 
          :key="c" 
          type="button" 
          @click="$emit('update:color', c)" 
          :style="{ backgroundColor: c }" 
          :class="['w-10 h-10 rounded-full transition-all transform hover:scale-110 active:scale-90 shadow-sm border-4', selectedColor === c ? 'border-white ring-2 ring-gray-200' : 'border-transparent']"
        ></button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormStyle',
  props: {
    colors: { type: Array, required: true },
    selectedColor: { type: String, default: '#10B981' }
  },
  emits: ['update:color']
};
</script>
