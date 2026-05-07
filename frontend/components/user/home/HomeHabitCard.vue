<template>
  <div
    class="bg-white rounded-xl shadow transition-all hover:shadow-md overflow-hidden"
    :data-testid="'home-habit-card-' + habit.id"
    :class="climaAdvers ? 'ring-1 ring-orange-200' : ''"
  >
    <div v-if="climaAdvers" class="flex items-center gap-1.5 bg-orange-50 border-b border-orange-100 px-3 py-1">
      <span class="text-sm">🌧️</span>
      <span class="text-xs font-bold text-orange-600">Clima advers — considera alternativa interior</span>
    </div>

    <div class="p-3 lg:p-4 flex items-center gap-3">
      <!-- Color indicator -->
      <div
        class="w-8 h-8 lg:w-10 lg:h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm lg:text-base"
        :class="completatAvui ? 'opacity-50' : ''"
        :style="{ backgroundColor: colorIndicador }"
      >
        <span aria-hidden="true">{{ iconaCategoria }}</span>
      </div>

      <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-800 text-sm lg:text-base">{{ habit.nom }}</p>
        <p class="text-xs text-gray-500 truncate hidden lg:block">{{ habit.descripcio }} • +{{ habit.recompensaXP }} XP</p>
        <p class="text-xs text-blue-600 font-semibold">{{ progress }}/{{ habit.objectiuVegades || 1 }}
          <span v-if="completatAvui" class="text-green-600 ml-1">✓ {{ $t('home.completed') }}</span>
        </p>
      </div>

      <div class="flex flex-col gap-1.5 lg:gap-2 flex-shrink-0">
        <button
          class="px-2 py-1.5 lg:px-3 lg:py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed min-w-[80px] lg:min-w-[110px]"
          :disabled="estaProcessant"
          @click="$emit('obrir-modal', habit)"
        >
          <span v-if="estaProcessant">{{ $t('home.loading') }}</span>
          <span v-else>{{ $t('home.progress') }}</span>
        </button>
        <button
          data-testid="habit-details-button"
          class="px-2 py-1.5 lg:px-3 lg:py-2 bg-white text-indigo-600 border border-indigo-200 rounded-full hover:bg-indigo-50 transition text-xs font-bold min-w-[80px] lg:min-w-[110px]"
          @click="$emit('obrir-detalls', habit)"
        >
          detalls
        </button>
      </div>
    </div>
  </div>
</template>

<script>
var CATEGORY_COLORS = {
  1: '#4ade80', // verd (exercici / activitat física)
  2: '#60a5fa', // blau (salut / beure aigua)
  3: '#f97316', // taronja (productivitat)
  4: '#a78bfa', // lila (creativitat)
  5: '#f43f5e', // vermell (social)
  6: '#facc15', // groc (ment / meditació)
  7: '#34d399', // verd menta (exterior)
  8: '#fb923c'  // taronja clar (esport)
};

var CATEGORY_ICONS = {
  1: '🏃',
  2: '💧',
  3: '📚',
  4: '🎨',
  5: '💬',
  6: '🧘',
  7: '🌳',
  8: '⚽'
};

export default {
  name: 'HomeHabitCard',
  props: {
    habit:          { type: Object,  required: true },
    progress:       { type: Number,  default: 0 },
    completatAvui:  { type: Boolean, default: false },
    estaProcessant: { type: Boolean, default: false },
    climaAdvers:    { type: Boolean, default: false }
  },
  computed: {
    colorIndicador: function () {
      var catId = this.habit.categoriaId || this.habit.categoria_id;
      return CATEGORY_COLORS[catId] || '#94a3b8';
    },
    iconaCategoria: function () {
      var catId = this.habit.categoriaId || this.habit.categoria_id;
      return CATEGORY_ICONS[catId] || '✅';
    }
  }
};
</script>
