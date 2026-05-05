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
    <select
      id="habit-category-select"
      data-testid="habit-category-select"
      class="habit-category-select w-full bg-gray-50/50 border-2 rounded-2xl font-bold text-gray-800 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all cursor-pointer border-gray-100"
      :class="
        [
          normalizedSelected === '' ? 'text-gray-500' : 'text-gray-800',
          embedded ? 'px-6 py-4' : 'px-4 py-3 sm:px-5 sm:py-3.5 text-sm sm:text-base'
        ]
      "
      :value="normalizedSelected"
      @change="onChange"
    >
      <option value="" disabled>{{ $t('habits.category_select_placeholder') }}</option>
      <option
        v-for="cat in categories"
        :key="cat.id"
        :value="String(cat.id)"
        :data-testid="'habit-category-' + cat.key"
      >
        {{ cat.icona }} {{ $t('habits.categories.' + cat.key) }}
      </option>
    </select>
  </div>
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
  computed: {
    normalizedSelected: function () {
      var s = this.selectedId;
      if (s === '' || s === null || s === undefined) {
        return '';
      }
      return String(s);
    }
  },
  methods: {
    onChange: function (e) {
      var v = e.target.value;
      if (v === '') {
        return;
      }
      var n = parseInt(v, 10);
      if (!Number.isNaN(n)) {
        this.$emit('select', n);
      }
    }
  }
};
</script>

<style scoped>
.habit-category-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.25rem;
  padding-right: 2.75rem;
}
</style>
