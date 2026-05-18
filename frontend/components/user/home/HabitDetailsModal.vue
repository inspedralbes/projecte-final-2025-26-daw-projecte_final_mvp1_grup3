<template>
  <div v-if="isOpen" data-testid="habit-details-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-gray-100 overflow-hidden">
      <div class="px-6 border-b border-gray-100 flex flex-col items-center w-full pt-4">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full mb-4"></div>
        <h3 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-[#949494] text-center w-full mb-4">Detalls</h3>
      </div>

      <div class="px-6 py-5 space-y-4">
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-400 font-bold">Hàbit</p>
          <p class="text-base font-bold text-gray-800">{{ habit ? habit.nom : "" }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-bold">Dificultat</p>
            <p class="font-semibold text-gray-800">{{ dificultatText }}</p>
          </div>
          <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100">
            <p class="text-xs text-gray-400 uppercase font-bold">Recompensa</p>
            <p class="font-semibold text-gray-800">+{{ recompensaXp }} XP</p>
          </div>
        </div>

        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100">
          <p class="text-xs text-gray-400 uppercase font-bold">Repetició</p>
          <p class="font-semibold text-gray-800">{{ diesSetmanaText }}</p>
        </div>

        <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100">
          <p class="text-xs text-gray-400 uppercase font-bold">Objectiu manual</p>
          <p class="font-semibold text-gray-800">{{ objectiuText }}</p>
        </div>

        <div v-if="metadata && (metadata.titol || metadata.url_imatge)" data-testid="habit-details-metadata-block" class="p-3 rounded-2xl bg-blue-50 border border-blue-100">
          <p class="text-xs text-blue-500 uppercase font-bold">Bloc API</p>
          <div class="flex items-center gap-3 mt-2">
            <img v-if="metadata.url_imatge" :src="metadata.url_imatge" alt="" class="w-12 h-12 rounded-lg object-cover" />
            <div class="min-w-0">
              <p data-testid="habit-details-metadata-title" class="font-semibold text-gray-800 truncate">{{ metadata.titol || "Sense títol" }}</p>
              <p class="text-xs text-gray-500">{{ metadata.tipus_api || "manual" }}</p>
            </div>
          </div>
        </div>

        <div v-if="mostrarClima" data-testid="habit-details-weather-block" class="p-3 rounded-2xl bg-amber-50 border border-amber-100">
          <p class="text-xs text-amber-600 uppercase font-bold">Context clima (Llar)</p>
          <p class="text-sm font-semibold text-gray-800 mt-1">{{ weatherText }}</p>
        </div>

        <div class="pt-2">
          <button
            data-testid="start-focus-session-button"
            class="w-full px-4 py-3 rounded-2xl font-bold text-sm transition"
            :class="isCompletedToday ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700'"
            :disabled="isCompletedToday"
            @click="$emit('start-focus', habit)"
          >
            <span v-if="isCompletedToday">Hàbit completat avui</span>
            <span v-else>Iniciar sessió de focus</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { XP_PER_DIFICULTAT } from "~/utils/mappers/apiMappers.js";

export default {
  name: "HabitDetailsModal",
  props: {
    isOpen: { type: Boolean, default: false },
    habit: { type: Object, default: null },
    weatherContext: { type: Object, default: null },
    isCompletedToday: { type: Boolean, default: false }
  },
  computed: {
    metadata: function () {
      if (!this.habit || !this.habit.metadata) {
        return null;
      }
      return this.habit.metadata;
    },
    recompensaXp: function () {
      if (!this.habit || !this.habit.dificultat) {
        return 10;
      }
      return XP_PER_DIFICULTAT[this.habit.dificultat] || 10;
    },
    dificultatText: function () {
      if (!this.habit || !this.habit.dificultat) {
        return "Fàcil";
      }
      if (this.habit.dificultat === "media") {
        return "Mitjana";
      }
      if (this.habit.dificultat === "dificil") {
        return "Difícil";
      }
      return "Fàcil";
    },
    diesSetmanaText: function () {
      if (!this.habit || !Array.isArray(this.habit.diesSetmana) || this.habit.diesSetmana.length === 0) {
        return "Cada dia";
      }
      var noms = ["Dilluns", "Dimarts", "Dimecres", "Dijous", "Divendres", "Dissabte", "Diumenge"];
      var seleccionats = [];
      for (var i = 0; i < this.habit.diesSetmana.length; i++) {
        if (this.habit.diesSetmana[i]) {
          seleccionats.push(noms[i]);
        }
      }
      if (seleccionats.length === 0) {
        return "Sense dies específics";
      }
      return seleccionats.join(", ");
    },
    objectiuText: function () {
      if (!this.habit) {
        return "-";
      }
      var quantitat = this.habit.objectiuVegades || 1;
      var unitat = this.habit.unitat || "vegades";
      return quantitat + " " + unitat;
    },
    mostrarClima: function () {
      if (!this.habit) {
        return false;
      }
      return this.habit.categoriaId === 7;
    },
    weatherText: function () {
      if (!this.weatherContext || this.weatherContext.ok !== true) {
        return "No s'ha pogut obtenir el clima en temps real.";
      }
      if (this.weatherContext.suitable) {
        return "Condicions favorables: " + (this.weatherContext.description || "clima estable");
      }
      return "Condicions no ideals: " + (this.weatherContext.description || "clima advers");
    }
  }
};
</script>
