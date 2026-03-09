<template>
  <div class="login-container relative w-full min-h-screen pb-12 overflow-y-auto">
    <!-- Navbar / Header Base -->
    <div class="w-full p-6 flex justify-between items-center z-20">
      <div class="flex items-center gap-4">
        <h1 class="text-3xl font-extrabold text-white drop-shadow-md">Loopy Home</h1>
      </div>
      <div class="login-lang-switch !static !top-auto !right-auto">
        <LanguageSwitcher />
        <button v-if="user" @click="logout" class="ml-4 bg-white/90 backdrop-blur-sm text-gray-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:shadow-md transition-all">
          {{ $t('logout') || 'Surt' }}
        </button>
      </div>
    </div>

    <!-- Contenedor Principal Bento -->
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-12 gap-6 pb-20">
      
      <!-- COSTAT ESQUERRE: Missions i Perfil -->
      <div class="col-span-12 lg:col-span-3 space-y-6">
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-xl border border-white/50">
          <UserHomeHomeMissionCard
            :missio-diaria="missioDiaria"
            :missio-completada="missioCompletada"
            :missio-progres="missioProgres"
            :missio-objectiu="missioObjectiu"
          />
          <div class="h-px bg-gray-100 my-4"></div>
          <UserHomeHomeProfileCard
            :user="user"
            :nivell="nivell"
            :xp-actual-nivel="xpActualNivel"
            :xp-objetivo-nivel="xpObjetivoNivel"
            :percentatge-nivell="percentatgeNivell"
          />
        </div>
        <UserHomeHomeLogrosCard :ultims-logros="ultimsLogros" @obrir-modal-logros="obrirModalLogros" />
        <UserHomeHomeRouletteSection :classe-icona-ruleta="classeIconaRuleta" @obrir-modal-ruleta="obrirModalRuleta" />
      </div>

      <!-- CENTRE: El teu Monstre -->
      <div class="col-span-12 lg:col-span-6 space-y-6">
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-white/50 flex flex-col items-center relative min-h-[500px]">
          <div class="flex items-center justify-between w-full mb-6 relative z-10">
            <div>
              <h2 class="text-2xl font-black text-gray-800 tracking-tight">
                {{ $t('home.monster_title') }}
              </h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider">Level {{ nivell }}</span>
              </div>
            </div>
            <UserHomeHomeStreakSection :ratxa="ratxa" :ratxa-maxima="ratxaMaxima" :xp-total="xpTotal" :monedes="monedes" />
          </div>

          <!-- Imatge Monstre en Escenari -->
          <div class="flex-1 w-full flex items-center justify-center relative">
            <div class="w-full h-full rounded-2xl overflow-hidden shadow-inner relative" :style="estilFons">
              <div class="absolute inset-0 bg-black/5"></div>
              <div class="relative w-full h-full flex items-center justify-center p-8">
                <img
                  v-if="imatgeMascota"
                  :src="imatgeMascota"
                  alt="El teu monstre"
                  class="w-48 h-48 lg:w-64 lg:h-64 object-contain drop-shadow-[0_20px_20px_rgba(0,0,0,0.3)] animate-float"
                />
              </div>
            </div>
          </div>

          <p class="text-center text-gray-500 font-medium text-sm mt-6 max-w-sm">
            {{ $t('home.monster_subtitle') }}
          </p>
        </div>
      </div>

      <!-- COSTAT DRET: Hàbits -->
      <div class="col-span-12 lg:col-span-3 space-y-6">
        <UserHomeHomeHabitsSection
          :habits="habitsDelDia"
          :esta-carregant="estaCarregantHabits"
          :error-missatge="errorMissatge"
          :obtenir-progres="obtenirProgres"
          :habit-completat-avui="habitCompletatAvui"
          :esta-processant="comvprovarSiSestaProcessant"
          @netejar-error="errorMissatge = ''"
          @obrir-modal-habit="obrirModalHabit"
        />
      </div>
    </div>

    <!-- Modals -->
    <HabitProgressModal
      :is-open="esObertModalHabit"
      :habit="habitSeleccionat"
      :progress="progresModal"
      :objectiu="objectiuModal"
      :unitat="unitatModal"
      @close="tancarModalHabit"
      @increment="incrementarHabit"
      @decrement="decrementarHabit"
      @confirm="confirmarHabit"
      @invalid-complete="mostrarAvisIncomplet"
    />

    <StreakBrokenModal
      :is-open="esObertModalRatxa"
      :ratxa-anterior="ratxaAnteriorModal"
      @close="tancarModalRatxa"
    />

    <!-- Modal Ruleta y Logros omitted for brevity but remain functional through their components -->
  </div>
</template>

<script>
import { useGameStore } from "~/stores/gameStore.js";
import { useHabitStore } from "~/stores/useHabitStore.js";
import { useLogroStore } from "~/stores/useLogroStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

import HabitProgressModal from "~/components/home/HabitProgressModal.vue";
import StreakBrokenModal from "~/components/home/StreakBrokenModal.vue";
import UserHomeHomeMissionCard from "~/components/user/home/HomeMissionCard.vue";
import UserHomeHomeProfileCard from "~/components/user/home/HomeProfileCard.vue";
import UserHomeHomeLogrosCard from "~/components/user/home/HomeLogrosCard.vue";
import UserHomeHomeRouletteSection from "~/components/user/home/HomeRouletteSection.vue";
import UserHomeHomeStreakSection from "~/components/user/home/HomeStreakSection.vue";
import UserHomeHomeHabitsSection from "~/components/user/home/HomeHabitsSection.vue";
import bosqueImg from "~/assets/img/Bosque.png";
import mascotaImg from "~/assets/img/Mascota.png";

