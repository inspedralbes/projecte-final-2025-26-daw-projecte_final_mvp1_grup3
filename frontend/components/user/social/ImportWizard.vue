<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">{{ $t('social.import_data') }}</h3>
        <button @click="close" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div v-if="step === 1" class="space-y-4">
        <p class="text-gray-600">{{ $t('social.select_import_type') }}</p>
        <div class="grid grid-cols-2 gap-4">
          <button
            @click="selectType('habit')"
            :disabled="!post?.habit"
            class="p-4 border-2 border-blue-500 bg-blue-50 rounded-lg text-center hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-8 h-8 mx-auto text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span class="font-medium text-blue-700">{{ $t('social.import_habit') }}</span>
          </button>
          <button
            @click="selectType('plantilla')"
            :disabled="!post?.plantilla"
            class="p-4 border-2 border-purple-500 bg-purple-50 rounded-lg text-center hover:bg-purple-100 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-8 h-8 mx-auto text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span class="font-medium text-purple-700">{{ $t('social.import_plantilla') }}</span>
          </button>
        </div>
      </div>

      <div v-if="step === 2 && importType === 'habit'" class="space-y-4">
        <p class="text-gray-600">{{ $t('social.select_days') }}</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="day in weekDays"
            :key="day.value"
            @click="toggleDay(day.value)"
            :class="[
              'px-3 py-2 rounded-lg text-sm font-medium',
              selectedDays.includes(day.value)
                ? 'bg-blue-500 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            {{ day.label }}
          </button>
        </div>
        <p v-if="selectedDays.length === 0" class="text-red-500 text-sm">
          {{ $t('social.select_at_least_one') }}
        </p>
        <button
          @click="confirmHabitImport"
          :disabled="selectedDays.length === 0 || loading"
          class="w-full py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 disabled:opacity-50"
        >
          {{ loading ? $t('home.loading') : $t('social.confirm') }}
        </button>
      </div>

      <div v-if="step === 2 && importType === 'plantilla'" class="space-y-4">
        <p class="text-gray-600">{{ $t('social.select_habit_for_template') }}</p>
        <div v-if="habitsLoading" class="text-center py-4 text-gray-500">
          {{ $t('home.loading') }}
        </div>
        <div v-else-if="habits.length === 0" class="text-center py-4 text-gray-500">
          {{ $t('habits.no_habits') }}
        </div>
        <div v-else class="max-h-60 overflow-y-auto space-y-2">
          <button
            v-for="habit in habits"
            :key="habit.id"
            @click="selectedHabit = habit"
            :class="[
              'w-full p-3 rounded-lg text-left border-2',
              selectedHabit?.id === habit.id
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'
            ]"
          >
            <span class="font-medium">{{ habit.titol }}</span>
          </button>
        </div>
        <button
          @click="confirmPlantillaImport"
          :disabled="!selectedHabit || loading"
          class="w-full py-2 bg-purple-500 text-white rounded-lg font-medium hover:bg-purple-600 disabled:opacity-50"
        >
          {{ loading ? $t('home.loading') : $t('social.confirm') }}
        </button>
      </div>

      <div v-if="step === 3" class="text-center py-4">
        <svg class="w-16 h-16 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <p class="text-lg font-medium text-gray-800">{{ $t('social.import_success') }}</p>
        <button
          @click="close"
          class="mt-4 px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900"
        >
          {{ $t('social.close') }}
        </button>
      </div>

      <p v-if="error" class="text-red-500 text-sm mt-2 text-center">{{ error }}</p>
    </div>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";
import { useHabitStore } from "~/stores/useHabitStore.js";

export default {
  name: "ImportWizard",
  props: {
    show: { type: Boolean, default: false },
    post: { type: Object, default: null }
  },
  emits: ["close"],
  data: function () {
    return {
      step: 1,
      importType: null,
      selectedDays: [],
      selectedHabit: null,
      habits: [],
      habitsLoading: false,
      loading: false,
      error: null,
      weekDays: [
        { value: 1, label: "Dl" },
        { value: 2, label: "Dt" },
        { value: 3, label: "Dc" },
        { value: 4, label: "Dj" },
        { value: 5, label: "Dv" },
        { value: 6, label: "Ds" },
        { value: 7, label: "Dg" }
      ]
    };
  },
  methods: {
    close: function () {
      this.reset();
      this.$emit("close");
    },
    reset: function () {
      this.step = 1;
      this.importType = null;
      this.selectedDays = [];
      this.selectedHabit = null;
      this.habits = [];
      this.loading = false;
      this.error = null;
    },
    selectType: function (type) {
      this.importType = type;
      if (type === "plantilla") {
        this.loadHabits();
      }
      this.step = 2;
    },
    loadHabits: async function () {
      this.habitsLoading = true;
      var habitStore = useHabitStore();
      await habitStore.obtenirHabitsDesDeApi();
      this.habits = habitStore.habits;
      this.habitsLoading = false;
    },
    toggleDay: function (day) {
      var index = this.selectedDays.indexOf(day);
      if (index === -1) {
        this.selectedDays.push(day);
      } else {
        this.selectedDays.splice(index, 1);
      }
    },
    confirmHabitImport: async function () {
      this.loading = true;
      this.error = null;

      var socialStore = useSocialStore();
      var result = await socialStore.importHabit(this.post.id, this.selectedDays);

      if (result && result.success) {
        this.step = 3;
      } else {
        this.error = result?.message || this.$t('social.error_import');
      }

      this.loading = false;
    },
    confirmPlantillaImport: async function () {
      this.loading = true;
      this.error = null;

      var socialStore = useSocialStore();
      var result = await socialStore.importPlantilla(this.post.id, this.selectedHabit.id);

      if (result && result.success) {
        this.step = 3;
      } else {
        this.error = result?.message || this.$t('social.error_import');
      }

      this.loading = false;
    }
  }
};
</script>
