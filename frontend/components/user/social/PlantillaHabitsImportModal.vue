<!--
  Modal per importar hàbits d'una plantilla (xat d'amics, clans, etc.).
-->
<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[120] p-4"
    @click.self="tancar"
  >
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
      <template v-if="exit">
        <div class="text-center py-4">
          <p class="text-lg font-medium text-gray-800">{{ $t('social.import_success') }}</p>
          <button type="button" class="mt-4 w-full py-2 bg-gray-800 text-white rounded-lg" @click="tancarExit">
            {{ $t('social.close') }}
          </button>
        </div>
      </template>
      <template v-else>
        <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $t('social.import_data') }}</h3>
        <p v-if="plantillaTitol" class="text-sm text-gray-500 mb-4">{{ plantillaTitol }}</p>

        <div v-if="carregant" class="text-center py-6 text-gray-500">
          {{ $t('home.loading') }}
        </div>

        <div v-else-if="error" class="text-center py-4">
          <p class="text-red-500 text-sm mb-4">{{ error }}</p>
          <button type="button" class="w-full py-2 bg-gray-200 rounded-lg" @click="tancar">
            {{ $t('social.close') }}
          </button>
        </div>

        <div v-else-if="habits.length === 0" class="text-center py-4 text-gray-500">
          <p>{{ $t('habits.no_habits') }}</p>
          <button type="button" class="mt-4 w-full py-2 bg-gray-200 rounded-lg" @click="tancar">
            {{ $t('social.close') }}
          </button>
        </div>

        <div v-else class="space-y-4">
          <p class="text-gray-600 text-sm">{{ $t('social.select_habit_for_template') }}</p>
          <div class="max-h-60 overflow-y-auto space-y-2">
            <button
              v-for="habit in habits"
              :key="habit.id"
              type="button"
              class="w-full p-3 rounded-lg text-left border-2 transition-colors"
              :class="selectedHabitIds.indexOf(habit.id) !== -1 ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'"
              @click="toggleHabit(habit.id)"
            >
              <span class="font-medium text-gray-800">{{ habitLabel(habit) }}</span>
            </button>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" class="flex-1 py-2 border border-gray-300 rounded-lg text-gray-700" @click="tancar">
              {{ $t('habits.cancel') }}
            </button>
            <button
              type="button"
              class="flex-1 py-2 bg-purple-500 text-white rounded-lg font-medium disabled:opacity-50"
              :disabled="selectedHabitIds.length === 0 || important"
              @click="confirmarImport"
            >
              {{ important ? $t('home.loading') : $t('social.confirm') }}
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { authFetch } from "~/composables/useApi.js";
import { mapPlantillaFromApi, mapHabitFromApi } from "~/utils/mappers/apiMappers.js";

export default {
  name: "PlantillaHabitsImportModal",
  props: {
    show: { type: Boolean, default: false },
    plantillaId: { type: Number, default: null },
    plantillaTitol: { type: String, default: "" }
  },
  emits: ["close", "imported"],
  data: function () {
    return {
      carregant: false,
      important: false,
      error: null,
      habits: [],
      selectedHabitIds: [],
      exit: false
    };
  },
  watch: {
    show: function (visible) {
      if (visible && this.plantillaId) {
        this.carregarPlantilla();
      }
      if (!visible) {
        this.reiniciar();
      }
    },
    plantillaId: function () {
      if (this.show && this.plantillaId) {
        this.carregarPlantilla();
      }
    }
  },
  methods: {
    habitLabel: function (habit) {
      return habit.titol || habit.nom || "Hàbit";
    },
    reiniciar: function () {
      this.carregant = false;
      this.important = false;
      this.error = null;
      this.habits = [];
      this.selectedHabitIds = [];
      this.exit = false;
    },
    tancar: function () {
      this.reiniciar();
      this.$emit("close");
    },
    tancarExit: function () {
      this.$emit("imported");
      this.tancar();
    },
    toggleHabit: function (id) {
      var pos = this.selectedHabitIds.indexOf(id);
      if (pos === -1) {
        this.selectedHabitIds.push(id);
      } else {
        this.selectedHabitIds.splice(pos, 1);
      }
    },
    carregarPlantilla: async function () {
      var self = this;
      if (!self.plantillaId) {
        return;
      }
      self.carregant = true;
      self.error = null;
      self.habits = [];
      self.selectedHabitIds = [];
      self.exit = false;
      try {
        var resposta = await authFetch("/api/plantilles/" + self.plantillaId);
        if (!resposta.ok) {
          throw new Error(self.$t("social.error_import"));
        }
        var json = await resposta.json();
        var dades = json.data || json;
        var plantilla = mapPlantillaFromApi(dades, mapHabitFromApi);
        self.habits = plantilla.habits || [];
        var i;
        for (i = 0; i < self.habits.length; i++) {
          self.selectedHabitIds.push(self.habits[i].id);
        }
      } catch (e) {
        self.error = e.message || self.$t("social.error_import");
      } finally {
        self.carregant = false;
      }
    },
    confirmarImport: async function () {
      var self = this;
      if (!self.plantillaId || self.selectedHabitIds.length === 0) {
        return;
      }
      self.important = true;
      self.error = null;
      try {
        var resposta = await authFetch("/api/plantilles/" + self.plantillaId + "/import-habits", {
          method: "POST",
          body: JSON.stringify({ habit_ids: self.selectedHabitIds })
        });
        var json = await resposta.json().catch(function () {
          return {};
        });
        if (!resposta.ok || !json.success) {
          throw new Error(json.message || self.$t("social.error_import"));
        }
        self.exit = true;
      } catch (e) {
        self.error = e.message || self.$t("social.error_import");
      } finally {
        self.important = false;
      }
    }
  }
};
</script>
