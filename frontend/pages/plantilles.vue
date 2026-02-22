<template>
  <main class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
      <!-- Capçalera -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Crear Nova Plantilla</h1>
          <p class="text-gray-500">
            Selecciona els hàbits que vols incloure en aquesta plantilla.
          </p>
        </div>
        <NuxtLink
          to="/"
          class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors"
        >
          ← Tornar
        </NuxtLink>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Columna Esquerra: Configuració de la Plantilla -->
        <div class="md:col-span-1 space-y-6">
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Informació</h2>

            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                  Nom de la Plantilla
                </label>
                <input
                  v-model="nomPlantilla"
                  type="text"
                  placeholder="Ex: Rutina de Matí"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                  Categoria
                </label>
                <select
                  v-model="categoriaSeleccionada"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all appearance-none"
                >
                  <option value="">Selecciona una categoria</option>
                  <option value="salut">Salut</option>
                  <option value="estudi">Estudi</option>
                  <option value="treball">Treball</option>
                  <option value="esport">Esport</option>
                </select>
              </div>

              <div class="pt-4">
                <button
                  @click="guardarPlantilla"
                  :disabled="estaEnviant || !potGuardar"
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-700/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ estaEnviant ? "Guardant..." : "Crear Plantilla" }}
                </button>
              </div>

              <p v-if="errorMissatge" class="text-xs text-red-500 mt-2 text-center">
                {{ errorMissatge }}
              </p>
            </div>
          </div>

          <!-- Resum Selecció -->
          <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100">
            <h3 class="text-sm font-bold text-blue-800 uppercase mb-2">Resum</h3>
            <p class="text-2xl font-bold text-blue-900">
              {{ habitsSeleccionats.length }}
            </p>
            <p class="text-xs text-blue-600">Hàbits seleccionats</p>
          </div>
        </div>

        <!-- Columna Dreta: Llistat d'Hàbits -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Els teus Hàbits</h2>

            <div v-if="estaCarregant" class="text-center py-10">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
              <p class="text-gray-500">Carregant la teva llista...</p>
            </div>

            <div v-else-if="habitsDisponibles.length === 0" class="text-center py-10 bg-gray-50 rounded-xl">
              <p class="text-gray-500">No tens cap hàbit creat encara.</p>
              <NuxtLink to="/habits" class="text-blue-600 font-bold hover:underline">
                Crea un hàbit primer
              </NuxtLink>
            </div>

            <div v-else class="grid grid-cols-1 gap-4">
              <div
                v-for="hàbit in habitsDisponibles"
                :key="hàbit.id"
                @click="alternarSeleccio(hàbit.id)"
                :class="[
                  'flex items-center gap-4 p-4 rounded-xl border-2 transition-all cursor-pointer group',
                  esSeleccionat(hàbit.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-200 bg-gray-50'
                ]"
              >
                <div
                  :style="{ backgroundColor: hàbit.color || '#10B981' }"
                  class="w-12 h-12 rounded-full flex items-center justify-center text-xl text-white shadow-sm"
                >
                  {{ hàbit.icona || '📝' }}
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-800">{{ hàbit.nom }}</h3>
                  <p class="text-xs text-gray-500">{{ hàbit.frequencia }}</p>
                </div>
                <div
                  class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
                  :class="esSeleccionat(hàbit.id) ? 'bg-blue-500 border-blue-500 text-white' : 'border-gray-300'"
                >
                  <span v-if="esSeleccionat(hàbit.id)" class="text-xs">✓</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { io } from "socket.io-client";
import { useHabitStore } from "../stores/useHabitStore";

