<template>
  <div class="login-container relative w-full min-h-screen pb-12 overflow-y-auto">
    <!-- Navbar / Header Base -->
    <div class="w-full p-6 flex justify-between items-center z-20">
      <div class="flex items-center gap-4">
        <NuxtLink to="/home" class="bg-white/90 backdrop-blur-sm text-green-700 w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm hover:shadow-md hover:bg-white transition-all hover:-translate-x-1">
          ←
        </NuxtLink>
        <h1 class="text-3xl font-extrabold text-white drop-shadow-md">{{ $t('habits.title') }}</h1>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Esquerra: Seccions del formulari -->
      <div class="lg:col-span-2 space-y-8">
        <!-- 1. Detalls de l'Hàbit -->
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📝</div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.details') }}</h2>
          </div>

          <div class="space-y-6">
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.habit_name') }}</label>
              <input v-model="formulari.nom" type="text" :placeholder="$t('habits.placeholder_name')" class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all text-lg font-medium" />
            </div>

            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.motivation') }}</label>
              <textarea v-model="formulari.motivacio" :placeholder="$t('habits.motivation_placeholder')" class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all resize-none h-32 text-lg font-medium"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.difficulty') }}</label>
                <select v-model="formulari.dificultat" class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all appearance-none cursor-pointer font-bold">
                  <option value="facil">{{ $t('habits.facil') }}</option>
                  <option value="media">{{ $t('habits.media') }}</option>
                  <option value="dificil">{{ $t('habits.dificil') }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Categoria -->
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📂</div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.category') }}</h2>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <button v-for="cat in categories" :key="cat.id" type="button" @click="seleccionarCategoria(cat.id)" :class="['p-6 rounded-2xl flex flex-col items-center justify-center gap-3 transition-all transform hover:scale-105 active:scale-95 border-2', formulari.categoria === cat.id ? 'border-green-500 bg-green-50 shadow-md ring-4 ring-green-500/5' : 'bg-gray-50/50 border-gray-100 hover:border-green-200']">
              <span class="text-4xl shadow-sm">{{ cat.icona }}</span>
              <span class="text-sm font-bold text-gray-700">{{ $t('habits.categories.' + cat.key) }}</span>
            </button>
          </div>
        </div>

        <!-- Botó Enviar -->
        <button @click="crearHabit" :disabled="estaCarregant" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-6 rounded-3xl shadow-2xl shadow-green-900/40 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-4 text-2xl uppercase tracking-widest disabled:opacity-50">
          <span class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">＋</span>
          {{ estaCarregant ? 'Processant...' : $t('habits.create_button') }}
        </button>
      </div>

      <!-- Dreta: Llista dels meus hàbits -->
      <div class="lg:col-span-1">
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50 h-full">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-xl">✨</div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.my_habits') }}</h2>
          </div>

          <div v-if="habitStore.habits.length === 0" class="text-center py-20 bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-200">
            <p class="text-gray-400 font-bold">{{ $t('habits.no_habits_yet') }}</p>
            <p class="text-xs text-gray-300 mt-2 uppercase tracking-widest">{{ $t('habits.add_new') }}</p>
          </div>

          <div v-else class="space-y-4">
            <div v-for="hàbit in habitStore.habits" :key="hàbit.id" class="flex items-center gap-4 p-4 rounded-2xl bg-white border-2 border-gray-50 shadow-sm hover:shadow-lg hover:border-green-100 transition-all cursor-pointer group" @click="obrirModalEdicio(hàbit)">
              <div :style="{ backgroundColor: hàbit.color || '#10B981' }" class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl text-white shadow-lg shadow-inner transform group-hover:rotate-6 transition-transform">
                {{ hàbit.icona }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-black text-gray-800 truncate text-lg tracking-tight">{{ hàbit.nom }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase">{{ obtenerNomCategoria(hàbit.categoriaId) }}</p>
              </div>
              <button @click.stop="eliminarHabit(hàbit.id)" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white transition-all transform hover:scale-110">
                ×
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHabitStore } from "../stores/useHabitStore";

export default {
  data: function () {
    return {
      socket: null,
      estaCarregant: false,
      errorMissatge: "",
      formulari: {
        nom: "", motivacio: "", icona: "💧", categoria: "", frequencia: "Diari", recordatori: "08:00", color: "#10B981", objectiuVegades: 1, dificultat: "facil"
      },
      categories: [
        { id: 1, key: "physical", icona: "🏃" },
        { id: 2, key: "food", icona: "🥗" },
        { id: 3, key: "study", icona: "📚" },
        { id: 4, key: "reading", icona: "📖" },
        { id: 5, key: "wellness", icona: "🧘" },
        { id: 6, key: "improvement", icona: "✨" },
        { id: 7, key: "home", icona: "🏠" },
        { id: 8, key: "hobby", icona: "🎨" }
      ]
    };
  },
  computed: {
    habitStore: function () { return useHabitStore(); }
  },
  mounted: function () {
    this.carregarHabits();
    this.socket = useNuxtApp().$socket;
  },
  methods: {
    carregarHabits: async function () {
      await this.habitStore.obtenirHabitsDesDeApi();
    },
    seleccionarCategoria: function (id) {
      this.formulari.categoria = id;
      var cat = this.categories.find(function(c) { return c.id === id; });
      if (cat) this.formulari.icona = cat.icona;
    },
    obtenerNomCategoria: function (id) {
      var cat = this.categories.find(function(c) { return c.id === id; });
      return cat ? this.$t('habits.categories.' + cat.key) : "";
    },
    crearHabit: function () {
      if (!this.formulari.nom || !this.formulari.categoria) return;
      this.estaCarregant = true;
      this.socket.emit("habit_action", {
        action: "CREATE",
        habit_data: {
          titol: this.formulari.nom,
          dificultat: this.formulari.dificultat,
          frequencia_tipus: "diaria",
          categoria_id: this.formulari.categoria,
          icona: this.formulari.icona,
          color: this.formulari.color
        }
      });
      setTimeout(function() { this.estaCarregant = false; this.carregarHabits(); }.bind(this), 1000);
    }
  }
};
</script>
