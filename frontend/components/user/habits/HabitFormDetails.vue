<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="space-y-5">
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

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
              class="daily-goal-step-btn daily-goal-step-btn--plus flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-0 bg-gray-100 text-lg font-bold text-gray-600 shadow-sm transition hover:bg-gray-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
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
        <button
          id="habit-color-select"
          data-testid="habit-color-select"
          type="button"
          class="w-full bg-gray-50/50 border-2 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all cursor-pointer font-bold text-gray-800 flex items-center justify-between"
          :style="colorSelectOutlineStyle"
          @click="obrirSelectorColor"
        >
          <span>{{ colorOptionLabel(modelValue.color) }}</span>
          <span class="text-gray-400 text-lg leading-none">⌄</span>
        </button>
      </div>
    </div>
  </div>

  <Teleport to="body">
    <Transition name="color-backdrop">
      <div
        v-if="selectorColorObert"
        class="fixed inset-0 z-[84] bg-black/40"
        @click="tancarSelectorColor"
      ></div>
    </Transition>

    <Transition name="color-sheet">
      <div
        v-if="selectorColorObert"
        class="fixed left-0 right-0 bottom-0 z-[85] bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 max-h-[70vh] flex flex-col"
      >
        <div class="px-4 pt-3 pb-2 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-base font-black text-gray-800">{{ $t('habits.color') }}</h3>
          <button
            type="button"
            class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 text-xl font-bold"
            @click="tancarSelectorColor"
          >
            ×
          </button>
        </div>

        <div class="overflow-y-auto p-4 space-y-2 pb-[max(1rem,env(safe-area-inset-bottom))]">
          <button
            v-for="c in colorsForSelect"
            :key="c"
            type="button"
            class="w-full flex items-center justify-between rounded-2xl border-2 px-4 py-3 text-left transition"
            :class="modelValue.color === c ? 'bg-emerald-50 border-emerald-500' : 'bg-white border-gray-100 hover:border-emerald-200'"
            @click="seleccionarColor(c)"
          >
            <span class="font-semibold text-gray-800">{{ colorOptionLabel(c) }}</span>
            <span class="inline-flex w-6 h-6 rounded-full border-2 border-white shadow" :style="{ backgroundColor: c }"></span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
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
  data: function () {
    return {
      selectorColorObert: false
    }
  },
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
    obrirSelectorColor: function () {
      this.selectorColorObert = true
    },
    tancarSelectorColor: function () {
      this.selectorColorObert = false
    },
    seleccionarColor: function (colorHex) {
      this.$emit('update:modelValue', { ...this.modelValue, color: colorHex })
      this.tancarSelectorColor()
    }
  }
}
</script>

<style scoped>
.color-backdrop-enter-active,
.color-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.color-backdrop-enter-from,
.color-backdrop-leave-to {
  opacity: 0;
}

.color-sheet-enter-active,
.color-sheet-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.color-sheet-enter-from,
.color-sheet-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}
</style>
