<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancar"></div>

    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl relative">
      <button
        @click="tancar"
        class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600"
        title="Tancar"
      >
        <span class="text-2xl">×</span>
      </button>

      <div class="flex flex-col items-center gap-4">
        <h2 class="text-lg font-bold text-gray-800">
          {{ titolHabit }}
        </h2>
        <p class="text-xs text-gray-500">
          {{ $t('habits.daily_goal_label') }}: {{ objectiu }} {{ unitat }}
        </p>

        <div class="relative w-48 h-48 flex items-center justify-center">
          <svg class="absolute inset-0" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="40" stroke="#e5e7eb" stroke-width="10" fill="none" />
            <circle
              cx="50"
              cy="50"
              r="40"
              stroke="#6366f1"
              stroke-width="10"
              fill="none"
              stroke-linecap="round"
              :stroke-dasharray="circumferencia"
              :stroke-dashoffset="offset"
              transform="rotate(-90 50 50)"
            />
          </svg>
          <div class="text-center">
            <p class="text-3xl font-bold text-gray-800">{{ progress }}</p>
            <p class="text-xs text-gray-500">{{ unitat }}</p>
          </div>
        </div>

        <div class="flex items-center gap-6">
          <button
            class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 font-bold hover:bg-gray-200"
            @click="restar"
          >
            -
          </button>
          <span class="text-sm text-gray-600">{{ progress }}/{{ objectiu }}</span>
          <button
            class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold hover:bg-indigo-700"
            @click="sumar"
          >
            +
          </button>
        </div>

        <div class="w-full flex flex-col gap-3 pt-2">
          <button
            class="w-full py-3 rounded-xl font-bold text-white transition-all"
            :class="potCompletar ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
            :disabled="!potCompletar"
            @click="completar"
          >
            {{ $t('habits.complete_habit') }}
          </button>
          <button
            class="w-full py-3 rounded-xl font-bold text-gray-600 border border-gray-200 hover:bg-gray-50"
            @click="tancar"
          >
            {{ $t('habits.back') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isOpen: { type: Boolean, required: true },
    habit: { type: Object, required: false, default: null },
    progress: { type: Number, required: true, default: 0 },
    objectiu: { type: Number, required: true, default: 1 },
    unitat: { type: String, required: true, default: "vegades" }
  },
  computed: {
    titolHabit: function () {
      return this.habit && this.habit.nom ? this.habit.nom : "Hàbit";
    },
    potCompletar: function () {
      return this.progress >= this.objectiu;
    },
    circumferencia: function () {
      return 2 * Math.PI * 40;
    },
    offset: function () {
      var percent = 0;
      if (this.objectiu > 0) {
        percent = Math.min(this.progress / this.objectiu, 1);
      }
      return this.circumferencia * (1 - percent);
    }
  },
  methods: {
    tancar: function () {
      this.$emit("close");
    },
    sumar: function () {
      this.$emit("increment");
    },
    restar: function () {
      this.$emit("decrement");
    },
    completar: function () {
      if (!this.potCompletar) {
        this.$emit("invalid-complete");
        return;
      }
      this.$emit("confirm");
    }
  }
};
</script>
