<template>
  <div :class="embedded ? null : 'bento-card bg-white/95 backdrop-blur-md rounded-3xl p-4 sm:p-5 shadow-xl border border-white/50'">
    <label
      for="habit-category-select"
      :class="
        embedded
          ? 'block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1'
          : 'sr-only'
      "
    >{{ $t('habits.category') }}</label>
    <button
      id="habit-category-select"
      data-testid="habit-category-select"
      type="button"
      class="w-full bg-gray-50/50 border-2 rounded-2xl font-bold text-gray-800 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all cursor-pointer border-gray-100 flex items-center justify-between"
      :class="[embedded ? 'px-6 py-4' : 'px-4 py-3 sm:px-5 sm:py-3.5 text-sm sm:text-base']"
      @click="obrirSelector"
    >
      <span :class="normalizedSelected === '' ? 'text-gray-500' : 'text-gray-800'">
        {{ etiquetaSeleccionada }}
      </span>
      <span class="text-gray-400 text-lg leading-none">⌄</span>
    </button>
  </div>

  <Teleport to="body">
    <Transition name="category-backdrop">
      <div
        v-if="selectorObert"
        class="fixed inset-0 z-[82] bg-black/40"
        @click="tancarSelector"
      ></div>
    </Transition>

    <Transition name="category-sheet">
      <div
        v-if="selectorObert"
        class="fixed left-0 right-0 bottom-0 z-[83] bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 max-h-[78vh] flex flex-col"
      >
        <div class="px-4 pt-3 pb-2 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-base font-black text-gray-800">{{ $t('habits.category_select_placeholder') }}</h3>
          <button
            type="button"
            class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 text-xl font-bold"
            @click="tancarSelector"
          >
            ×
          </button>
        </div>

        <div class="overflow-y-auto p-4 space-y-2 pb-[max(1rem,env(safe-area-inset-bottom))]">
          <button
            v-for="cat in categories"
            :key="cat.id"
            type="button"
            class="w-full flex items-center justify-between rounded-2xl border-2 px-4 py-3 text-left transition"
            :class="String(cat.id) === normalizedSelected ? 'border-green-500 bg-green-50' : 'border-gray-100 bg-white hover:border-green-200'"
            :data-testid="'habit-category-' + cat.key"
            @click="seleccionarCategoria(cat.id)"
          >
            <span class="font-semibold text-gray-800">{{ cat.icona }} {{ $t('habits.categories.' + cat.key) }}</span>
            <span v-if="String(cat.id) === normalizedSelected" class="text-green-600 font-black">✓</span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'HabitFormCategory',
  props: {
    categories: { type: Array, required: true },
    selectedId: { type: [Number, String], default: '' },
    /** Sense tarja pròpia; per usar dins HabitFormDetails */
    embedded: { type: Boolean, default: false }
  },
  emits: ['select'],
  data: function () {
    return {
      selectorObert: false
    };
  },
  computed: {
    normalizedSelected: function () {
      var s = this.selectedId;
      if (s === '' || s === null || s === undefined) {
        return '';
      }
      return String(s);
    },
    etiquetaSeleccionada: function () {
      if (this.normalizedSelected === '') {
        return this.$t('habits.category_select_placeholder');
      }
      var current = (this.categories || []).find(function (cat) {
        return String(cat.id) === this.normalizedSelected;
      }, this);
      if (!current) {
        return this.$t('habits.category_select_placeholder');
      }
      return current.icona + ' ' + this.$t('habits.categories.' + current.key);
    }
  },
  methods: {
    obrirSelector: function () {
      this.selectorObert = true;
    },
    tancarSelector: function () {
      this.selectorObert = false;
    },
    seleccionarCategoria: function (id) {
      var n = parseInt(String(id), 10);
      if (!Number.isNaN(n)) {
        this.$emit('select', n);
      }
      this.tancarSelector();
    }
  }
};
</script>

<style scoped>
.category-backdrop-enter-active,
.category-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.category-backdrop-enter-from,
.category-backdrop-leave-to {
  opacity: 0;
}

.category-sheet-enter-active,
.category-sheet-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.category-sheet-enter-from,
.category-sheet-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}
</style>
