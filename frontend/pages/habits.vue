<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">{{ $t('habits.title') }}</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Esquerra: Seccions del formulari -->
        <div class="lg:col-span-2 space-y-6">
          <!-- 1. Detalls de l'Hàbit -->
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
          >
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-green-100 p-2 rounded-lg">
                <span class="text-xl">{{ $t('habits.details') }}</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">
                {{ $t('habits.details') }}
              </h2>
            </div>

            <div class="space-y-4">
              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >{{ $t('habits.habit_name') }}</label
                >
                <input
                  v-model="formulari.nom"
                  type="text"
                  :placeholder="$t('habits.placeholder_name')"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
                />
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >{{ $t('habits.motivation') }}</label
                >
                <textarea
                  v-model="formulari.motivacio"
                  :placeholder="$t('habits.motivation_placeholder')"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all resize-none h-24"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >{{ $t('habits.daily_goal') }}</label
                  >
                  <input
                    v-model.number="formulari.objectiuVegades"
                    type="number"
                    min="1"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
                  />
                </div>
                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >{{ $t('habits.unit') }}</label
                  >
                  <input
                    v-model="formulari.unitat"
                    type="text"
                    :placeholder="$t('habits.placeholder_unit')"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
                  />
                </div>
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >{{ $t('habits.difficulty') }}</label
                >
                <select
                  v-model="formulari.dificultat"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
                >
                  <option value="facil">{{ $t('habits.facil') }}</option>
                  <option value="media">{{ $t('habits.media') }}</option>
                  <option value="dificil">{{ $t('habits.dificil') }}</option>
                </select>
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
                <span class="text-xl">{{ $t('habits.category') }}</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.category') }}</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <button
                v-for="cat in categories"
                :key="cat.id"
                type="button"
                @click="seleccionarCategoria(cat.id)"
                :class="[
                  'p-4 rounded-xl flex flex-col items-center justify-center gap-2 transition-all',
                  formulari.categoria === cat.id
                    ? 'ring-2 ring-green-500 bg-green-50'
                    : 'bg-white border border-gray-200 hover:border-green-300',
                ]"
              >
                <span class="text-2xl">{{ cat.icona }}</span>
                <span class="text-sm font-medium text-gray-700">{{
                  $t('habits.categories.' + cat.key)
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
                  <span class="text-xl">{{ $t('habits.planning') }}</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.planning') }}</h2>
              </div>

              <div class="space-y-4">
                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >{{ $t('habits.frequency') }}</label
                  >
                  <div class="flex bg-gray-100 rounded-lg p-1">
                    <button
                      v-for="freq in frequencies"
                      :key="freq"
                      type="button"
                      @click="formulari.frequencia = freq"
                      :class="[
                        'flex-1 py-1.5 text-sm font-medium rounded-md transition-all',
                        formulari.frequencia === freq
                          ? 'bg-white shadow-sm text-gray-800'
                          : 'text-gray-500 hover:text-gray-700',
                      ]"
                    >
                      {{ $t('habits.frequencies.' + freq.toLowerCase()) }}
                    </button>
                  </div>
                </div>

                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >{{ $t('habits.target_days') }}</label
                  >
                  <div class="flex justify-between">
                    <button
                      v-for="(dia, index) in diesSetmana"
                      :key="dia"
                      type="button"
                      @click="alternarDia(index)"
                      :class="[
                        'w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors',
                        comprovarSiDiaSeleccionat(index)
                          ? 'bg-green-600 text-white'
                          : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                      ]"
                    >
                      {{ $t('habits.days.' + dia.toLowerCase()) }}
                    </button>
                  </div>
                </div>

                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >{{ $t('habits.reminder') }}</label
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
                  <span class="text-xl">{{ $t('habits.style') }}</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.customize') }}</h2>
              </div>

              <p class="text-sm text-gray-500 mb-4">
                {{ $t('habits.style_description') }}
              </p>

              <div class="flex gap-3 mb-6">
                <button
                  v-for="color in colors"
                  :key="color"
                  type="button"
                  @click="formulari.color = color"
                  :style="{ backgroundColor: color }"
                  :class="[
                    'w-10 h-10 rounded-full transition-transform hover:scale-110 focus:outline-none ring-2 ring-offset-2',
                    formulari.color === color
                      ? 'ring-gray-400'
                      : 'ring-transparent',
                  ]"
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
            {{ $t('habits.create_button') }}
          </button>
        </div>

        <!-- Dreta: Llista dels meus hàbits -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full"
          >
            <div class="flex items-center gap-3 mb-6">
              <span class="text-xl text-gray-400">{{ $t('home.habits_title') }}</span>
              <h2 class="text-lg font-bold text-gray-800">{{ $t('habits.my_habits') }}</h2>
            </div>

            <div
              v-if="habitStore.habits.length === 0"
              class="text-center py-10 text-gray-400"
            >
              <p>{{ $t('habits.no_habits_yet') }}</p>
              <p class="text-sm">{{ $t('habits.add_new') }}</p>
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
                  {{ $t('habits.delete') }}
                </button>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
              <button
                class="text-sm text-gray-400 hover:text-green-600 transition-colors border-dashed border border-gray-300 rounded-full px-4 py-2 w-full"
              >
                {{ $t('habits.quick_habit') }}
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
            <h2 class="text-xl font-bold text-gray-800">{{ $t('habits.edit_title') }}</h2>
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
              >{{ $t('habits.habit_name') }}</label
            >
            <input
              v-model="formulariEdicio.nom"
              type="text"
              class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                >{{ $t('habits.daily_goal') }}</label
              >
              <input
                v-model.number="formulariEdicio.objectiuVegades"
                type="number"
                min="1"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
            <div>
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                >{{ $t('habits.unit') }}</label
              >
              <input
                v-model="formulariEdicio.unitat"
                type="text"
                placeholder="vegades, minuts, km..."
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>
          </div>

          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >{{ $t('habits.difficulty') }}</label
            >
            <select
              v-model="formulariEdicio.dificultat"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm"
            >
              <option value="facil">{{ $t('habits.facil') }}</option>
              <option value="media">{{ $t('habits.media') }}</option>
              <option value="dificil">{{ $t('habits.dificil') }}</option>
            </select>
          </div>

          <!-- Icon Selection eliminado para automatización -->

          <!-- Category -->
          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >{{ $t('habits.category') }}</label
            >
            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="cat in categories"
                :key="cat.id"
                @click="seleccionarCategoriaEdicio(cat.id)"
                :class="
                  formulariEdicio.categoria === cat.id
                    ? 'ring-2 ring-green-500 bg-green-50'
                    : 'bg-white border border-gray-200'
                "
                class="p-3 rounded-xl flex items-center gap-3 transition-all"
              >
                <span>{{ cat.icona }}</span>
                <span class="text-sm font-medium">{{ $t('habits.categories.' + cat.key) }}</span>
              </button>
            </div>
          </div>

          <!-- Frequency & Days -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                >{{ $t('habits.frequency') }}</label
              >
              <select
                v-model="formulariEdicio.frequencia"
                class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm"
              >
                <option v-for="freq in frequencies" :key="freq" :value="freq">
                  {{ $t('habits.frequencies.' + freq.toLowerCase()) }}
                </option>
              </select>
            </div>
            <div>
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                >{{ $t('habits.reminder') }}</label
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
              >{{ $t('habits.target_days') }}</label
            >
            <div class="flex justify-between">
              <button
                v-for="(dia, index) in diesSetmana"
                :key="dia"
                @click="alternarDiaEdicio(index)"
                :class="
                  formulariEdicio.diesSeleccionats.indexOf(index) !== -1
                    ? 'bg-green-600 text-white'
                    : 'bg-gray-200'
                "
                class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors"
              >
                {{ $t('habits.days.' + dia.toLowerCase()) }}
              </button>
            </div>
          </div>

          <!-- Color Selection -->
          <div>
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
              >{{ $t('habits.color') }}</label
            >
            <div class="flex gap-3">
              <button
                v-for="color in colors"
                :key="color"
                @click="formulariEdicio.color = color"
                :style="{ backgroundColor: color }"
                :class="
                  formulariEdicio.color === color
                    ? 'ring-2 ring-gray-400'
                    : 'ring-transparent'
                "
                class="w-8 h-8 rounded-full transition-transform hover:scale-110 ring-offset-2"
              ></button>
            </div>
          </div>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100 flex gap-3">
          <button
            @click="tancarModalEdicio"
            class="flex-1 px-4 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition-colors"
          >
            {{ $t('habits.cancel') }}
          </button>
          <button
            @click="actualitzarHabit"
            class="flex-1 px-4 py-3 bg-green-700 text-white font-bold rounded-xl hover:bg-green-800 shadow-lg shadow-green-700/20 transition-all"
          >
            {{ $t('habits.save_changes') }}
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
        icona: "💧",
        categoria: "",
        frequencia: "Diari",
        recordatori: "08:00",
        diesSeleccionats: [0, 1, 2, 3, 4], // Dilluns a Divendres per defecte
        color: "#10B981",
        objectiuVegades: 1,
        unitat: "vegades",
        dificultat: "facil",
      },
      esObertModalEdicio: false,
      idHabitEdicio: null,
      formulariEdicio: {
        nom: "",
        motivacio: "",
        icona: "💧",
        categoria: "",
        frequencia: "Diari",
        recordatori: "08:00",
        diesSeleccionats: [],
        color: "#10B981",
        objectiuVegades: 1,
        unitat: "vegades",
        dificultat: "facil",
      },
      categories: [
        { id: 1, key: "physical", icona: "🏃" },
        { id: 2, key: "food", icona: "🥗" },
        { id: 3, key: "study", icona: "📚" },
        { id: 4, key: "reading", icona: "📖" },
        { id: 5, key: "wellness", icona: "🧘" },
        { id: 6, key: "improvement", icona: "✨" },
        { id: 7, key: "home", icona: "🏠" },
        { id: 8, key: "hobby", icona: "🎨" },
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
        console.error("❌ Socket global no disponible");
        return;
      }

      self.socket.on("connect", function () {
        console.log("✅ Socket conectat:", self.socket.id);
      });

      self.socket.on("habit_action_confirmed", function (pàrrega) {
        self.gestionarFeedbackHabit(pàrrega);
      });

      self.socket.on("disconnect", function () {
        console.log("❌ Socket desconectat");
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
      var booleans = [];
      var i;

      if (self.formulari.frequencia === "Setmanal") {
        frequencia = "semanal";
      } else if (self.formulari.frequencia === "Mensual") {
        frequencia = "mensual";
      }

      for (i = 0; i < 7; i++) {
        booleans.push(self.formulari.diesSeleccionats.indexOf(i) !== -1);
      }

      return {
        titol: self.formulari.nom,
        dificultat: self.formulari.dificultat || "facil",
        frequencia_tipus: frequencia,
        dies_setmana: booleans,
        objectiu_vegades: self.formulari.objectiuVegades || 1,
        unitat: self.formulari.unitat || "vegades",
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

      if (pàrrega.action !== "CREATE" && pàrrega.action !== "UPDATE" && pàrrega.action !== "DELETE") {
        return;
      }

      if (pàrrega.action === "CREATE" || pàrrega.action === "UPDATE") {
        if (pàrrega.habit) {
          mapejat = self.habitStore.mapejarHabitDesDeApi(pàrrega.habit);
          self.habitStore.guardarOActualitzarHabit(mapejat);
          self.netejarFormulari();
          if (pàrrega.action === "CREATE") {
            self.mostrarAlertaHabitCreat();
          }
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
      this.formulari.icona = "💧";
      this.formulari.categoria = "";
      this.formulari.frequencia = "Diari";
      this.formulari.recordatori = "08:00";
      this.formulari.objectiuVegades = 1;
      this.formulari.unitat = "vegades";
      this.formulari.dificultat = "facil";
    },

    /**
     * Mostra SweetAlert quan es crea un hàbit.
     */
    mostrarAlertaHabitCreat: function () {
      var mostrarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: "Hábito creado",
            text: "El hábito se ha creado correctamente.",
            icon: "success"
          });
        }
      };

      if (typeof window !== "undefined" && window.Swal) {
        mostrarAlerta();
      } else if (typeof document !== "undefined") {
        var script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js";
        script.onload = mostrarAlerta;
        document.head.appendChild(script);
      }
    },

    /**
     * Obre el modal d'edició d'un hàbit.
     */
    obrirModalEdicio: function (hàbit) {
      console.log("🛠️ Obrint modal per a l'hàbit:", hàbit.nom);
      this.idHabitEdicio = hàbit.id;
      this.formulariEdicio.nom = hàbit.nom;
      this.formulariEdicio.icona = hàbit.icona;
      this.formulariEdicio.categoria = hàbit.categoriaId;
      this.formulariEdicio.frequencia = hàbit.frequencia;
      this.formulariEdicio.recordatori = hàbit.recordatori || "08:00";
      this.formulariEdicio.color = hàbit.color || "#10B981";
      this.formulariEdicio.objectiuVegades = hàbit.objectiuVegades || 1;
      this.formulariEdicio.unitat = hàbit.unitat || "vegades";
      this.formulariEdicio.dificultat = hàbit.dificultat || "facil";

      // Reconstruir dies seleccionats a partir del boolean array
      this.formulariEdicio.diesSeleccionats = [];
      if (Array.isArray(hàbit.diesSetmana)) {
        for (var i = 0; i < hàbit.diesSetmana.length; i++) {
          if (hàbit.diesSetmana[i]) {
            this.formulariEdicio.diesSeleccionats.push(i);
          }
        }
      }
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

      var booleans = [];
      for (var i = 0; i < 7; i++) {
        booleans.push(self.formulariEdicio.diesSeleccionats.indexOf(i) !== -1);
      }

      var dadesActualitzades = {
        titol: self.formulariEdicio.nom,
        dificultat: self.formulariEdicio.dificultat || "facil",
        frequencia_tipus: frequencia,
        dies_setmana: booleans,
        objectiu_vegades: self.formulariEdicio.objectiuVegades || 1,
        unitat: self.formulariEdicio.unitat || "vegades",
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
