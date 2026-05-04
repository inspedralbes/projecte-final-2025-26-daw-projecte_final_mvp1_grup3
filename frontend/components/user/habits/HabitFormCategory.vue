<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-xl border border-white/50">
    <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-100">
      <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📂</div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.category') }}</h2>
    </div>

    <label for="habit-category-select" class="sr-only">{{ $t('habits.category') }}</label>
    <select
      id="habit-category-select"
      data-testid="habit-category-select"
      class="habit-category-select w-full bg-gray-50/50 border-2 rounded-2xl px-6 py-4 font-bold text-gray-800 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all cursor-pointer border-gray-100"
      :class="normalizedSelected === '' ? 'text-gray-500' : 'text-gray-800'"
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
    selectedId: { type: [Number, String], default: '' }
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
