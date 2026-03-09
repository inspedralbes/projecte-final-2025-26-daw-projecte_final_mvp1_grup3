<template>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <div class="flex items-center gap-3 mb-4">
      <div class="bg-blue-100 p-2 rounded-lg"><span class="text-xl">{{ $t('habits.planning') }}</span></div>
      <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.planning') }}</h2>
    </div>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('habits.frequency') }}</label>
        <div class="flex bg-gray-100 rounded-lg p-1">
          <button
            v-for="freq in frequencies"
            :key="freq"
            type="button"
            @click="$emit('update:frequencia', freq)"
            :class="[
              'flex-1 py-1.5 text-sm font-medium rounded-md transition-all',
              modelValue.frequencia === freq ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            {{ $t('habits.frequencies.' + freq.toLowerCase()) }}
          </button>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ objectiuEtiqueta }}</label>
          <input
            :value="modelValue.objectiuVegades"
            @input="$emit('update:modelValue', { ...modelValue, objectiuVegades: parseInt($event.target.value, 10) || 1 })"
            type="number"
            min="1"
            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
          />
        </div>
      </div>
      <div v-if="modelValue.frequencia === 'Especifics'">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('habits.target_days') }}</label>
        <div class="flex justify-between">
          <button
            v-for="(dia, index) in diesSetmana"
            :key="dia"
            type="button"
            @click="$emit('toggle-day', index)"
            :class="[
              'w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors',
              isDaySelected(index) ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300'
            ]"
          >
            {{ $t('habits.days.' + dia.toLowerCase()) }}
          </button>
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('habits.reminder') }}</label>
        <input
          :value="modelValue.recordatori"
          @input="$emit('update:modelValue', { ...modelValue, recordatori: $event.target.value })"
          type="time"
          class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormPlanning',
  props: {
    modelValue: { type: Object, required: true },
    frequencies: { type: Array, default: function () { return ['Diari', 'Setmanal', 'Mensual', 'Especifics']; } },
    diesSetmana: { type: Array, default: function () { return ['Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte', 'Diumenge']; } },
    objectiuEtiqueta: { type: String, default: '' },
    isDaySelected: { type: Function, default: function () { return false; } }
  }
};
</script>
