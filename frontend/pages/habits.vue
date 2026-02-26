<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Crear Hàbit</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Esquerra: Seccions del formulari -->
        <div class="lg:col-span-2 space-y-6">
          <!-- 1. Detalls de l'Hàbit -->
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
          >
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-green-100 p-2 rounded-lg">
                <span class="text-xl">Detalls</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">
                Detalls de l'Hàbit
              </h2>
            </div>

            <div class="space-y-4">
              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >Nom de l'hàbit</label
                >
                <input
                  v-model="formulari.nom"
                  type="text"
                  placeholder="Ex: Beure 2L d'aigua, Llegir 30 min..."
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
                />
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >Motivació (Opcional)</label
                >
                <textarea
                  v-model="formulari.motivacio"
                  placeholder="Per què vols començar aquest hàbit?"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all resize-none h-24"
                ></textarea>
              </div>

              <!-- Sección de Icona Ràpida eliminada para automatización -->
            </div>
          </div>

          <!-- 2. Categoria -->
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
          >
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-orange-100 p-2 rounded-lg">
                <span class="text-xl">Categoria</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">Categoria</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <button
                v-for="cat in categories"
                :key="cat.id"
                type="button"
                @click="seleccionarCategoria(cat.id)"
                class="p-4 rounded-xl flex flex-col items-center justify-center gap-2 transition-all"
                :class="obtenirClasseCategoria(cat.id)"
              >
                <span class="text-2xl">{{ cat.icona }}</span>
                <span class="text-sm font-medium text-gray-700">{{
                  cat.nom
                }}</span>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 3. Planificació -->
            <div
              class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
            >
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                  <span class="text-xl">Planificació</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Planificació</h2>
              </div>

              <div class="space-y-4">
                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >Freqüència</label
                  >
                  <div class="flex bg-gray-100 rounded-lg p-1">
                    <button
                      v-for="freq in frequencies"
                      :key="freq"
                      type="button"
                      @click="formulari.frequencia = freq"
                      class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all"
                      :class="obtenirClasseFrequencia(freq)"
                    >
                      {{ freq }}
                    </button>
                  </div>
                </div>

                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >Dies Objectiu</label
                  >
                  <div class="flex justify-between">
                    <button
                      v-for="(dia, index) in diesSetmana"
                      :key="dia"
                      type="button"
                      @click="alternarDia(index)"
                      class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors"
                      :class="obtenirClasseDia(index)"
                    >
                      {{ dia }}
                    </button>
                  </div>
                </div>

                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >Recordatori</label
                  >
                  <input
                    v-model="formulari.recordatori"
                    type="time"
                    class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
              </div>
            </div>

            <!-- 4. Personalitzar -->
            <div
              class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
            >
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-purple-100 p-2 rounded-lg">
                  <span class="text-xl">Estil</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Personalitzar</h2>
              </div>

              <p class="text-sm text-gray-500 mb-4">
                Tria l'estil visual del teu hàbit.
              </p>

              <div class="flex gap-3 mb-6">
                <button
                  v-for="color in colors"
                  :key="color"
                  type="button"
                  @click="formulari.color = color"
                  :style="{ backgroundColor: color }"
                  class="w-10 h-10 rounded-full transition-transform hover:scale-110 focus:outline-none ring-2 ring-offset-2"
                  :class="obtenirClasseColor(color)"
                ></button>
              </div>

              <!-- Vista prèvia -->
              <div
                class="bg-gray-50 rounded-xl p-4 flex items-center gap-4 opacity-75"
              >
                <div
                  :style="{
                    backgroundColor: formulari.color || '#10B981',
                    color: 'white',
                  }"
                  class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                >
                  {{ formulari.icona }}
                </div>
                <div class="h-2 bg-gray-200 rounded w-2/3"></div>
              </div>
            </div>
          </div>

          <!-- Botó Enviar -->
          <button
            @click="crearHabit"
            class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-700/20 transition-all transform active:scale-95 flex items-center justify-center gap-2"
          >
            <span
              class="bg-white text-green-700 rounded-full w-5 h-5 flex items-center justify-center text-xs"
              >V</span
            >
            Crear Hàbit
          </button>
        </div>

        <!-- Dreta: Llista dels meus hàbits -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full"
          >
            <div class="flex items-center gap-3 mb-6">
              <span class="text-xl text-gray-400">Hàbits</span>
              <h2 class="text-lg font-bold text-gray-800">Els Meus Hàbits</h2>
            </div>

            <div
              v-if="habitStore.habits.length === 0"
              class="text-center py-10 text-gray-400"
            >
              <p>Encara no tens hàbits.</p>
              <p class="text-sm">Afegeix-ne un de nou!</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="hàbit in habitStore.habits"
                :key="hàbit.id"
                class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100 group cursor-pointer"
                @click="obrirModalEdicio(hàbit)"
              >
                <div
                  :style="{ backgroundColor: hàbit.color || '#10B981' }"
                  class="w-12 h-12 rounded-full flex items-center justify-center text-xl text-white shadow-sm"
                >
                  {{ hàbit.icona }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-gray-800 truncate">
                    {{ hàbit.nom }}
                  </h3>
                  <p class="text-xs text-gray-500">
                    {{ obtenirNomCategoria(hàbit.categoriaId) }}
                    <span v-if="hàbit.recordatori"
                      >• {{ hàbit.recordatori }}</span
                    >
                  </p>
                </div>
                <button
                  class="text-xs text-red-600 hover:text-red-700 font-semibold px-2 py-1 rounded border border-red-200 hover:border-red-300 transition-colors"
                  @click.stop="eliminarHabit(hàbit.id)"
                >
                  Borrar
                </button>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
              <button
                class="text-sm text-gray-400 hover:text-green-600 transition-colors border-dashed border border-gray-300 rounded-full px-4 py-2 w-full"
              >
                + Hàbit ràpid
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Habit Modal -->
    <div
      v-if="esObertModalEdicio"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="tancarModalEdicio"
      ></div>

      <div
        class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl relative animate-in fade-in zoom-in duration-200"
      >
        <div
          class="p-6 border-b border-gray-100 flex items-center justify-between"
        >
          <div class="flex items-center gap-3">
            <div
              :style="{ backgroundColor: formulariEdicio.color }"
              class="w-10 h-10 rounded-xl flex items-center justify-center text-xl text-white"
            >
              {{ formulariEdicio.icona }}
            </div>
            <h2 class="text-xl font-bold text-gray-800">Editar Hàbit</h2>
          </div>
          <button
            @click="tancarModalEdicio"
            class="text-gray-400 hover:text-gray-600"
          >
            <span class="text-2xl">×</span>
          </button>
        </div>

        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
          <!-- Name -->
          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >Nom de l'hàbit</label
            >
            <input
              v-model="formulariEdicio.nom"
              type="text"
              class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>

          <!-- Icon Selection eliminado para automatización -->

          <!-- Category -->
          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >Categoria</label
            >
            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="cat in categories"
                :key="cat.id"
                @click="seleccionarCategoriaEdicio(cat.id)"
                class="p-3 rounded-xl flex items-center gap-3 transition-all"
                :class="obtenirClasseCategoriaEdicio(cat.id)"
              >
                <span>{{ cat.icona }}</span>
                <span class="text-sm font-medium">{{ cat.nom }}</span>
              </button>
            </div>
          </div>

          <!-- Frequency & Days -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                >Freqüència</label
              >
              <select
                v-model="formulariEdicio.frequencia"
                class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm"
              >
                <option v-for="freq in frequencies" :key="freq">
                  {{ freq }}
                </option>
              </select>
            </div>
            <div>
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                >Recordatori</label
              >
              <input
                v-model="formulariEdicio.recordatori"
                type="time"
                class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm"
              />
            </div>
          </div>

          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >Dies Objectiu</label
            >
            <div class="flex justify-between">
              <button
                v-for="(dia, index) in diesSetmana"
                :key="dia"
                @click="alternarDiaEdicio(index)"
                class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors"
                :class="obtenirClasseDiaEdicio(index)"
              >
                {{ dia }}
              </button>
            </div>
          </div>

          <!-- Color Selection -->
          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >Color</label
            >
            <div class="flex gap-3">
              <button
                v-for="color in colors"
                :key="color"
                @click="formulariEdicio.color = color"
                :style="{ backgroundColor: color }"
                class="w-8 h-8 rounded-full transition-transform hover:scale-110 ring-offset-2"
                :class="obtenirClasseColorEdicio(color)"
              ></button>
            </div>
          </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex gap-3">
          <button
            @click="tancarModalEdicio"
            class="flex-1 px-4 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition-colors"
          >
            Cancel·lar
          </button>
          <button
            @click="actualitzarHabit"
            class="flex-1 px-4 py-3 bg-green-700 text-white font-bold rounded-xl hover:bg-green-800 shadow-lg shadow-green-700/20 transition-all"
          >
            Guardar Canvis
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHabitStore } from "../stores/useHabitStore";

