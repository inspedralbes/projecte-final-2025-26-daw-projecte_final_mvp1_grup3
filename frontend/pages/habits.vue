<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Crear Hábito</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Column 1 & 2: Form Sections -->
        <div class="lg:col-span-2 space-y-6">
          <!-- 1. Detalles del Hábito -->
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
          >
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-green-100 p-2 rounded-lg">
                <span class="text-xl">✏️</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">
                Detalles del Hábito
              </h2>
            </div>

            <div class="space-y-4">
              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >Nombre del hábito</label
                >
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Ej: Beber 2L de agua, Leer 30 min..."
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
                />
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >Motivación (Opcional)</label
                >
                <textarea
                  v-model="form.motivation"
                  placeholder="¿Por qué quieres empezar este hábito?"
                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all resize-none h-24"
                ></textarea>
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                  >Icono Rápido</label
                >
                <div class="flex gap-3">
                  <button
                    v-for="icon in icons"
                    :key="icon"
                    @click="selectIcon(icon)"
                    :class="{
                      'bg-green-500 text-white': form.icon === icon,
                      'bg-gray-100 text-gray-600 hover:bg-gray-200':
                        form.icon !== icon,
                    }"
                    class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                  >
                    {{ icon }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Categoría -->
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
          >
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-orange-100 p-2 rounded-lg">
                <span class="text-xl">Shapes</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">Categoría</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <button
                v-for="cat in categories"
                :key="cat.id"
                @click="selectCategory(cat.id)"
                :class="{
                  'ring-2 ring-green-500 bg-green-50': form.category === cat.id,
                  'bg-white border border-gray-200 hover:border-green-300':
                    form.category !== cat.id,
                }"
                class="p-4 rounded-xl flex flex-col items-center justify-center gap-2 transition-all"
              >
                <span class="text-2xl">{{ cat.icon }}</span>
                <span class="text-sm font-medium text-gray-700">{{
                  cat.name
                }}</span>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 3. Planificación -->
            <div
              class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
            >
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                  <span class="text-xl">📅</span>
                </div>
                <!-- Fixed typo from User Request: PEsonalizar -> Personalizar for correct spelling if desired, but user said 'Planificación' here -->
                <h2 class="text-lg font-bold text-gray-800">Planificación</h2>
              </div>

              <div class="space-y-4">
                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >Frecuencia</label
                  >
                  <div class="flex bg-gray-100 rounded-lg p-1">
                    <button
                      v-for="freq in frequencies"
                      :key="freq"
                      @click="form.frequency = freq"
                      :class="{
                        'bg-white shadow-sm text-gray-800':
                          form.frequency === freq,
                        'text-gray-500 hover:text-gray-700':
                          form.frequency !== freq,
                      }"
                      class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all"
                    >
                      {{ freq }}
                    </button>
                  </div>
                </div>

                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >Días Objetivo</label
                  >
                  <div class="flex justify-between">
                    <button
                      v-for="(day, index) in days"
                      :key="day"
                      @click="toggleDay(index)"
                      :class="{
                        'bg-green-600 text-white':
                          form.selectedDays.indexOf(index) !== -1,
                        'bg-gray-200 text-gray-600 hover:bg-gray-300':
                          form.selectedDays.indexOf(index) === -1,
                      }"
                      class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors"
                    >
                      {{ day }}
                    </button>
                  </div>
                </div>

                <div>
                  <label
                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"
                    >Recordatorio</label
                  >
                  <input
                    v-model="form.reminder"
                    type="time"
                    class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
              </div>
            </div>

            <!-- 4. Personalizar -->
            <div
              class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100"
            >
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-purple-100 p-2 rounded-lg">
                  <span class="text-xl">🖌️</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Personalizar</h2>
              </div>

              <p class="text-sm text-gray-500 mb-4">
                Elige el estilo visual de tu tarjeta de hábito.
              </p>

              <div class="flex gap-3 mb-6">
                <button
                  v-for="color in colors"
                  :key="color"
                  @click="form.color = color"
                  :style="{ backgroundColor: color }"
                  class="w-10 h-10 rounded-full transition-transform hover:scale-110 focus:outline-none ring-2 ring-offset-2"
                  :class="{
                    'ring-gray-400': form.color === color,
                    'ring-transparent': form.color !== color,
                  }"
                ></button>
              </div>

              <!-- Preview Card -->
              <div
                class="bg-gray-50 rounded-xl p-4 flex items-center gap-4 opacity-75"
              >
                <div
                  :style="{
                    backgroundColor: form.color || '#10B981',
                    color: 'white',
                  }"
                  class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                >
                  {{ form.icon || "📝" }}
                </div>
                <div class="h-2 bg-gray-200 rounded w-2/3"></div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            @click="createHabit"
            class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-700/20 transition-all transform active:scale-95 flex items-center justify-center gap-2"
          >
            <span
              class="bg-white text-green-700 rounded-full w-5 h-5 flex items-center justify-center text-xs"
              >✓</span
            >
            Crear Hábito
          </button>
        </div>

        <!-- Column 3: My Habits List -->
        <!-- 5. Mis hábitos -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full"
          >
            <div class="flex items-center gap-3 mb-6">
              <span class="text-xl text-gray-400">📋</span>
              <h2 class="text-lg font-bold text-gray-800">Mis Hábitos</h2>
            </div>

            <div
              v-if="habitStore.habits.length === 0"
              class="text-center py-10 text-gray-400"
            >
              <p>No tienes hábitos aún.</p>
              <p class="text-sm">¡Añade uno nuevo!</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="habit in habitStore.habits"
                :key="habit.id"
                class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100 group"
              >
                <div
                  :style="{ backgroundColor: habit.color || '#10B981' }"
                  class="w-12 h-12 rounded-full flex items-center justify-center text-xl text-white shadow-sm"
                >
                  {{ habit.icon }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-gray-800 truncate">
                    {{ habit.name }}
                  </h3>
                  <p class="text-xs text-gray-500">
                    {{ habit.frequency }}
                    <span v-if="habit.reminder">• {{ habit.reminder }}</span>
                  </p>
                </div>
                <button
                  class="text-xs text-red-600 hover:text-red-700 font-semibold px-2 py-1 rounded border border-red-200 hover:border-red-300 transition-colors"
                  @click="deleteHabit(habit.id)"
                >
                  Borrar
                </button>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
              <button
                class="text-sm text-gray-400 hover:text-green-600 transition-colors border-dashed border border-gray-300 rounded-full px-4 py-2 w-full"
              >
                + Hábito rápido
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { io } from "socket.io-client";
import { useHabitStore } from "../stores/useHabitStore";

export default {
  setup: function () {
    var habitStore = useHabitStore();
    return { habitStore: habitStore };
  },
  data: function () {
    return {
      socket: null,
      isLoading: false,
      errorMessage: "",
      form: {
        name: "",
        motivation: "",
        icon: "💧",
        category: "",
        frequency: "Diario",
        reminder: "08:00",
        selectedDays: [0, 1, 2, 3, 4], // Default Mon-Fri
        color: "#10B981",
      },
      icons: ["💧", "📖", "🏃", "🧘", "🚭", "🥗", "💊", "💤"],
      categories: [
        { id: "salud", name: "Salud", icon: "❤️" },
        { id: "estudio", name: "Estudio", icon: "📚" },
        { id: "trabajo", name: "Trabajo", icon: "💼" },
        { id: "arte", name: "Arte", icon: "🎨" },
      ],
      frequencies: ["Diario", "Semanal", "Mensual"],
      days: ["L", "M", "X", "J", "V", "S", "D"],
      colors: ["#65A30D", "#3B82F6", "#A855F7", "#F97316", "#EC4899"],
    };
  },
  mounted: function () {
    this.initSocket();
    this.loadHabits();
  },
  beforeUnmount: function () {
    if (this.socket) {
      this.socket.disconnect();
    }
  },
  methods: {
    initSocket: function () {
      if (this.socket) {
        return;
      }
      this.socket = io("http://localhost:3001", {
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: 5,
      });

      var self = this;

      this.socket.on("connect", function () {
        console.log("✅ Socket connectat:", self.socket.id);
      });

      this.socket.on("habit_action_confirmed", function (payload) {
        self.handleHabitFeedback(payload);
      });

      this.socket.on("disconnect", function () {
        console.log("❌ Socket desconnectat");
      });

      this.socket.on("error", function (error) {
        console.error("⚠️ Error en socket:", error);
      });
    },
    loadHabits: async function () {
      try {
        await this.habitStore.fetchHabitsFromApi();
      } catch (e) {
        this.errorMessage = e.message || "Error al cargar los hábitos";
      }
    },
    selectIcon: function (icon) {
      this.form.icon = icon;
    },
    selectCategory: function (id) {
      this.form.category = id;
    },
    toggleDay: function (index) {
      var pos = this.form.selectedDays.indexOf(index);
      if (pos === -1) {
        this.form.selectedDays.push(index);
      } else {
        // Manually implement splice/filter to avoid prohibited high-order functions if strict is extreme,
        // but prompt said "Prohibides funcions d'ordre superior: Prohibits map, filter o reduce". 'indexOf' and 'splice' are standard array methods not typically considered functional HOFs like map/reduce, but I will be safe.
        // Actually splice is fine. Filter is HOF.
        this.form.selectedDays.splice(pos, 1);
      }
    },
    buildHabitData: function () {
      var frequencia = "diaria";
      if (this.form.frequency === "Semanal") {
        frequencia = "semanal";
      } else if (this.form.frequency === "Mensual") {
        frequencia = "mensual";
      }

      var dies = [];
      for (var i = 0; i < this.form.selectedDays.length; i++) {
        dies.push(this.form.selectedDays[i] + 1);
      }

      return {
        titol: this.form.name,
        dificultat: "facil",
        frequencia_tipus: frequencia,
        dies_setmana: dies.join(","),
        objectiu_vegades: 1,
      };
    },
    createHabit: function () {
      // Basic validation
      if (!this.form.name) {
        alert("Por favor, introduce un nombre para el hábito.");
        return;
      }
      if (!this.form.category) {
        alert("Por favor, selecciona una categoría.");
        return;
      }

      if (!this.socket) {
        alert("Socket no disponible");
        return;
      }

      var habitData = this.buildHabitData();
      this.isLoading = true;
      this.errorMessage = "";

      this.socket.emit("habit_action", {
        action: "CREATE",
        habit_data: habitData,
      });
    },
    deleteHabit: function (habitId) {
      if (!this.socket) {
        alert("Socket no disponible");
        return;
      }
      this.isLoading = true;
      this.errorMessage = "";

      this.socket.emit("habit_action", {
        action: "DELETE",
        habit_id: habitId,
      });
    },
    handleHabitFeedback: function (payload) {
      this.isLoading = false;

      if (!payload || payload.success !== true) {
        this.errorMessage = "Error al procesar la acción del hábito";
        return;
      }

      if (payload.action === "CREATE" || payload.action === "UPDATE") {
        if (payload.habit) {
          var mapped = this.habitStore.mapHabitFromApi(payload.habit);
          this.habitStore.upsertHabit(mapped);
          this.resetForm();
        }
      } else if (payload.action === "DELETE") {
        if (payload.habit && payload.habit.id) {
          this.habitStore.removeHabit(payload.habit.id);
        }
      }
    },
    resetForm: function () {
      this.form.name = "";
      this.form.motivation = "";
      this.form.icon = "💧";
      this.form.category = "";
      // Keep some defaults like frequency or days if UX prefers, or reset all
      this.form.frequency = "Diario";
      this.form.reminder = "08:00";
    },
  },
};
</script>
