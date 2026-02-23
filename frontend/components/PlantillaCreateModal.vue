<template>
  <div
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-50"
    @click.self="tancar"
  >
    <div
      class="relative bg-white rounded-2xl shadow-xl p-8 m-4 max-w-2xl w-full"
    >
      <button
        @click="tancar"
        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold"
      >
        &times;
      </button>

      <h2 class="text-2xl font-bold text-gray-800 mb-6">
        {{ modoEdicio ? "Editar Plantilla" : "Crear Nova Plantilla" }}
      </h2>

      <div class="space-y-6">
        <!-- Nom de la Plantilla -->
        <div>
          <label
            class="block text-sm font-medium text-gray-700 mb-2"
            for="titol"
            >Nom de la Plantilla</label
          >
          <input
            id="titol"
            v-model="form.titol"
            type="text"
            placeholder="Nom de la plantilla"
            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
          />
        </div>

        <!-- Categoria -->
        <div>
          <label
            class="block text-sm font-medium text-gray-700 mb-2"
            for="categoria"
            >Categoria</label
          >
          <input
            id="categoria"
            v-model="form.categoria"
            type="text"
            placeholder="Ex: Productivitat, Salut, Esport..."
            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
          />
        </div>

        <!-- És Pública? -->
        <div class="flex items-center">
          <input
            id="esPublica"
            v-model="form.esPublica"
            type="checkbox"
            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
          />
          <label for="esPublica" class="ml-2 block text-sm text-gray-900"
            >Plantilla pública</label
          >
        </div>

        <!-- Selecció d'Hàbits -->
        <div>
          <h3 class="text-lg font-bold text-gray-800 mb-4">Selecciona Hàbits</h3>
          <div v-if="habitStore.loading" class="text-center py-4">
            <p class="text-gray-500">Carregant hàbits...</p>
          </div>
          <div
            v-else-if="habitStore.error"
            class="text-center py-4 text-red-500"
          >
            <p>Error: {{ habitStore.error }}</p>
          </div>
          <div
            v-else-if="habitStore.habits.length === 0"
            class="text-center py-4 text-gray-400"
          >
            <p>No hi ha hàbits disponibles per seleccionar.</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto pr-2">
            <div
              v-for="habit in habitStore.habits"
              :key="habit.id"
              class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer"
              @click="toggleHabitSeleccionat(habit.id)"
            >
              <input
                type="checkbox"
                :checked="form.habitsSeleccionats.indexOf(habit.id) !== -1"
                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mr-3"
              />
              <div
                :style="{ backgroundColor: habit.color || '#10B981' }"
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm text-white mr-3"
              >
                {{ habit.icona }}
              </div>
              <span class="text-sm font-medium text-gray-700">{{ habit.nom }}</span>
            </div>
          </div>
        </div>

        <!-- Botons d'Acció -->
        <div class="flex justify-end gap-3 mt-8">
          <button
            @click="tancar"
            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors"
          >
            Cancel·lar
          </button>
          <button
            @click="guardarPlantilla"
            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg transition-all transform active:scale-95"
          >
            {{ modoEdicio ? "Actualitzar" : "Crear Plantilla" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHabitStore } from "../stores/useHabitStore";
import { useSocketConfig } from "../composables/useSocketConfig";
import { io } from "socket.io-client";

export default {
  props: {
    modoEdicio: {
      type: Boolean,
      default: false,
    },
    plantillaAEditar: {
      type: Object,
      default: null,
    },
  },
  setup: function () {
    var habitStore = useHabitStore();
    return { habitStore: habitStore };
  },
  data: function () {
    return {
      socket: null,
      form: {
        titol: "",
        categoria: "",
        esPublica: false,
        habitsSeleccionats: [], // Array de IDs d'hàbits seleccionats
      },
    };
  },
  mounted: function () {
    var self = this;
    self.initSocket();
    self.carregarHabits();

    if (self.modoEdicio && self.plantillaAEditar) {
      self.form.titol = self.plantillaAEditar.titol;
      self.form.categoria = self.plantillaAEditar.categoria;
      self.form.esPublica = self.plantillaAEditar.esPublica;
      // TODO: Carregar habits seleccionats de la plantillaAEditar
    }
  },
  beforeUnmount: function () {
    if (this.socket) {
      this.socket.disconnect();
    }
  },
  methods: {
    tancar: function () {
      this.$emit("tancar");
    },
    initSocket: function () {
      if (this.socket) {
        return;
      }
      var socketConfig = useSocketConfig();
      var socketUrl = socketConfig.socketUrl;
      this.socket = io(socketUrl, {
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: 5,
      });

      console.log('Socket URL:', socketUrl); // Added for debugging

      var self = this;

      this.socket.on("connect", function () {
        console.log("✅ Socket de Plantilles connectat:", self.socket.id);
      });

      this.socket.on("plantilla_action_confirmed", function (payload) {
        self.handlePlantillaFeedback(payload);
      });

      this.socket.on("disconnect", function () {
        console.log("❌ Socket de Plantilles desconnectat");
      });

      this.socket.on("error", function (error) {
        console.error("⚠️ Error en socket de Plantilles:", error);
      });
    },
    carregarHabits: async function () {
      await this.habitStore.obtenirHabitsDesDeApi();
    },
    toggleHabitSeleccionat: function (habitId) {
      var pos = this.form.habitsSeleccionats.indexOf(habitId);
      if (pos === -1) {
        this.form.habitsSeleccionats.push(habitId);
      } else {
        this.form.habitsSeleccionats.splice(pos, 1);
      }
    },
    guardarPlantilla: function () {
      console.log('guardarPlantilla called'); // Added for debugging
      if (!this.form.titol) {
        alert("El nom de la plantilla és obligatori.");
        return;
      }
      if (this.form.habitsSeleccionats.length === 0) {
        alert("Has de seleccionar al menys un hàbit per crear la plantilla.");
        return;
      }

      if (!this.socket) {
        alert("Socket no disponible per crear la plantilla.");
        return;
      }

      var plantillaData = {
        titol: this.form.titol,
        categoria: this.form.categoria,
        es_publica: this.form.esPublica, // Backend espera 'es_publica'
        habits_ids: this.form.habitsSeleccionats, // Backend espera 'habits_ids'
      };

      if (this.modoEdicio && this.plantillaAEditar) {
        // Lògica per actualitzar una plantilla existent
        // Cal un ID per actualitzar
        plantillaData.id = this.plantillaAEditar.id;
        this.socket.emit("plantilla_action", {
          action: "UPDATE",
          plantilla_data: plantillaData,
        });
      } else {
        // Lògica per crear una nova plantilla
        this.socket.emit("plantilla_action", {
          action: "CREATE",
          plantilla_data: plantillaData,
        });
      }
    },
    handlePlantillaFeedback: function (payload) {
      if (!payload || payload.success !== true) {
        alert(
          payload.message || "Error al procesar la acción de la plantilla"
        );
        return;
      }

      if (payload.action === "CREATE") {
        this.$emit("plantillaCreada");
      } else if (payload.action === "UPDATE") {
        this.$emit("plantillaActualitzada");
      }
      this.tancar(); // Tanca el modal després de l'acció
    },
  },
};
</script>

<style scoped>
/* Estils específics del modal si calen */
</style>
