<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
      <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">✍️</div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.details') }}</h2>
    </div>

    <div class="space-y-6">
      <div>
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.habit_name') }}</label>
        <input 
          :value="modelValue.nom" 
          @input="$emit('update:modelValue', { ...modelValue, nom: $event.target.value })"
          type="text" 
          :placeholder="$t('habits.placeholder_name')" 
          class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all font-bold" 
        />
      </div>

      <div>
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.motivation') }}</label>
        <textarea 
          :value="modelValue.motivacio" 
          @input="$emit('update:modelValue', { ...modelValue, motivacio: $event.target.value })"
          :placeholder="$t('habits.motivation_placeholder')" 
          class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all font-bold resize-none h-32"
        ></textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.difficulty') }}</label>
          <select 
            :value="modelValue.dificultat" 
            @change="$emit('update:modelValue', { ...modelValue, dificultat: $event.target.value })"
            class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all appearance-none cursor-pointer font-bold"
          >
            <option value="facil">{{ $t('habits.facil') }}</option>
            <option value="media">{{ $t('habits.media') }}</option>
            <option value="dificil">{{ $t('habits.dificil') }}</option>
          </select>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.daily_goal') }}</label>
            <input 
              :value="modelValue.objectiuVegades" 
              @input="$emit('update:modelValue', { ...modelValue, objectiuVegades: parseInt($event.target.value, 10) || 1 })"
              type="number" min="1" 
              class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all font-bold" 
            />
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.unit') }}</label>
            <input 
              :value="modelValue.unitat" 
              @input="$emit('update:modelValue', { ...modelValue, unitat: $event.target.value })"
              type="text" 
              :placeholder="$t('habits.placeholder_unit')" 
              class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all font-bold" 
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormDetails',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue']
};
</script>