/**
 * Pàgina de gestió d'hàbits. Permet crear, visualitzar i eliminar hàbits.
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
      errorMissatge: "",
      formulari: {
        nom: "",
        motivacio: "",
        icona: "HAB",
        categoria: "",
        frequencia: "Diari",
        recordatori: "08:00",
        diesSeleccionats: [0, 1, 2, 3, 4], // Dilluns a Divendres per defecte
        color: "#10B981",
      },
      esObertModalEdicio: false,
      idHabitEdicio: null,
      formulariEdicio: {
        nom: "",
        motivacio: "",
        icona: "HAB",
        categoria: "",
        frequencia: "Diari",
        recordatori: "08:00",
        diesSeleccionats: [],
        color: "#10B981",
      },
      categories: [
        { id: 1, nom: "Activitat física", icona: "AF" },
        { id: 2, nom: "Alimentació", icona: "AL" },
        { id: 3, nom: "Estudi", icona: "ES" },
        { id: 4, nom: "Lectura", icona: "LE" },
        { id: 5, nom: "Benestar", icona: "BE" },
        { id: 6, nom: "Millora d'hàbits", icona: "HB" },
        { id: 7, nom: "Llar", icona: "LL" },
        { id: 8, nom: "Hobby", icona: "HO" },
      ],
      frequencies: ["Diari", "Setmanal", "Mensual"],
      diesSetmana: ["L", "M", "X", "J", "V", "S", "D"],
      colors: ["#65A30D", "#3B82F6", "#A855F7", "#F97316", "#EC4899"],
    };
  },

  /**
   * Computat: Store d'hàbits.
   */
  computed: {
    habitStore: function () {
      return useHabitStore();
    },
  },

  /**
   * Inicialització.
   */
  mounted: function () {
    this.inicialitzarSocket();
    this.carregarHabits();
  },

  /**
   * Neteja.
   */
  beforeUnmount: function () {
    // El socket global es gestionat pel plugin, no el tanquem aquí
  },

  methods: {
    /**
     * Retorna la classe per a la categoria seleccionada.
     */
    obtenirClasseCategoria: function (id) {
      if (this.formulari.categoria === id) {
        return 'ring-2 ring-green-500 bg-green-50';
      }
      return 'bg-white border border-gray-200 hover:border-green-300';
    },

    /**
     * Retorna la classe per a la freqüència seleccionada.
     */
    obtenirClasseFrequencia: function (freq) {
      if (this.formulari.frequencia === freq) {
        return 'bg-white shadow-sm text-gray-800';
      }
      return 'text-gray-500 hover:text-gray-700';
    },

    /**
     * Retorna la classe per a un dia seleccionat.
     */
    obtenirClasseDia: function (index) {
      if (this.comprovarSiDiaSeleccionat(index)) {
        return 'bg-green-600 text-white';
      }
      return 'bg-gray-200 text-gray-600 hover:bg-gray-300';
    },

    /**
     * Retorna la classe per a un color seleccionat.
     */
    obtenirClasseColor: function (color) {
      if (this.formulari.color === color) {
        return 'ring-gray-400';
      }
      return 'ring-transparent';
    },

    /**
     * Retorna la classe per a la categoria seleccionada (edició).
     */
    obtenirClasseCategoriaEdicio: function (id) {
      if (this.formulariEdicio.categoria === id) {
        return 'ring-2 ring-green-500 bg-green-50';
      }
      return 'bg-white border border-gray-200';
    },

    /**
     * Retorna la classe per a un dia seleccionat (edició).
     */
    obtenirClasseDiaEdicio: function (index) {
      if (this.formulariEdicio.diesSeleccionats.indexOf(index) !== -1) {
        return 'bg-green-600 text-white';
      }
      return 'bg-gray-200';
    },

    /**
     * Retorna la classe per a un color seleccionat (edició).
     */
    obtenirClasseColorEdicio: function (color) {
      if (this.formulariEdicio.color === color) {
        return 'ring-2 ring-gray-400';
      }
      return 'ring-transparent';
    },

    /**
     * Inicialitza la conexió amb el servidor de sockets.
     */
    inicialitzarSocket: function () {
      var self = this;
      var nuxtApp = useNuxtApp();

      if (self.socket) {
        return;
      }

      // Utilitzem la instància global injectada pel plugin
      self.socket = nuxtApp.$socket;

      if (!self.socket) {
        console.error("Socket global no disponible");
        return;
      }

      self.socket.on("connect", function () {
        console.log("Socket conectat:", self.socket.id);
      });

      self.socket.on("habit_action_confirmed", function (pàrrega) {
        self.gestionarFeedbackHabit(pàrrega);
      });

      self.socket.on("disconnect", function () {
        console.log("Socket desconectat");
      });
    },

    /**
     * Carrega els hàbits de l'usuari des del servidor.
     */
    carregarHabits: async function () {
      var self = this;
      try {
        await self.habitStore.obtenirHabitsDesDeApi();
      } catch (e) {
        self.errorMissatge = e.message || "Error al carregar els hàbits";
      }
    },
    /**
     * Obté el nom de la categoria a partir del seu ID.
     */
    obtenirNomCategoria: function (id) {
      var i;
      for (i = 0; i < this.categories.length; i++) {
        if (this.categories[i].id === id) {
          return this.categories[i].nom;
        }
      }
      return "Sense categoria";
    },

    /**
     * Selecciona una categoria per l'hàbit i assigna la seva icona automàticament.
     */
    seleccionarCategoria: function (id) {
      this.formulari.categoria = id;
      // Assignem l'icona segons la categoria
      var i;
      for (i = 0; i < this.categories.length; i++) {
        if (this.categories[i].id === id) {
          this.formulari.icona = this.categories[i].icona;
          break;
        }
      }
    },

    /**
     * Selecciona una categoria en el modal d'edició i assigna la seva icona.
     */
    seleccionarCategoriaEdicio: function (id) {
      this.formulariEdicio.categoria = id;
      var i;
      for (i = 0; i < this.categories.length; i++) {
        if (this.categories[i].id === id) {
          this.formulariEdicio.icona = this.categories[i].icona;
          break;
        }
      }
    },

    /**
     * Selecciona o deselecciona un dia de la setmana.
     */
    alternarDia: function (index) {
      var pos = this.formulari.diesSeleccionats.indexOf(index);
      if (pos === -1) {
        this.formulari.diesSeleccionats.push(index);
      } else {
        this.formulari.diesSeleccionats.splice(pos, 1);
      }
    },

    /**
     * Comprova si un dia està seleccionat.
     */
    comprovarSiDiaSeleccionat: function (index) {
      return this.formulari.diesSeleccionats.indexOf(index) !== -1;
    },

    /**
     * Construeix les dades de l'hàbit per enviar al servidor.
     */
    construirDadesHabit: function () {
      var self = this;
      var frequencia = "diaria";
      var dies = [];
      var i;

      if (self.formulari.frequencia === "Setmanal") {
        frequencia = "semanal";
      } else if (self.formulari.frequencia === "Mensual") {
        frequencia = "mensual";
      }

      for (i = 0; i < self.formulari.diesSeleccionats.length; i++) {
        dies.push(self.formulari.diesSeleccionats[i] + 1);
      }

      return {
        titol: self.formulari.nom,
        dificultat: "facil",
        frequencia_tipus: frequencia,
        dies_setmana: dies.join(","),
        objectiu_vegades: 1,
        categoria_id: self.formulari.categoria,
        icona: self.formulari.icona,
        color: self.formulari.color,
      };
    },

    /**
     * Crea un nou hàbit emetent un esdeveniment al socket.
     */
    crearHabit: function () {
      var self = this;
      var dadesHabit;

      // A. Validació bàsica
      if (!self.formulari.nom) {
        alert("Si us plau, introdueix un nom per l'hàbit.");
        return;
      }
      if (!self.formulari.categoria) {
        alert("Si us plau, selecciona una categoria.");
        return;
      }

      if (!self.socket) {
        alert("Conexió de socket no disponible.");
        return;
      }

      // B. Preparar dades i enviar
      dadesHabit = self.construirDadesHabit();
      self.estaCarregant = true;
      self.errorMissatge = "";

      self.socket.emit("habit_action", {
        action: "CREATE",
        habit_data: dadesHabit,
      });
    },

    /**
     * Elimina un hàbit existent.
     */
    eliminarHabit: function (idHabit) {
      var self = this;
      if (!self.socket) {
        alert("Socket no disponible");
        return;
      }
      self.estaCarregant = true;
      self.errorMissatge = "";

      self.socket.emit("habit_action", {
        action: "DELETE",
        habit_id: idHabit,
      });
    },

    /**
     * Gestiona el feedback rebut pel socket després d'una acció CUD.
     */
    gestionarFeedbackHabit: function (pàrrega) {
      var self = this;
      var mapejat;

      self.estaCarregant = false;

      if (!pàrrega || pàrrega.success !== true) {
        self.errorMissatge = "Error al processar l'acció de l'hàbit";
        return;
      }

      if (pàrrega.action === "CREATE" || pàrrega.action === "UPDATE") {
        if (pàrrega.habit) {
          mapejat = self.habitStore.mapejarHabitDesDeApi(pàrrega.habit);
          self.habitStore.guardarOActualitzarHabit(mapejat);
          self.netejarFormulari();
        }
      } else if (pàrrega.action === "DELETE") {
        if (pàrrega.habit && pàrrega.habit.id) {
          self.habitStore.eliminarHabit(pàrrega.habit.id);
        }
      }
    },

    /**
     * Neteja el formulari després d'una creació d'èxit.
     */
    netejarFormulari: function () {
      this.formulari.nom = "";
      this.formulari.motivacio = "";
      this.formulari.icona = "HAB";
      this.formulari.categoria = "";
      this.formulari.frequencia = "Diari";
      this.formulari.recordatori = "08:00";
    },

    /**
     * Obre el modal d'edició d'un hàbit.
     */
    obrirModalEdicio: function (hàbit) {
      console.log("Obrint modal per a l'hàbit:", hàbit.nom);
      this.idHabitEdicio = hàbit.id;
      this.formulariEdicio.nom = hàbit.nom;
      this.formulariEdicio.icona = hàbit.icona;
      this.formulariEdicio.categoria = hàbit.categoriaId;
      this.formulariEdicio.frequencia = hàbit.frequencia;
      this.formulariEdicio.recordatori = hàbit.recordatori || "08:00";
      this.formulariEdicio.color = hàbit.color || "#10B981";

      // Reconstruir dies seleccionats (simulat de moment, hauria de venir de l'api mapejat)
      this.formulariEdicio.diesSeleccionats = [0, 1, 2, 3, 4];
      this.esObertModalEdicio = true;
    },

    /**
     * Tanca el modal d'edició.
     */
    tancarModalEdicio: function () {
      this.esObertModalEdicio = false;
      this.idHabitEdicio = null;
    },

    /**
     * Alterna la selecció d'un dia en el formulari d'edició.
     */
    alternarDiaEdicio: function (index) {
      var pos = this.formulariEdicio.diesSeleccionats.indexOf(index);
      if (pos === -1) {
        this.formulariEdicio.diesSeleccionats.push(index);
      } else {
        this.formulariEdicio.diesSeleccionats.splice(pos, 1);
      }
    },

    /**
     * Actualitza un hàbit emetent l'acció al socket.
     */
    actualitzarHabit: function () {
      var self = this;
      if (!self.formulariEdicio.nom) {
        alert("Si us plau, introdueix un nom.");
        return;
      }

      if (!self.socket) {
        alert("Socket no disponible");
        return;
      }

      var frequencia = "diaria";
      if (self.formulariEdicio.frequencia === "Setmanal") {
        frequencia = "semanal";
      } else if (self.formulariEdicio.frequencia === "Mensual") {
        frequencia = "mensual";
      }

      var dies = [];
      for (var i = 0; i < self.formulariEdicio.diesSeleccionats.length; i++) {
        dies.push(self.formulariEdicio.diesSeleccionats[i] + 1);
      }

      var dadesActualitzades = {
        titol: self.formulariEdicio.nom,
        dificultat: "facil",
        frequencia_tipus: frequencia,
        dies_setmana: dies.join(","),
        objectiu_vegades: 1,
        icona: self.formulariEdicio.icona,
        color: self.formulariEdicio.color,
        categoria_id: self.formulariEdicio.categoria,
      };

      self.estaCarregant = true;
      self.socket.emit("habit_action", {
        action: "UPDATE",
        habit_id: self.idHabitEdicio,
        habit_data: dadesActualitzades,
      });

      self.tancarModalEdicio();
    },
  },
};
</script>
