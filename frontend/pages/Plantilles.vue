<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Gestió de Plantilles</h1>

      <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <!-- Dropdown per filtrar plantilles -->
        <div class="mb-4 md:mb-0">
          <label for="filterTemplates" class="sr-only">Filtrar Plantilles</label>
          <select
            id="filterTemplates"
            v-model="selectedFilter"
            class="block w-full md:w-auto p-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
          >
            <option value="all">Totes les Plantilles</option>
            <option value="my">Les Meves Plantilles</option>
          </select>
        </div>

        <button
          @click="obrirModalCrearPlantilla"
          class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2"
        >
          <span
            class="bg-white text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-xs"
            >+</span>
          Crear Nova Plantilla
        </button>
      </div>

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

      <!-- Modal per crear/editar plantilla -->
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
import { useGameStore } from "../stores/gameStore"; // Import useGameStore
import { useSocketConfig } from "../composables/useSocketConfig";
import { io } from "socket.io-client";
import { watch } from 'vue'; // Import watch from vue

export default {
  // Configuració inicial dels 'stores' de Pinia per a la gestió de l'estat.
  setup: function () {
    var plantillaStore = usePlantillaStore();
    var habitStore = useHabitStore();
    var gameStore = useGameStore(); // Initialize useGameStore

    return { plantillaStore: plantillaStore, habitStore: habitStore, gameStore: gameStore };
  },
  // Dades reactives del component.
  data: function () {
    return {
      modalVisible: false, // Controla la visibilitat del modal de creació/edició.
      modoEdicio: false, // Indica si el modal està en mode edició o creació.
      plantillaAEditar: null, // Objecte de plantilla a editar, si n'hi ha.
      socket: null, // Instància del socket per a comunicació en temps real.
      selectedFilter: 'all', // New reactive property for the filter dropdown
      form: {
        titol: "",
        categoria: "",
        esPublica: false,
        habitsSeleccionats: [], // Array d'IDs d'hàbits seleccionats per a la plantilla.
      },
    };
  },
  // Hook de cicle de vida: s'executa quan el component és muntat.
  mounted: function () {
    var self = this;
    // A. Carregar les plantilles existents des de l'API.
    self.carregarPlantilles();
    // Set default userId for now as there's no authentication yet
    self.gameStore.setUserId(1); // Set userId to 1
    // B. Inicialitzar la connexió del socket.
    self.initSocket();
    // C. Carregar els hàbits disponibles per a la selecció.
    self.carregarHabits();

    // Watch for changes in selectedFilter and re-carregarPlantilles
    this.$watch('selectedFilter', function (newFilter, oldFilter) {
      if (newFilter !== oldFilter) {
        self.carregarPlantilles();
      }
    });

    // Watch for changes in gameStore.userId and re-carregarPlantilles
    this.$watch(function () {
      return this.gameStore.userId;
    }, function (newUserId, oldUserId) {
      // A. Comprovar si l'ID d'usuari ha canviat
      if (newUserId !== oldUserId) {
        console.log("gameStore.userId ha canviat, recarregant plantilles. Nou userId:", newUserId);
        self.carregarPlantilles();
      }
    });
  },
  // Hook de cicle de vida: s'executa abans que el component sigui desmuntat.
  beforeUnmount: function () {
    // Desconnectar el socket si està actiu per evitar pèrdues de memòria.
    if (this.socket) {
      this.socket.disconnect();
    }
  },
  // Mètodes del component.
  methods: {
    // --- Mètodes de la vista principal de Plantilles ---

    /**
     * Carrega les plantilles des de l'API a través del 'plantillaStore'.
     * @returns {Promise<void>}
     */
    carregarPlantilles: async function () {
      var filter = this.selectedFilter;
      var userId = this.gameStore.userId; // Get userId from gameStore
      console.log("Fetching plantilles with filter:", filter, "and userId:", userId); // Debug log
      await this.plantillaStore.obtenirPlantillesDesDeApi(filter, userId);
    },

    /**
     * Obre el modal en mode de creació de plantilla i reinicia el formulari.
     */
    obrirModalCrearPlantilla: function () {
      var self = this;
      self.modoEdicio = false;
      self.plantillaAEditar = null;
      // Reiniciar el formulari per a una nova creació.
      self.form.titol = "";
      self.form.categoria = "";
      self.form.esPublica = false;
      self.form.habitsSeleccionats = [];
      self.modalVisible = true;
    },

    /**
     * Obre el modal en mode d'edició per a una plantilla específica.
     * @param {number} id - L'ID de la plantilla a editar.
     */
    editarPlantilla: function (id) {
      var self = this;
      var plantillaTrobada = null;
      var i;

      // Cercar la plantilla per ID a l'emmagatzematge.
      for (i = 0; i < self.plantillaStore.plantilles.length; i++) {
        if (self.plantillaStore.plantilles[i].id === id) {
          plantillaTrobada = self.plantillaStore.plantilles[i];
          break;
        }
      }

      self.plantillaAEditar = plantillaTrobada;
      self.modoEdicio = true;
      self.modalVisible = true;

      // A. Omplir el formulari amb les dades de la plantilla per editar.
      if (self.plantillaAEditar) {
        self.form.titol = self.plantillaAEditar.titol;
        self.form.categoria = self.plantillaAEditar.categoria;
        self.form.esPublica = self.plantillaAEditar.esPublica;
        // S'assumeix que 'plantillaAEditar.habits_ids' existeix i és un array.
        self.form.habitsSeleccionats = self.plantillaAEditar.habits_ids ? self.plantillaAEditar.habits_ids : [];
      }
    },

    /**
     * Gestiona l'eliminació d'una plantilla. (Lògica pendent d'implementació via socket)
     * @param {number} id - L'ID de la plantilla a eliminar.
     */
    eliminarPlantilla: function (id) {
      // TODO: Implementar la lògica d'eliminació via socket.
      alert("Eliminar plantilla amb ID: " + id);
      // Després d'eliminar, recarregar les plantilles o actualitzar l'store.
    },

    /**
     * Tanca el modal de creació/edició de plantilles.
     */
    tancar: function () {
      this.modalVisible = false;
    },

    /**
     * S'executa després que una plantilla s'hagi creat amb èxit.
     * Tanca el modal i recarrega la llista de plantilles.
     */
    plantillaCreada: function () {
      this.tancar();
      this.carregarPlantilles(); // Recarregar les plantilles per veure la nova.
    },

    /**
     * S'executa després que una plantilla s'hagi actualitzat amb èxit.
     * Tanca el modal i recarrega la llista de plantilles.
     */
    plantillaActualitzada: function () {
      this.tancar();
      this.carregarPlantilles(); // Recarregar les plantilles per veure els canvis.
    },

    // --- Mètodes del modal de creació/edició (adaptats) ---

    /**
     * Inicialitza la connexió amb el servidor de sockets.
     * Si ja hi ha una connexió, no fa res.
     */
    initSocket: function () {
      var self = this;
      // No fer res si el socket ja està inicialitzat.
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

      // console.log('Socket URL:', socketUrl); // Comentari de depuració, es pot eliminar o comentar.

      // Gestió d'esdeveniments del socket.
      self.socket.on("connect", function () {
        console.log("Socket de Plantilles connectat:", self.socket.id);
      });

      self.socket.on("plantilla_action_confirmed", function (payload) {
        self.handlePlantillaFeedback(payload);
      });

      self.socket.on("disconnect", function () {
        console.log("Socket de Plantilles desconnectat");
      });

      self.socket.on("error", function (error) {
        console.error("Error en socket de Plantilles:", error);
      });
    },

    /**
     * Carrega els hàbits disponibles des de l'API a través del 'habitStore'.
     * @returns {Promise<void>}
     */
    carregarHabits: async function () {
      await this.habitStore.obtenirHabitsDesDeApi();
    },

    /**
     * Afegeix o treu un hàbit de la llista de seleccionats per a la plantilla.
     * @param {number} habitId - L'ID de l'hàbit a alternar.
     */
    toggleHabitSeleccionat: function (habitId) {
      var self = this;
      var pos = self.form.habitsSeleccionats.indexOf(habitId);
      // A. Si l'hàbit no està seleccionat, afegir-lo.
      if (pos === -1) {
        self.form.habitsSeleccionats.push(habitId);
      } else {
        // B. Si l'hàbit ja està seleccionat, treure'l.
        self.form.habitsSeleccionats.splice(pos, 1);
      }
    },

    /**
     * Guarda la plantilla (creació o actualització) enviant les dades via socket.
     */
    guardarPlantilla: function () {
      var self = this;
      // console.log('guardarPlantilla called'); // Comentari de depuració, es pot eliminar o comentar.

      // A. Validacions del formulari.
      if (!self.form.titol) {
        alert("El nom de la plantilla és obligatori.");
        return;
      }
      if (self.form.habitsSeleccionats.length === 0) {
        alert("Has de seleccionar al menys un hàbit per crear la plantilla.");
        return;
      }

      // B. Comprovar que el socket estigui disponible.
      if (!self.socket) {
        alert("Socket no disponible per crear la plantilla.");
        return;
      }

      // C. Preparar les dades de la plantilla per enviar.
      var plantillaData = {
        titol: self.form.titol,
        categoria: self.form.categoria,
        es_publica: self.form.esPublica, // El backend espera 'es_publica'.
        habits_ids: self.form.habitsSeleccionats, // El backend espera 'habits_ids'.
      };

      // D. Determinar si és una creació o una actualització.
      if (self.modoEdicio && self.plantillaAEditar) {
        // Lògica per actualitzar una plantilla existent.
        // Afegir l'ID per a l'actualització.
        plantillaData.id = self.plantillaAEditar.id;
        self.socket.emit("plantilla_action", {
          action: "UPDATE",
          plantilla_data: plantillaData,
        });
      } else {
        // Lògica per crear una nova plantilla.
        self.socket.emit("plantilla_action", {
          action: "CREATE",
          plantilla_data: plantillaData,
        });
      }
    },

    /**
     * Gestiona el feedback rebut del servidor de sockets després d'una acció de plantilla.
     * @param {object} payload - Les dades de resposta del servidor.
     */
    handlePlantillaFeedback: function (payload) {
      var self = this;
      // A. Comprovar si l'acció ha estat exitosa.
      if (!payload || payload.success !== true) {
        alert(
          payload.message || "Error al processar la acción de la plantilla"
        );
        return;
      }

      // B. Executar la funció corresponent segons l'acció realitzada.
      if (payload.action === "CREATE") {
        self.plantillaCreada();
      } else if (payload.action === "UPDATE") {
        self.plantillaActualitzada();
      }
      // C. Tancar el modal un cop finalitzada l'acció amb èxit.
      self.tancar();
    },
  },
};
</script>

<style scoped>
/* Estils específics de la pàgina si calen */
</style>
