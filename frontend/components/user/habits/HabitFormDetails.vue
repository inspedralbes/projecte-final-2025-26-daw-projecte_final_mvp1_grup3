<!--
  Component o pagina Nuxt: HabitFormDetails.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="habit-form bento-card rounded-3xl border border-gray-100 bg-white p-4 shadow-sm">
    <div class="space-y-5">
      <div>
        <label class="habit-form-label">{{ $t('habits.habit_name') }}</label>
        <input 
          data-testid="habit-name-input"
          :value="modelValue.nom" 
          @input="$emit('update:modelValue', { ...modelValue, nom: $event.target.value })"
          type="text" 
          :placeholder="$t('habits.placeholder_name')" 
          class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all" 
        />
      </div>

      <HabitFormCategory
        v-if="categories && categories.length"
        embedded
        :categories="categories"
        :user-categories="userCategories"
        :selected-id="modelValue.categoria"
        :category-custom-label="categoryCustomLabel"
        :category-custom-icona="categoryCustomIcona"
        :selected-user-category-id="selectedUserCategoryId"
        @select="$emit('select-category', $event)"
        @select-user="$emit('select-user-category', $event)"
        @add-user-category="$emit('add-user-category', $event)"
      />

      <HabitFormPlanning
        v-if="includePlanning && isDaySelected"
        embedded
        :model-value="modelValue"
        :is-day-selected="isDaySelected"
        @update:model-value="$emit('update:modelValue', $event)"
        @toggle-day="forwardToggleDay"
      />

      <div>
        <label class="habit-form-label">{{ $t('habits.times_per_day_label') }}</label>
        <div class="flex flex-row items-center justify-center gap-4 py-2">
          <button
            type="button"
            data-testid="habit-daily-goal-minus"
            class="habit-daily-goal-step flex h-[51px] w-[51px] shrink-0 items-center justify-center border-0 bg-transparent p-0 text-[#D8D8D8] outline-none transition-colors hover:text-[#c8c8c8] focus-visible:text-[#535353] active:text-[#535353] disabled:pointer-events-none disabled:opacity-40 disabled:hover:text-[#D8D8D8]"
            :disabled="dailyGoalValue <= 1"
            :aria-label="$t('habits.daily_goal_decrease')"
            @click="bumpObjectiu(-1)"
          >
            <svg width="51" height="51" viewBox="0 0 51 51" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="0.5" y="0.5" width="50" height="50" rx="24.5" fill="#F0F0F0" stroke="currentColor" />
              <line x1="11" y1="25.9999" x2="40" y2="25.9999" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
            </svg>
          </button>
          <span
            class="habit-form-daily-goal-count min-w-[2ch] shrink-0 select-none text-center tabular-nums"
            data-testid="habit-daily-goal-display"
          >{{ dailyGoalValue }}</span>
          <button
            type="button"
            data-testid="habit-daily-goal-plus"
            class="habit-daily-goal-step flex h-[51px] w-[51px] shrink-0 items-center justify-center border-0 bg-transparent p-0 text-[#D8D8D8] outline-none transition-colors hover:text-[#c8c8c8] focus-visible:text-[#535353] active:text-[#535353] disabled:pointer-events-none disabled:opacity-40 disabled:hover:text-[#D8D8D8]"
            :disabled="dailyGoalValue >= 99"
            :aria-label="$t('habits.daily_goal_increase')"
            @click="bumpObjectiu(1)"
          >
            <svg width="51" height="51" viewBox="0 0 51 51" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="0.5" y="0.5" width="50" height="50" rx="24.5" fill="#F0F0F0" stroke="currentColor" />
              <line x1="26" y1="11.0001" x2="26" y2="40.0001" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
              <line x1="11" y1="24.9999" x2="40" y2="24.9999" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
            </svg>
          </button>
        </div>
      </div>

      <div>
        <label class="habit-form-label" for="habit-difficulty-select">{{ $t('habits.difficulty_select_label') }}</label>
        <div class="relative">
          <select
            id="habit-difficulty-select"
            :value="modelValue.dificultat"
            @change="$emit('update:modelValue', { ...modelValue, dificultat: $event.target.value })"
            class="habit-form-field-surface relative z-0 w-full cursor-pointer appearance-none border-gray-100 bg-gray-50/50 pl-6 pr-14 transition-all focus:border-green-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10"
          >
            <option value="facil">{{ $t('habits.facil') }}</option>
            <option value="media">{{ $t('habits.media') }}</option>
            <option value="dificil">{{ $t('habits.dificil') }}</option>
          </select>
          <span
            class="pointer-events-none absolute inset-y-0 right-4 z-10 flex w-[25px] items-center justify-center"
            aria-hidden="true"
          >
            <HabitFormSelectChevron />
          </span>
        </div>
      </div>

      <div>
        <label class="habit-form-label" for="habit-moment-dia-select">{{ $t('habits.moment_of_day_label') }}</label>
        <div class="relative">
          <select
            id="habit-moment-dia-select"
            :value="momentDiaModel"
            @change="$emit('update:modelValue', { ...modelValue, momentDia: $event.target.value })"
            class="habit-form-field-surface relative z-0 w-full cursor-pointer appearance-none border-gray-100 bg-gray-50/50 pl-6 pr-14 transition-all focus:border-green-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10"
          >
            <option value="tot_dia">{{ $t('habits.moment_tot_dia') }}</option>
            <option value="mati">{{ $t('habits.moment_mati') }}</option>
            <option value="tarda">{{ $t('habits.moment_tarda') }}</option>
            <option value="nit">{{ $t('habits.moment_nit') }}</option>
          </select>
          <span
            class="pointer-events-none absolute inset-y-0 right-4 z-10 flex w-[25px] items-center justify-center"
            aria-hidden="true"
          >
            <HabitFormSelectChevron />
          </span>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import HabitFormCategory from './HabitFormCategory.vue'
import HabitFormPlanning from './HabitFormPlanning.vue'
import HabitFormSelectChevron from './HabitFormSelectChevron.vue'

export default {
  name: 'HabitFormDetails',
  components: {
    HabitFormCategory,
    HabitFormPlanning,
    HabitFormSelectChevron
  },
  props: {
    modelValue: { type: Object, required: true },
    categories: { type: Array, default: function () { return []; } },
    userCategories: { type: Array, default: function () { return []; } },
    categoryCustomLabel: { type: String, default: '' },
    categoryCustomIcona: { type: String, default: '' },
    selectedUserCategoryId: { type: [Number, String], default: null },
    includePlanning: { type: Boolean, default: true },
    isDaySelected: { type: Function, default: null }
  },
  emits: ['update:modelValue', 'select-category', 'select-user-category', 'add-user-category', 'toggle-day'],
  computed: {
    dailyGoalValue: function () {
      var v = parseInt(this.modelValue.objectiuVegades, 10)
      if (Number.isNaN(v) || v < 1) return 1
      return v
    },
    momentDiaModel: function () {
      var v = this.modelValue.momentDia || this.modelValue.moment_dia
      if (v === 'mati' || v === 'tarda' || v === 'nit' || v === 'tot_dia') {
        return v
      }
      return 'tot_dia'
    }
  },
  methods: {
    forwardToggleDay: function (index) {
      this.$emit('toggle-day', index)
    },
    bumpObjectiu: function (delta) {
      var next = this.dailyGoalValue + delta
      if (next < 1) next = 1
      if (next > 99) next = 99
      this.$emit('update:modelValue', { ...this.modelValue, objectiuVegades: next })
    }
  }
}
</script>

<style scoped>
/* Estils del formulari: classes .habit-form* i Tailwind a main.css / capa components */
</style>
