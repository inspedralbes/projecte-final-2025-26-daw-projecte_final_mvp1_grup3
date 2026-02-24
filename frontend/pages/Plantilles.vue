<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Gestió de Plantilles</h1>

      <button
        @click="obrirModalCrearPlantilla"
        class="mb-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2"
      >
        <span
          class="bg-white text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-xs"
          >+</span>
        Crear Nova Plantilla
      </button>

      <div v-if="plantillaStore.loading" class="text-center py-10">
        <p class="text-gray-500">Carregant plantilles...</p>
      </div>

      <div v-else-if="plantillaStore.error" class="text-center py-10 text-red-500">
        <p>Error: {{ plantillaStore.error }}</p>
      </div>

      <div v-else-if="plantillaStore.plantilles.length === 0" class="text-center py-10 text-gray-400">
        <p>No hi ha plantilles disponibles.</p>
        <p class="text-sm">Crea la primera plantilla!</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="plantilla in plantillaStore.plantilles"
          :key="plantilla.id"
          class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between"
        >
          <div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">{{ plantilla.titol }}</h2>
            <p class="text-sm text-gray-600 mb-4">Categoria: {{ plantilla.categoria }}</p>
            <span
              :class="{
                'bg-green-100 text-green-800': plantilla.esPublica,
                'bg-blue-100 text-blue-800': !plantilla.esPublica
              }"
              class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium"
            >
              {{ plantilla.esPublica ? 'Pública' : 'Privada' }}
            </span>
          </div>
          <div class="mt-4 flex justify-end gap-3">
            <button
              @click="editarPlantilla(plantilla.id)"
              class="text-sm text-blue-600 hover:text-blue-700 font-semibold px-3 py-1 rounded border border-blue-200 hover:border-blue-300 transition-colors"
            >
              Editar
            </button>
            <button
              @click="eliminarPlantilla(plantilla.id)"
              class="text-sm text-red-600 hover:text-red-700 font-semibold px-3 py-1 rounded border border-red-200 hover:border-red-300 transition-colors"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal para crear/editar plantilla -->
      <div
        v-if="modalVisible"
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
                <template v-if="modoEdicio">Actualitzar</template>
                <template v-else>Crear Plantilla</template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { usePlantillaStore } from "../stores/usePlantillaStore";
import { useHabitStore } from "../stores/useHabitStore";
import { useSocketConfig } from "../composables/useSocketConfig";
import { io } from "socket.io-client";

