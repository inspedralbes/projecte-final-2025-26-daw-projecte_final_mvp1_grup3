<template>
  <div class="space-y-4">
    <h3 class="text-lg font-bold text-gray-800">{{ $t('habits.my_habits') }}</h3>
    <div v-if="loading" class="text-center py-8 text-gray-500">{{ $t('home.loading') }}</div>
    <div v-else-if="habits.length === 0" class="text-center py-8 text-gray-500">{{ $t('habits.no_habits') }}</div>
    <div v-else class="space-y-3">
      <UserHabitsHabitListItem
        v-for="h in habits"
        :key="h.id"
        :habit="h"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'HabitList',
  props: {
    habits: { type: Array, default: function () { return []; } },
    loading: { type: Boolean, default: false }
  }
};
</script>
