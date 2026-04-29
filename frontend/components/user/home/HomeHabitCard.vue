<template>
  <div
    class="bg-white rounded-xl shadow transition-all hover:shadow-md overflow-hidden"
    :class="climaAdvers ? 'ring-1 ring-orange-200' : ''"
  >
    <div v-if="climaAdvers" class="flex items-center gap-1.5 bg-orange-50 border-b border-orange-100 px-3 py-1">
      <span class="text-sm">🌧️</span>
      <span class="text-xs font-bold text-orange-600">Clima advers — considera alternativa interior</span>
    </div>

    <div class="p-4 flex items-center justify-between">
      <div class="flex-1 mr-3">
        <p class="font-semibold text-gray-800">{{ habit.nom }}</p>
        <p class="text-xs text-gray-500 truncate">{{ habit.descripcio }} • +{{ habit.recompensaXP }} XP</p>
        <p class="text-xs text-blue-600 font-semibold">{{ progress }}/{{ habit.objectiuVegades || 1 }}</p>
        <p v-if="completatAvui" class="text-xs text-green-600 font-semibold">✓ {{ $t('home.completed') }}</p>
      </div>
      <div class="flex flex-col gap-2">
        <button
          class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed min-w-[110px]"
          :disabled="estaProcessant"
          @click="$emit('obrir-modal', habit)"
        >
          <span v-if="estaProcessant">{{ $t('home.loading') }}</span>
          <span v-else>{{ $t('home.progress') }}</span>
        </button>
        <button
          data-testid="habit-details-button"
          class="px-3 py-2 bg-white text-indigo-600 border border-indigo-200 rounded-full hover:bg-indigo-50 transition text-xs font-bold min-w-[110px]"
          @click="$emit('obrir-detalls', habit)"
        >
          detalls
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeHabitCard',
  props: {
    habit:          { type: Object,  required: true },
    progress:       { type: Number,  default: 0 },
    completatAvui:  { type: Boolean, default: false },
    estaProcessant: { type: Boolean, default: false },
    climaAdvers:    { type: Boolean, default: false }
  }
};
</script>