/**
 * Pàgina per crear plantilles a partir d'hàbits existents de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export default {
  /**
   * Estat local del component.
   */
  data: function () {
    return {
      socket: null,
      estaCarregant: false,
      estaEnviant: false,
      errorMissatge: "",
      nomPlantilla: "",
      categoriaSeleccionada: "",
      habitsSeleccionats: [] // Array d'IDs dels hàbits escollits
    };
  },

  /**
   * Propietats computades.
   */
  computed: {
    habitStore: function () {
      return useHabitStore();
    },
    habitsDisponibles: function () {
      return this.habitStore.habits;
    },
    potGuardar: function () {
      return (
        this.nomPlantilla.length > 0 &&
        this.categoriaSeleccionada.length > 0 &&
        this.habitsSeleccionats.length > 0
      );
    }
  },

  /**
   * Inicialització del component.
   */
  mounted: function () {
    this.inicialitzarSocket();
    this.carregarHabits();
  },

  /**
   * Neteja abans de destruir el component.
   */
  beforeUnmount: function () {
    if (this.socket) {
      this.socket.disconnect();
    }
  },

  methods: {
    /**
     * Inicialitza la conexió amb el servidor de sockets per transaccions.
     */
    inicialitzarSocket: function () {
      var self = this;
      var socketConfig = useSocketConfig();
      var urlSocket = socketConfig.socketUrl;

      if (self.socket) {
        return;
      }

      self.socket = io(urlSocket, {
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: 5
      });

      self.socket.on("connect", function () {
        console.log("✅ Socket conectat per a plantilles:", self.socket.id);
      });

      self.socket.on("template_action_confirmed", function (respostat) {
        self.gestionarFeedback(respostat);
      });

      self.socket.on("disconnect", function () {
        console.log("❌ Socket desconnectat");
      });
    },

    /**
     * Carrega els hàbits de l'usuari des de l'API Laravel.
     */
    carregarHabits: async function () {
      var self = this;
      self.estaCarregant = true;
      try {
        await self.habitStore.obtenirHabitsDesDeApi();
      } catch (error) {
        self.errorMissatge = "No s'han pogut carregar els hàbits.";
        console.error("Error carregarHabits:", error);
      } finally {
        self.estaCarregant = false;
      }
    },

    /**
     * Alterna la selecció d'un hàbit a la llista.
     */
    alternarSeleccio: function (idHabit) {
      var posicio = this.habitsSeleccionats.indexOf(idHabit);
      if (posicio === -1) {
        this.habitsSeleccionats.push(idHabit);
      } else {
        this.habitsSeleccionats.splice(posicio, 1);
      }
    },

    /**
     * Comprova si un hàbit està seleccionat.
     */
    esSeleccionat: function (idHabit) {
      var i;
      var trobat = false;
      for (i = 0; i < this.habitsSeleccionats.length; i++) {
          if (this.habitsSeleccionats[i] === idHabit) {
              trobat = true;
              break;
          }
      }
      return trobat;
    },

    /**
     * Envia la creació de la plantilla al backend via Socket.
     */
    guardarPlantilla: function () {
      var self = this;
      var dadesPlantilla;

      if (!self.potGuardar) {
        return;
      }

      if (!self.socket) {
        alert("Socket no disponible");
        return;
      }

      self.estaEnviant = true;
      self.errorMissatge = "";

      // A. Preparar l'objecte de dades
      dadesPlantilla = {
        nom: self.nomPlantilla,
        categoria: self.categoriaSeleccionada,
        es_publica: false,
        ids_habits: self.habitsSeleccionats
      };

      // B. Emetre acció al backend de Node.js
      self.socket.emit("template_action", {
        action: "CREATE",
        template_data: dadesPlantilla
      });
    },

    /**
     * Gestiona la resposta del backend.
     */
    gestionarFeedback: function (resposta) {
      var self = this;
      self.estaEnviant = false;

      if (resposta && resposta.success === true) {
        alert("Plantilla creada correctament!");
        // Redirigir o netejar
        navigateTo("/");
      } else {
        self.errorMissatge = "Error al crear la plantilla. Intenta-ho més tard.";
      }
    }
  }
};
</script>

<style scoped>
/* Estils locals per a plantilles */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 1rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
}
</style>