export default {
  setup: function () {
    var plantillaStore = usePlantillaStore();
    var habitStore = useHabitStore();
    return { plantillaStore: plantillaStore, habitStore: habitStore };
  },
  data: function () {
    return {
      modalVisible: false,
      modoEdicio: false,
      plantillaAEditar: null, // Used for editing, replaces plantillaSeleccionada
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
    self.carregarPlantilles();
    self.initSocket();
    self.carregarHabits();

    // The logic below is adapted from the original PlantillaCreateModal's mounted hook
    // It will be triggered when the modal becomes visible, not directly on page load
    // The actual initialization of the form will happen in obrirModalCrearPlantilla or editarPlantilla
  },
  beforeUnmount: function () {
    if (this.socket) {
      this.socket.disconnect();
    }
  },
  methods: {
    // --- Methods from Plantilles.vue ---
    carregarPlantilles: async function () {
      await this.plantillaStore.obtenirPlantillesDesDeApi();
    },
    obrirModalCrearPlantilla: function () {
      var self = this;
      self.modoEdicio = false;
      self.plantillaAEditar = null;
      // Reset form when opening for creation
      self.form.titol = "";
      self.form.categoria = "";
      self.form.esPublica = false;
      self.form.habitsSeleccionats = [];
      self.modalVisible = true;
    },
    editarPlantilla: function (id) {
      var self = this;
      var plantillaTrobada = null;
      var i;

      for (i = 0; i < self.plantillaStore.plantilles.length; i++) {
        if (self.plantillaStore.plantilles[i].id === id) {
          plantillaTrobada = self.plantillaStore.plantilles[i];
          break;
        }
      }

      self.plantillaAEditar = plantillaTrobada;
      self.modoEdicio = true;
      self.modalVisible = true;

      // Populate form for editing
      if (self.plantillaAEditar) {
        self.form.titol = self.plantillaAEditar.titol;
        self.form.categoria = self.plantillaAEditar.categoria;
        self.form.esPublica = self.plantillaAEditar.esPublica;
        // Assuming plantillaAEditar.habits_ids exists and is an array
        self.form.habitsSeleccionats = self.plantillaAEditar.habits_ids ? self.plantillaAEditar.habits_ids : [];
      }
    },
    eliminarPlantilla: function (id) {
      // Implementar lògica d'eliminació via socket
      alert("Eliminar plantilla amb ID: " + id);
      // Després d'eliminar, recarregar plantilles o actualitzar l'store
    },
    tancar: function () { // Renamed from tancarModal to match modal's original name
      this.modalVisible = false;
    },
    plantillaCreada: function () {
      this.tancar();
      this.carregarPlantilles(); // Recarregar les plantilles per veure la nova
    },
    plantillaActualitzada: function () {
      this.tancar();
      this.carregarPlantilles(); // Recarregar les plantilles per veure els canvis
    },

    // --- Methods from PlantillaCreateModal.vue (adapted) ---
    initSocket: function () {
      var self = this;
      if (self.socket) {
        return;
      }
      var socketConfig = useSocketConfig();
      var socketUrl = socketConfig.socketUrl;
      self.socket = io(socketUrl, {
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: 5,
      });

      console.log('Socket URL:', socketUrl); // Added for debugging

      self.socket.on("connect", function () {
        console.log("✅ Socket de Plantilles connectat:", self.socket.id);
      });

      self.socket.on("plantilla_action_confirmed", function (payload) {
        self.handlePlantillaFeedback(payload);
      });

      self.socket.on("disconnect", function () {
        console.log("❌ Socket de Plantilles desconnectat");
      });

      self.socket.on("error", function (error) {
        console.error("⚠️ Error en socket de Plantilles:", error);
      });
    },
    carregarHabits: async function () {
      await this.habitStore.obtenirHabitsDesDeApi();
    },
    toggleHabitSeleccionat: function (habitId) {
      var self = this;
      var pos = self.form.habitsSeleccionats.indexOf(habitId);
      if (pos === -1) {
        self.form.habitsSeleccionats.push(habitId);
      } else {
        self.form.habitsSeleccionats.splice(pos, 1);
      }
    },
    guardarPlantilla: function () {
      var self = this;
      console.log('guardarPlantilla called'); // Added for debugging
      if (!self.form.titol) {
        alert("El nom de la plantilla és obligatori.");
        return;
      }
      if (self.form.habitsSeleccionats.length === 0) {
        alert("Has de seleccionar al menys un hàbit per crear la plantilla.");
        return;
      }

      if (!self.socket) {
        alert("Socket no disponible per crear la plantilla.");
        return;
      }

      var plantillaData = {
        titol: self.form.titol,
        categoria: self.form.categoria,
        es_publica: self.form.esPublica, // Backend espera 'es_publica'
        habits_ids: self.form.habitsSeleccionats, // Backend espera 'habits_ids'
      };

      if (self.modoEdicio && self.plantillaAEditar) {
        // Lògica per actualitzar una plantilla existent
        // Cal un ID per actualitzar
        plantillaData.id = self.plantillaAEditar.id;
        self.socket.emit("plantilla_action", {
          action: "UPDATE",
          plantilla_data: plantillaData,
        });
      } else {
        // Lògica per crear una nova plantilla
        self.socket.emit("plantilla_action", {
          action: "CREATE",
          plantilla_data: plantillaData,
        });
      }
    },
    handlePlantillaFeedback: function (payload) {
      var self = this;
      if (!payload || payload.success !== true) {
        alert(
          payload.message || "Error al procesar la acción de la plantilla"
        );
        return;
      }

      if (payload.action === "CREATE") {
        self.plantillaCreada();
      } else if (payload.action === "UPDATE") {
        self.plantillaActualitzada();
      }
      self.tancar(); // Tanca el modal després de l'acció
    },
  },
};
</script>

<style scoped>
/* Estils específics de la pàgina si calen */
</style>
