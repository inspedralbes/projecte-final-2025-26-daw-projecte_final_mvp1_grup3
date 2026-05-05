<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
      <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">✍️</div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.details') }}</h2>
    </div>

    <div class="space-y-6">
      <div>
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.habit_name') }}</label>
        <input 
          data-testid="habit-name-input"
          :value="modelValue.nom" 
          @input="$emit('update:modelValue', { ...modelValue, nom: $event.target.value })"
          type="text" 
          :placeholder="$t('habits.placeholder_name')" 
          class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all font-bold" 
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.difficulty') }}</label>
          <select 
            :value="modelValue.dificultat" 
            @change="$emit('update:modelValue', { ...modelValue, dificultat: $event.target.value })"
            class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all appearance-none cursor-pointer font-bold"
          >
            <option value="facil">{{ $t('habits.facil') }}</option>
            <option value="media">{{ $t('habits.media') }}</option>
            <option value="dificil">{{ $t('habits.dificil') }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.daily_goal') }}</label>
          <div class="flex items-center justify-center gap-6 sm:gap-8 py-1">
            <button
              type="button"
              data-testid="habit-daily-goal-minus"
              class="daily-goal-step-btn daily-goal-step-btn--minus flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-0 bg-gray-100 text-lg font-bold text-gray-600 shadow-sm transition hover:bg-gray-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="dailyGoalValue <= 1"
              :aria-label="$t('habits.daily_goal_decrease')"
              @click="bumpObjectiu(-1)"
            >
              −
            </button>
            <span class="min-w-[3.5rem] text-center text-base font-semibold tabular-nums text-gray-600" data-testid="habit-daily-goal-display">
              0/{{ dailyGoalValue }}
            </span>
            <button
              type="button"
              data-testid="habit-daily-goal-plus"
              class="daily-goal-step-btn daily-goal-step-btn--plus flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-0 bg-indigo-600 text-lg font-bold text-white shadow-md transition hover:bg-indigo-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="dailyGoalValue >= 99"
              :aria-label="$t('habits.daily_goal_increase')"
              @click="bumpObjectiu(1)"
            >
              +
            </button>
          </div>
        </div>
      </div>

      <HabitFormCategory
        v-if="categories && categories.length"
        embedded
        :categories="categories"
        :selected-id="modelValue.categoria"
        @select="$emit('select-category', $event)"
      />

      <div v-if="colors && colors.length">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1" for="habit-color-select">{{ $t('habits.color') }}</label>
        <select
          id="habit-color-select"
          data-testid="habit-color-select"
          class="habit-color-select w-full bg-gray-50/50 border-2 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all appearance-none cursor-pointer font-bold text-gray-800"
          :value="modelValue.color"
          :style="colorSelectOutlineStyle"
          @change="onColorChange"
        >
          <option v-for="c in colorsForSelect" :key="c" :value="c">
            {{ colorOptionLabel(c) }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

<script>
import HabitFormCategory from './HabitFormCategory.vue'

var HEX_SWATCH_KEYS = {
  '#10B981': 'swatch_emerald',
  '#3B82F6': 'swatch_blue',
  '#F59E0B': 'swatch_amber',
  '#EF4444': 'swatch_red',
  '#8B5CF6': 'swatch_violet',
  '#EC4899': 'swatch_pink',
  '#06B6D4': 'swatch_cyan',
  '#1F2937': 'swatch_slate'
}

export default {
  name: 'HabitFormDetails',
  components: {
    HabitFormCategory
  },
  props: {
    modelValue: { type: Object, required: true },
    categories: { type: Array, default: function () { return []; } },
    colors: { type: Array, default: function () { return []; } }
  },
  emits: ['update:modelValue', 'select-category'],
  computed: {
    dailyGoalValue: function () {
      var v = parseInt(this.modelValue.objectiuVegades, 10)
      if (Number.isNaN(v) || v < 1) return 1
      return v
    },
    colorsForSelect: function () {
      var list = (this.colors || []).slice()
      var c = this.modelValue.color
      if (c && list.indexOf(c) < 0) {
        list.unshift(c)
      }
      return list
    },
    colorSelectOutlineStyle: function () {
      var c = this.modelValue.color || '#10B981'
      return {
        borderColor: c
      }
    }
  },
  methods: {
    bumpObjectiu: function (delta) {
      var next = this.dailyGoalValue + delta
      if (next < 1) next = 1
      if (next > 99) next = 99
      this.$emit('update:modelValue', { ...this.modelValue, objectiuVegades: next })
    },
    colorOptionLabel: function (hex) {
      var k = HEX_SWATCH_KEYS[hex]
      return k ? this.$t('habits.' + k) : hex
    },
    onColorChange: function (e) {
      this.$emit('update:modelValue', { ...this.modelValue, color: e.target.value })
    }
  }
}
</script>

<style scoped>
.habit-color-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.25rem;
  padding-right: 2.75rem;
}
</style>
