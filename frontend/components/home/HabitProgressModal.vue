<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div
      class="absolute inset-0 bg-black/60 backdrop-blur-sm"
      :class="potCompletar ? 'cursor-default' : ''"
      @click="gestionarClickBackdrop"
    ></div>

    <div class="bg-white rounded-3xl w-full max-w-md p-4 shadow-2xl relative border border-gray-100">
      <button
        v-if="!potCompletar"
        @click="tancar"
        class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors text-gray-500 hover:text-gray-700"
        title="Tancar"
      >
        <span class="text-lg font-black leading-none">...</span>
      </button>

      <div class="rounded-2xl bg-white p-3 text-gray-800">
        <div class="flex items-center gap-3">
          <div
            class="w-11 h-11 rounded-2xl shrink-0 flex items-center justify-center text-xl text-white"
            :style="{ backgroundColor: habitColor }"
          >
            {{ habitIcona }}
          </div>
          <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-black truncate leading-tight text-gray-800">{{ titolHabit }}</h2>
          </div>
        </div>

        <div class="mt-1 grid grid-cols-[1fr_auto] gap-1 items-start">
          <div class="space-y-2 text-sm">
            <div class="flex items-center gap-2">
              <span class="opacity-90">↪</span>
              <span class="font-semibold">{{ repeticioText }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="opacity-90">★</span>
              <span class="font-semibold">{{ dificultatText }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="opacity-90">♡</span>
              <span class="font-semibold">{{ prioridadText }}</span>
            </div>
          </div>

          <div class="flex items-center justify-start gap-2 -translate-y-1">
            <div class="w-20 rounded-2xl bg-amber-200 text-gray-900 px-2 py-2 text-center -rotate-12">
              <div class="flex items-center justify-center mb-1">
                <img :src="coinIcon" alt="Monedes" class="w-10 h-10 object-contain" />
              </div>
              <p class="text-xl font-black">+{{ recompensaMonedes }}</p>
            </div>
            <div class="w-20 rounded-2xl bg-sky-200 text-gray-900 px-2 py-2 text-center rotate-12">
              <div class="flex items-center justify-center mb-1">
                <img :src="xpIcon" alt="XP" class="w-10 h-10 object-contain" />
              </div>
              <p class="text-xl font-black">+{{ recompensaXp }}</p>
            </div>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-3 items-center">
          <button
            class="h-12 rounded-2xl bg-gray-200 text-gray-700 text-3xl font-bold transition-opacity"
            :class="isCompletedToday ? 'opacity-35 cursor-not-allowed' : 'hover:bg-gray-300'"
            :disabled="isCompletedToday"
            @click="restar"
          >
            -
          </button>
          <div class="h-12 rounded-2xl bg-gray-200 text-gray-800 font-black text-xl flex items-center justify-center">
            {{ progress }}/{{ objectiu }}
          </div>
          <button
            class="h-12 rounded-2xl bg-gray-200 text-gray-700 text-3xl font-bold transition-opacity"
            :class="isCompletedToday ? 'opacity-35 cursor-not-allowed' : 'hover:bg-gray-300'"
            :disabled="isCompletedToday"
            @click="sumar"
          >
            +
          </button>
        </div>
      </div>

      <div class="w-full flex flex-col gap-3 pt-3">
          <button
            data-testid="habit-progress-confirm"
            class="w-full py-3 rounded-xl font-bold text-white transition-all"
            :class="potCompletar ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
            :disabled="!potCompletar"
            @click="completar"
          >
            {{ $t('habits.complete_habit') }}
          </button>
          <button
            v-if="!potCompletar"
            class="w-full py-3 rounded-xl font-bold text-gray-600 border border-gray-200 hover:bg-gray-50"
            @click="tancar"
          >
            {{ $t('habits.back') }}
          </button>
      </div>
    </div>
  </div>
</template>

<script>
import coinIcon from "~/assets/img/Icones/Icona_Moneda.png";
import xpIcon from "~/assets/img/Icones/Icona_Experiencia.png";

export default {
  props: {
    isOpen: { type: Boolean, required: true },
    habit: { type: Object, required: false, default: null },
    progress: { type: Number, required: true, default: 0 },
    objectiu: { type: Number, required: true, default: 1 },
    unitat: { type: String, required: true, default: "vegades" },
    isCompletedToday: { type: Boolean, default: false }
  },
  computed: {
    coinIcon: function () {
      return coinIcon;
    },
    xpIcon: function () {
      return xpIcon;
    },
    titolHabit: function () {
      return this.habit && this.habit.nom ? this.habit.nom : "Hàbit";
    },
    habitIcona: function () {
      return this.habit && this.habit.icona ? this.habit.icona : "📝";
    },
    habitColor: function () {
      return this.habit && this.habit.color ? this.habit.color : "#9CA3AF";
    },
    potCompletar: function () {
      return this.progress >= this.objectiu;
    },
    dificultatText: function () {
      var d = this.habit && this.habit.dificultat ? this.habit.dificultat : "facil";
      if (d === "dificil") return "Difícil";
      if (d === "media" || d === "mitja") return "Mitjana";
      return "Fàcil";
    },
    repeticioText: function () {
      var f = this.habit && this.habit.frequenciaTipus ? this.habit.frequenciaTipus : "diaria";
      if (f === "setmanal") return "Setmanal";
      if (f === "especifics" || f === "especifica") return "Dies específics";
      return "Diari";
    },
    prioridadText: function () {
      return this.potCompletar ? "Preparat per completar" : "Prioritari";
    },
    recompensaXp: function () {
      return this.habit && this.habit.recompensaXP ? this.habit.recompensaXP : 100;
    },
    recompensaMonedes: function () {
      return this.habit && this.habit.recompensaMonedes ? this.habit.recompensaMonedes : 2;
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
    gestionarClickBackdrop: function () {
      if (this.potCompletar) {
        return;
      }
      this.tancar();
    },
    tancar: function () {
      if (this.potCompletar) {
        return;
      }
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