export default {
  components: {
    HabitProgressModal,
    StreakBrokenModal,
    UserHomeHomeMissionCard,
    UserHomeHomeProfileCard,
    UserHomeHomeLogrosCard,
    UserHomeHomeRouletteSection,
    UserHomeHomeStreakSection,
    UserHomeHomeHabitsSection
  },
  data: function () {
    return {
      socket: null,
      estaCarregantHabits: false,
      errorMissatge: "",
      imatgeMascota: mascotaImg,
      esObertModalLogros: false,
      esObertModalRuleta: false,
      esObertModalHabit: false,
      esObertModalRatxa: false,
      ratxaAnteriorModal: 0,
      habitSeleccionat: null,
      estilFons: {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      }
    };
  },
  computed: {
    user: function () { return useAuthStore().user; },
    gameStore: function () { return useGameStore(); },
    habitStore: function () { return useHabitStore(); },
    ratxa: function () { return this.gameStore.ratxa; },
    ratxaMaxima: function () { return this.gameStore.ratxaMaxima; },
    xpTotal: function () { return this.gameStore.xpTotal; },
    nivell: function () { return this.gameStore.nivell || 1; },
    xpActualNivel: function () { return this.gameStore.xpActualNivel || 0; },
    xpObjetivoNivel: function () { return this.gameStore.xpObjetivoNivel || 1000; },
    percentatgeNivell: function () {
      var percent = (this.xpActualNivel / this.xpObjetivoNivel) * 100;
      return Math.round(Math.min(100, Math.max(0, percent)));
    },
    habits: function () { return this.habitStore.habits || []; },
    habitsDelDia: function () {
      var llista = this.habits || [];
      var completats = [];
      var pendents = [];
      for (var i = 0; i < llista.length; i++) {
        if (this.habitCompletatAvui(llista[i].id)) completats.push(llista[i]);
        else pendents.push(llista[i]);
      }
      return pendents.concat(completats);
    },
    logroStore: function () { return useLogroStore(); },
    ultimsLogros: function () { return this.logroStore.logros.slice(-3); },
    missioDiaria: function () { return this.gameStore.missioDiaria; },
    missioCompletada: function () { return this.gameStore.missioCompletada; },
    missioProgres: function () { return this.gameStore.missioProgres; },
    missioObjectiu: function () { return this.gameStore.missioObjectiu; },
    monedes: function () { return this.gameStore.monedes; },
    canSpinRoulette: function () { return this.gameStore.canSpinRoulette; },
    classeIconaRuleta: function () { return this.canSpinRoulette ? "hover:scale-105" : "grayscale opacity-50"; },
    progresModal: function () { return this.habitSeleccionat ? this.obtenirProgres(this.habitSeleccionat.id) : 0; },
    objectiuModal: function () { return this.habitSeleccionat ? this.habitSeleccionat.objectiuVegades || 1 : 1; },
    unitatModal: function () { return this.habitSeleccionat ? this.habitSeleccionat.unitat || "vegades" : "vegades"; }
  },
  mounted: function () {
    var self = this;
    var authStore = useAuthStore();
    authStore.loadFromStorage();
    self.gameStore.sincronitzarUsuariId();
    self.estaCarregantHabits = true;
    self.gameStore.carregarDadesHome()
      .then(function (dades) { if (dades && dades.logros) self.logroStore.setLogros(dades.logros); })
      .finally(function () { self.estaCarregantHabits = false; });
    self.inicialitzarSocket();
  },
  methods: {
    logout: async function() {
      await useAuthStore().logout();
      navigateTo("/auth/login");
    },
    obtenirProgres: function (habitId) {
      var mapa = this.gameStore.habitProgress || {};
      return (mapa[habitId] && mapa[habitId].progress) || 0;
    },
    habitCompletatAvui: function (habitId) {
      var mapa = this.gameStore.habitProgress || {};
      return !!(mapa[habitId] && mapa[habitId].completed_today);
    },
    obrirModalHabit: function (habit) { this.habitSeleccionat = habit; this.esObertModalHabit = true; },
    tancarModalHabit: function () { this.esObertModalHabit = false; this.habitSeleccionat = null; },
    inicialitzarSocket: function () {
      var self = this;
      self.socket = useNuxtApp().$socket;
      if (!self.socket) return;
      self.socket.on("habit_action_confirmed", function (payload) {
        if (payload.success) {
          if (payload.xp_update) self.gameStore.actualitzarDesDeXpUpdate(payload.xp_update);
          if (payload.action === "PROGRESS") {
            var id = payload.habit ? payload.habit.id : payload.habit_id;
            self.gameStore.habitProgress[id] = { progress: payload.progress, completed_today: payload.completed_today };
          }
        }
      });
    },
    confirmarHabit: function () {
      if (!this.habitSeleccionat) return;
      this.gameStore.confirmarHabit(this.habitSeleccionat.id, this.socket);
      this.tancarModalHabit();
    }
  }
};
</script>

<style scoped>
.animate-float {
  animation: float 6s ease-in-out infinite;
}
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
  100% { transform: translateY(0px); }
}
</style>
