<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
      <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📅</div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.planning') }}</h2>
    </div>

    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.frequency') }}</label>
          <select 
            :value="modelValue.frequencia" 
            @change="$emit('update:modelValue', { ...modelValue, frequencia: $event.target.value })"
            class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all appearance-none cursor-pointer font-bold"
          >
            <option value="diaria">{{ $t('habits.frequencies.diari') }}</option>
            <option value="setmanal">{{ $t('habits.frequencies.setmanal') }}</option>
            <option value="especifics">{{ $t('habits.frequencies.especifics') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.reminder') }}</label>
          <input 
            :value="modelValue.recordatori" 
            @input="$emit('update:modelValue', { ...modelValue, recordatori: $event.target.value })"
            type="time" 
            class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all font-bold" 
          />
        </div>
      </div>

      <div v-if="modelValue.frequencia === 'especifics'" class="animate-in fade-in slide-in-from-top-4 duration-300">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.target_days') }}</label>
        <div class="flex flex-wrap gap-2">
          <button 
            v-for="(day, index) in ['l', 'm', 'x', 'j', 'v', 's', 'd']" 
            :key="day" 
            type="button" 
            @click="$emit('toggle-day', index)" 
            :class="['w-10 h-10 rounded-xl font-bold transition-all', isDaySelected(index) ? 'bg-green-600 text-white shadow-md scale-110' : 'bg-gray-100 text-gray-400 hover:bg-gray-200']"
          >
            {{ $t('habits.days.' + day) }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormPlanning',
  props: {
    modelValue: { type: Object, required: true },
    isDaySelected: { type: Function, required: true }
  },
  emits: ['update:modelValue', 'toggle-day']
};
</script>
