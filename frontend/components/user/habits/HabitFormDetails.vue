<template>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <div class="flex items-center gap-3 mb-4">
      <div class="bg-green-100 p-2 rounded-lg"><span class="text-xl">{{ $t('habits.details') }}</span></div>
      <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.details') }}</h2>
    </div>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('habits.habit_name') }}</label>
        <input
          :value="modelValue.nom"
          @input="$emit('update:modelValue', { ...modelValue, nom: $event.target.value })"
          type="text"
          :placeholder="$t('habits.placeholder_name')"
          class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
        />
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('habits.motivation') }}</label>
        <textarea
          :value="modelValue.motivacio"
          @input="$emit('update:modelValue', { ...modelValue, motivacio: $event.target.value })"
          :placeholder="$t('habits.motivation_placeholder')"
          class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all resize-none h-24"
        ></textarea>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ $t('habits.difficulty') }}</label>
        <select
          :value="modelValue.dificultat"
          @change="$emit('update:modelValue', { ...modelValue, dificultat: $event.target.value })"
          class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
        >
          <option value="facil">{{ $t('habits.facil') }}</option>
          <option value="media">{{ $t('habits.media') }}</option>
          <option value="dificil">{{ $t('habits.dificil') }}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitFormDetails',
  props: { modelValue: { type: Object, required: true } }
};
</script>
