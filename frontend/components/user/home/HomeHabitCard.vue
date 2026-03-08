<template>
  <div class="bg-white rounded-lg p-4 shadow flex items-center justify-between transition-all hover:shadow-md">
    <div class="flex-1 mr-3">
      <p class="font-semibold text-gray-800">{{ habit.nom }}</p>
      <p class="text-xs text-gray-500 truncate">{{ habit.descripcio }} • +{{ habit.recompensaXP }} XP</p>
      <p class="text-xs text-blue-600 font-semibold">{{ progress }}/{{ habit.objectiuVegades || 1 }}</p>
      <p v-if="completatAvui" class="text-xs text-green-600 font-semibold">✓ {{ $t('home.completed') }}</p>
    </div>
    <button
      class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed min-w-[110px]"
      :disabled="estaProcessant"
      @click="$emit('obrir-modal', habit)"
    >
      <span v-if="estaProcessant">{{ $t('home.loading') }}</span>
      <span v-else>{{ $t('home.progress') }}</span>
    </button>
  </div>
</template>

<script>
export default {
  name: 'HomeHabitCard',
  props: {
    habit: { type: Object, required: true },
    progress: { type: Number, default: 0 },
    completatAvui: { type: Boolean, default: false },
    estaProcessant: { type: Boolean, default: false }
  }
};
</script>
