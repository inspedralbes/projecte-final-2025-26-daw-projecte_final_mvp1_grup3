<template>
  <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
    <div class="flex-1 min-w-0 mr-4">
      <p class="font-semibold text-gray-800 truncate">{{ habit.nom }}</p>
      <p class="text-xs text-gray-500 truncate">{{ habit.frequencia }} · {{ $t('habits.difficulty') }}: {{ habit.dificultat || '-' }}</p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      <button
        type="button"
        @click="$emit('edit', habit)"
        class="px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
      >
        {{ $t('habits.edit') }}
      </button>
      <button
        type="button"
        @click="$emit('delete', habit)"
        class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition"
      >
        {{ $t('habits.delete') }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitListItem',
  props: { habit: { type: Object, required: true } }
};
</script>
