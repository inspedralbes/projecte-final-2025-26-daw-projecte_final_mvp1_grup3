<template>
  <div class="home-page-root relative w-full min-h-screen pb-24 lg:pb-12 overflow-y-auto">
    <!-- El header ja el proporciona el layout default.vue -->

    <!-- Contenedor Principal Bento -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 grid grid-cols-12 gap-3 lg:gap-6 lg:items-start lg:content-start pb-16 lg:pb-20">

      <!-- CENTRE: sense sticky → el monstre creua el scroll amb el fons natural (no queda pinat sol) -->
      <div class="col-span-12 lg:col-span-6 order-3 lg:order-2 lg:row-span-1 h-fit max-h-none space-y-4 lg:space-y-6 lg:self-start lg:z-[5]">
        <!-- Mobile: monstre sobre el fons global (imatge dalt + verd #7EB356 sota) -->
        <div class="lg:hidden relative w-full flex justify-center px-2 pt-0 pb-1 overflow-visible">
          <img
            v-if="imatgeMascotaDinamica"
            :src="imatgeMascotaDinamica"
            alt="El teu monstre"
            width="500"
            height="500"
            class="w-[500px] max-w-full h-auto max-h-[500px] object-contain object-bottom drop-shadow-[0_14px_28px_rgba(0,0,0,0.35)] select-none -translate-y-3 sm:-translate-y-4"
            decoding="async"
            draggable="false"
          />
        </div>

        <!-- Desktop: monstre en bento (mateix patró responsive) + accés ràpid al calendari -->
        <div class="hidden lg:flex bento-card rounded-3xl p-8 flex-col items-center relative w-full min-h-0 bg-white/95 backdrop-blur-md shadow-2xl border border-white/50 shrink-0">
          <div class="flex shrink-0 items-center justify-between w-full mb-6 relative z-10">
            <div>
              <h2 class="text-2xl font-black text-gray-800 tracking-tight">
                {{ $t('home.monster_title') }}
              </h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $t('home.level') }} {{ nivellMostrat }}</span>
                <button
                  v-if="vistaHistorialDia"
                  type="button"
                  class="w-10 h-10 flex items-center justify-center transition-transform hover:scale-110 active:scale-95"
                  @click="tornarHistorialCalendari"
                  aria-label="Tornar al calendari"
                >
                  <svg width="32" height="32" viewBox="0 0 73 73" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M42.5834 54.75L24.3334 36.5L42.5834 18.25L46.8417 22.5083L32.85 36.5L46.8417 50.4917L42.5834 54.75Z" fill="#FAF9F9"/>
                  </svg>
                </button>
                <div v-else class="flex items-center gap-2">
                  <button
                    type="button"
                    class="w-8 h-8 rounded-full bg-orange-50 shadow-[3px_3px_8px_rgba(0,0,0,0.1)] border border-white/60 flex items-center justify-center hover:bg-orange-100 hover:scale-105 transition-all duration-200"
                    title="Inventari"
                    @click="anarAlInventari"
                  >
                    <img :src="imatgeInventari" class="w-4 h-4 object-contain select-none" />
                  </button>
                  <button
                    type="button"
                    class="w-8 h-8 rounded-full bg-indigo-50 shadow-[3px_3px_8px_rgba(0,0,0,0.1)] border border-white/60 flex items-center justify-center text-indigo-500 hover:bg-indigo-100 hover:scale-105 transition-all duration-200"
                    title="Calendari"
                    @click="anarAlCalendari"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <UserHomeHomeStreakSection :ratxa="ratxa" :ratxa-maxima="ratxaMaxima" :xp-total="xpTotal" :monedes="monedes" />
          </div>

          <div class="w-full flex flex-col items-center justify-start relative pt-2 shrink-0">
            <div class="flex justify-center w-full px-2 pb-2 -mt-1">
              <img
                v-if="imatgeMascotaDinamica"
                :src="imatgeMascotaDinamica"
                alt="El teu monstre"
                width="500"
                height="500"
                class="w-[500px] max-w-full h-auto max-h-[500px] object-contain object-bottom drop-shadow-[0_20px_20px_rgba(0,0,0,0.28)] -translate-y-3 lg:-translate-y-5"
                decoding="async"
                draggable="false"
              />
            </div>
          </div>

          <p class="text-center text-gray-500 font-medium text-sm mt-6 max-w-sm shrink-0">
            {{ $t('home.monster_subtitle') }}
          </p>
        </div>
      </div>

      <!-- COSTAT ESQUERRE -->
      <div class="col-span-12 lg:col-span-3 order-1 lg:order-1 lg:self-start space-y-2 lg:space-y-6 pt-1 lg:pt-0">
        <div class="grid grid-cols-4 gap-1 items-stretch w-full lg:block lg:space-y-6">
          <div class="hidden lg:block col-span-4 bento-card rounded-xl lg:rounded-3xl bg-[#FAF9F9] p-2 sm:p-2.5 lg:p-6 lg:backdrop-blur-md lg:shadow-xl lg:border lg:border-white/50 min-w-0 w-full lg:h-auto overflow-hidden lg:overflow-visible">
            <UserHomeHomeProfileCard
              :user="user"
              :nivell="nivellMostrat"
              :xp-actual-nivel="xpActualMostrat"
              :xp-objetivo-nivel="xpObjetivoMostrat"
              :percentatge-nivell="percentatgeNivellMostrat"
            />
          </div>
          <!-- Calendari i inventari (mòbil) — ocult en vista històrica -->
          <div
            v-if="!vistaHistorialDia"
            class="col-span-4 lg:hidden flex flex-row flex-nowrap justify-end items-center gap-2 min-w-0 w-full pt-1 pr-0.5 pl-0.5"
          >
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="shrink-0 w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] flex items-center justify-center transition-transform hover:scale-105 active:scale-95 min-h-0"
                title="Inventari"
                @click="anarAlInventari"
              >
                <img
                  :src="imatgeInventari"
                  alt="Inventari"
                  class="max-w-full max-h-full w-auto h-auto object-contain select-none"
                  decoding="async"
                  draggable="false"
                />
              </button>
              <button
                type="button"
                class="shrink-0 w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] flex items-center justify-center transition-transform hover:scale-105 active:scale-95 min-h-0"
                :title="$t('nav.calendar') || 'Calendari'"
                @click="anarAlCalendari"
              >
                <img
                  :src="imatgeCalendari"
                  alt="Calendari"
                  class="max-w-full max-h-full w-auto h-auto object-contain select-none"
                  decoding="async"
                  draggable="false"
                />
              </button>
            </div>
          </div>
        </div>
        <div v-if="!vistaHistorialDia" class="hidden lg:block">
          <UserHomeHomeLogrosCard :ultims-logros="ultimsLogros" @obrir-modal-logros="obrirModalLogros" />
        </div>
        <div v-if="!vistaHistorialDia" class="hidden lg:block">
          <UserHomeHomeRouletteSection :classe-icona-ruleta="classeIconaRuleta" @obrir-modal-ruleta="obrirModalRuleta" />
        </div>
      </div>

      <!-- COSTAT DRET: només aquesta columna allarga la pàgina cap avall -->
      <div class="col-span-12 lg:col-span-3 order-5 lg:order-3 lg:self-start space-y-4 lg:space-y-6">
        <div class="hidden lg:block">
          <WeatherWidget
            :dades="weatherGlobal"
            :carregant="weatherCarregant"
            :mode="weatherMode"
            :ciutat="weatherCiutat"
            @update:ciutat="weatherCiutat = $event"
            @refresh="carregarClima"
            @use-geo="usarGeolocal"
            @switch-manual="passarAManual"
          />
        </div>
        <UserHomeHomeHabitsSection
          :habits="habitsDelDia"
          :esta-carregant="habitsSectionCarregant"
          :error-missatge="errorMissatge"
          :obtenir-progres="obtenirProgres"
          :habit-completat-avui="habitCompletatAvui"
          :esta-processant="comvprovarSiSestaProcessant"
          :weather-global="weatherGlobal"
          :read-only="vistaHistorialDia"
          @netejar-error="errorMissatge = ''"
          @obrir-modal-habit="obrirModalHabit"
          @obrir-detalls-habit="obrirModalDetallsHabit"
          @habit-creat="refrescarDespresCrearHabit"
          @incrementar-habit="incrementarHabitInline"
          @decrementar-habit="decrementarHabitInline"
          @completar-habit="completarHabitInline"
          @start-focus-habit="iniciarSessioFocus"
        >
          <template v-if="!vistaHistorialDia" #below-daily-progress>
            <div class="min-w-0 w-full space-y-3">
              <UserHomeHomeMissionCard
                :missio-diaria="missioDiaria"
                :missio-completada="missioCompletada"
                :missio-progres="missioProgres"
                :missio-objectiu="missioObjectiu"
              />
              <UserHomeHomeDailyRouletteCard
                :pot-tirar="canSpinRoulette"
                @obrir-ruleta="obrirModalRuleta"
              />
            </div>
          </template>
        </UserHomeHomeHabitsSection>
      </div>
    </div>

    <!-- Modals -->
    <HabitProgressModal
      :is-open="esObertModalHabit"
      :habit="habitSeleccionat"
      :progress="progresModal"
      :objectiu="objectiuModal"
      :unitat="unitatModal"
      :is-completed-today="habitSeleccionat ? habitCompletatAvui(habitSeleccionat.id) : false"
      @close="tancarModalHabit"
      @increment="incrementarHabit"
      @decrement="decrementarHabit"
      @confirm="confirmarHabit"
      @invalid-complete="mostrarAvisIncomplet"
    />
    <HabitDetailsModal
      :is-open="esObertModalDetalls"
      :habit="habitDetallsSeleccionat"
      :weather-context="weatherContextDetalls"
      :is-completed-today="habitDetallsCompletatAvui"
      @close="tancarModalDetallsHabit"
      @start-focus="iniciarSessioFocus"
    />

    <StreakBrokenModal
      :is-open="esObertModalRatxa"
      :ratxa-anterior="ratxaAnteriorModal"
      @close="tancarModalRatxa"
    />

    <StreakCelebrationModal
      :is-open="esObertModalRatxaCelebracio"
      :ratxa="ratxa"
      @close="tancarModalRatxaCelebracio"
    />

    <LevelUpCelebrationModal
      :is-open="esObertModalLevelUp"
      :nivell="nivellLevelUpModal"
      @close="tancarModalLevelUp"
    />

    <LogrosModal
      :is-open="esObertModalLogros"
      :logros="logroStore.logros"
      @close="tancarModalLogros"
    />

    <RouletteDailySpinHost ref="ruletaDailySpin" />

    <MissionCompletedModal
      :is-open="esObertModalMissio"
      :missio-titol="missioTitolModal"
      :recompensa-xp="missioRecompensaXp"
      :recompensa-monedes="missioRecompensaMonedes"
      @close="tancarModalMissio"
    />

  </div>
</template>

<script>
import { useGameStore } from "~/stores/gameStore.js";
import { useHabitStore } from "~/stores/useHabitStore.js";
import { useLogroStore } from "~/stores/useLogroStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

import HabitProgressModal from "~/components/home/HabitProgressModal.vue";
import StreakBrokenModal from "~/components/home/StreakBrokenModal.vue";
import StreakCelebrationModal from "~/components/home/StreakCelebrationModal.vue";
import LevelUpCelebrationModal from "~/components/home/LevelUpCelebrationModal.vue";
import LogrosModal from "~/components/home/LogrosModal.vue";
import MissionCompletedModal from "~/components/home/MissionCompletedModal.vue";
import HabitDetailsModal from "~/components/user/home/HabitDetailsModal.vue";
import UserHomeHomeMissionCard from "~/components/user/home/HomeMissionCard.vue";
import UserHomeHomeDailyRouletteCard from "~/components/user/home/HomeDailyRouletteCard.vue";
import UserHomeHomeProfileCard from "~/components/user/home/HomeProfileCard.vue";
import UserHomeHomeLogrosCard from "~/components/user/home/HomeLogrosCard.vue";
import UserHomeHomeRouletteSection from "~/components/user/home/HomeRouletteSection.vue";
import UserHomeHomeHabitsSection from "~/components/user/home/HomeHabitsSection.vue";
import WeatherWidget from "~/components/user/home/WeatherWidget.vue";
import { authFetch } from "~/composables/useApi.js";
import { useCalendar } from "~/composables/useCalendar.js";
import { useCalendarStore } from "~/stores/calendar.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import { useHabitsPage } from "~/composables/domains/habits/useHabitsPage.js";
import { useHabitActions } from "~/composables/domains/habits/useHabitActions.js";
import { useHomeSocketUi } from "~/composables/domains/game/useHomeSocketUi.js";
import { useShopStore } from "~/stores/useShopStore.js";
import calendarImg from "~/assets/img/Icones/Icona_Calendari.png";
import inventariImg from "~/assets/img/Icones/Icona_Inventari.png";
import UserHomeHomeStreakSection from "~/components/user/home/HomeStreakSection.vue";
import RouletteDailySpinHost from "~/components/roulette/RouletteDailySpinHost.vue";

export default {
  components: {
    HabitProgressModal,
    HabitDetailsModal,
    StreakBrokenModal,
    StreakCelebrationModal,
    LevelUpCelebrationModal,
    LogrosModal,
    MissionCompletedModal,
    UserHomeHomeMissionCard,
    UserHomeHomeDailyRouletteCard,
    UserHomeHomeProfileCard,
    UserHomeHomeLogrosCard,
    UserHomeHomeRouletteSection,
    UserHomeHomeHabitsSection,
    UserHomeHomeStreakSection,
    WeatherWidget,
    RouletteDailySpinHost
  },
  data: function () {
    return {
      socket: null,
      procesantHabits: [],
      estaCarregantHabits: false,
      errorMissatge: "",
      esObertModalLogros: false,
      esObertModalHabit: false,
      esObertModalDetalls: false,
      esObertModalRatxa: false,
      esObertModalRatxaCelebracio: false,
      esObertModalLevelUp: false,
      nivellLevelUpModal: 1,
      esObertModalMissio: false,
      missioTitolModal: "",
      missioRecompensaXp: 20,
      missioRecompensaMonedes: 10,
      rachaInicialCarregada: false,
      ratxaAnteriorModal: 0,
      habitSeleccionat: null,
      habitDetallsSeleccionat: null,
      weatherContextDetalls: null,
      weatherGlobal: null,
      weatherCiutat: "",
      weatherMode: "requesting",
      weatherLat: null,
      weatherLon: null,
      weatherCarregant: false,
      ruletaProcessant: false,
      imatgeCalendari: calendarImg,
      imatgeInventari: inventariImg,
      snapshotHistoric: null,
      carregantSnapshotHistoric: false,
      errorSnapshotHistoric: "",
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
    monstreTipusActual: function () {
      if (typeof window === 'undefined') return null;
      return localStorage.getItem('loopy_monstre_tipus') || null;
    },
    imatgeMascotaDinamica: function () {
      var skinKey = null;
      try {
        var shopSt = useShopStore();
        skinKey = shopSt.skinEquipat || null;
      } catch (_) {}
      if (this.snapshotHistoric && this.snapshotHistoric.mascota_json) {
        var mascotaData = this.snapshotHistoric.mascota_json;
        if (!mascotaData.monstre_tipus && this.user) {
          mascotaData = Object.assign({}, mascotaData, { monstre_tipus: this.user.monstre_tipus });
        }
        var snapshotSkin = mascotaData.skin_key || null;
        return getMonsterImageFromUser(mascotaData, snapshotSkin);
      }
      return getMonsterImageFromUser(this.user, skinKey);
    },
    dataHistorialDia: function () {
      var q = this.$route && this.$route.query ? this.$route.query.date : null;
      if (!q || typeof q !== "string") return null;
      var cal = useCalendar();
      if (cal.isAfterToday(q) || !cal.parseDate(q)) return null;
      var d = cal.parseDate(q);
      var avui = new Date();
      var avui0 = new Date(avui.getFullYear(), avui.getMonth(), avui.getDate());
      if (d.getTime() === avui0.getTime()) return null;
      return q;
    },
    vistaHistorialDia: function () {
      return this.dataHistorialDia != null;
    },
    habitsSectionCarregant: function () {
      return this.estaCarregantHabits || (this.vistaHistorialDia && this.carregantSnapshotHistoric);
    },
    xpActualMostrat: function () {
      if (this.snapshotHistoric && this.snapshotHistoric.mascota_json) {
        var m = this.snapshotHistoric.mascota_json;
        return m.xp_actual_nivel != null ? Number(m.xp_actual_nivel) : 0;
      }
      return this.xpActualNivel;
    },
    xpObjetivoMostrat: function () {
      if (this.snapshotHistoric && this.snapshotHistoric.mascota_json) {
        var m = this.snapshotHistoric.mascota_json;
        return m.xp_objetivo_nivel != null ? Number(m.xp_objetivo_nivel) : 1000;
      }
      return this.xpObjetivoNivel;
    },
    nivellMostrat: function () {
      if (this.snapshotHistoric && this.snapshotHistoric.mascota_json) {
        var m2 = this.snapshotHistoric.mascota_json;
        return m2.nivell != null ? Number(m2.nivell) : 1;
      }
      return this.nivell;
    },
    percentatgeNivellMostrat: function () {
      var xpA = this.xpActualMostrat;
      var xpO = this.xpObjetivoMostrat || 1000;
      return Math.round(Math.min(100, Math.max(0, (xpA / xpO) * 100)));
    },
    habitsDelDia: function () {
      if (this.dataHistorialDia) {
        if (this.snapshotHistoric && this.snapshotHistoric.habits_json) {
          return this.mapHabitsHistoric(this.snapshotHistoric.habits_json);
        }
        return [];
      }
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
    unitatModal: function () { return this.habitSeleccionat ? this.habitSeleccionat.unitat || "vegades" : "vegades" }
    ,
    habitDetallsCompletatAvui: function () {
      if (!this.habitDetallsSeleccionat || !this.habitDetallsSeleccionat.id) {
        return false;
      }
      return this.habitCompletatAvui(this.habitDetallsSeleccionat.id);
    }
  },
  mounted: function () {
    var self = this;
    var habitsPage = useHabitsPage();
    if (!self.habitStore.habits || self.habitStore.habits.length === 0) {
      self.estaCarregantHabits = true;
    }
    habitsPage.carregarHomeInicial().finally(function () {
      self.estaCarregantHabits = false;
      self.rachaInicialCarregada = true;
    });
    var homeSocketUi = useHomeSocketUi(self);
    self.socket = homeSocketUi.connectarSocketHome();
    self._homeSocketUiNetejar = homeSocketUi.netejar;

    self.inicialitzarClima();

    self._onLoopyWeatherCity = function () {
      var c = (localStorage.getItem("loopy_weather_city") || "").trim();
      if (c) {
        self.weatherCiutat = c;
      }
      self.weatherMode = "manual";
      self.weatherLat = null;
      self.weatherLon = null;
      self.carregarClima();
    };
    if (typeof window !== "undefined") {
      window.addEventListener("loopy-weather-city-changed", self._onLoopyWeatherCity);
    }
    self._onDebugKey = function (e) {
      if (e.key === "9") {
        self.mostrarAlertaLevelUp({ nivell: (self.gameStore.nivell || 1) + 1 });
      }
    };
    if (typeof window !== "undefined") {
      window.addEventListener("keydown", self._onDebugKey);
    }
    self.$nextTick(function () {
      self.sincronitzarHistoricDesDeRuta();
    });
  },
  beforeUnmount: function () {
    if (this._homeSocketUiNetejar && typeof this._homeSocketUiNetejar === "function") {
      this._homeSocketUiNetejar();
    }
    this.gameStore.historicOverrides = null;
    if (typeof window !== "undefined" && this._onLoopyWeatherCity) {
      window.removeEventListener("loopy-weather-city-changed", this._onLoopyWeatherCity);
    }
    if (typeof window !== "undefined" && this._onDebugKey) {
      window.removeEventListener("keydown", this._onDebugKey);
    }
  },
  watch: {
    ratxa: function (novaRatxa, vellaRatxa) {
      if (this.rachaInicialCarregada && vellaRatxa !== undefined && novaRatxa > vellaRatxa) {
        this.esObertModalRatxaCelebracio = true;
      }
    },
    weatherCiutat: function (nova) {
      if (typeof window !== "undefined" && nova && nova.trim() !== "") {
        localStorage.setItem("loopy_weather_city", nova.trim());
      }
    },
    "$route.query.date": function () {
      this.sincronitzarHistoricDesDeRuta();
    }
  },
  methods: {
    refrescarDespresCrearHabit: function () {
      if (this.vistaHistorialDia) return;
      var self = this;
      self.estaCarregantHabits = true;
      self.gameStore.carregarDadesHome()
        .finally(function () {
          self.estaCarregantHabits = false;
        });
    },
    /**
     * Punt d'entrada al carregar la pàgina.
     * Si el navegador té el permís concedit, getCurrentPosition torna ràpid
     * sense cap popup. Sempre s'intenta primer; el mode guardat és el fallback.
     */
    inicialitzarClima: function () {
      var self = this;
      if (typeof window === "undefined") {
        return;
      }

      var modeSat = localStorage.getItem("loopy_weather_mode");

      if (modeSat === "denied") {
        localStorage.removeItem("loopy_weather_mode");
        modeSat = null;
      }

      if (modeSat === "manual") {
        var ciutatDesada = localStorage.getItem("loopy_weather_city") || "";
        self.weatherCiutat = ciutatDesada.trim() || "Barcelona";
        self.weatherMode = "manual";
        self.carregarClima();
        return;
      }

      if (typeof navigator === "undefined" || !navigator.geolocation) {
        var ciutatFallback = localStorage.getItem("loopy_weather_city") || "Barcelona";
        self.weatherCiutat = ciutatFallback.trim();
        self.weatherMode = "manual";
        self.carregarClima();
        return;
      }

      self.weatherMode = "requesting";

      navigator.geolocation.getCurrentPosition(
        function (pos) {
          self.weatherLat = pos.coords.latitude;
          self.weatherLon = pos.coords.longitude;
          self.weatherMode = "geo";
          localStorage.setItem("loopy_weather_mode", "geo");
          self.carregarClima();
        },
        function () {
          self.weatherMode = "denied";
          var ciutatDesadaDenied = localStorage.getItem("loopy_weather_city") || "";
          self.weatherCiutat = ciutatDesadaDenied.trim() || "Barcelona";
          self.carregarClima();
        },
        { timeout: 8000, maximumAge: 300000 }
      );
    },

    /**
     * Intenta obtenir la geolocalització del navegador (cridat manualment).
     */
    intentarGeolocal: function () {
      var self = this;
      if (typeof navigator === "undefined" || !navigator.geolocation) {
        self.weatherMode = "manual";
        localStorage.setItem("loopy_weather_mode", "manual");
        return;
      }
      self.weatherMode = "requesting";
      navigator.geolocation.getCurrentPosition(
        function (pos) {
          self.weatherLat = pos.coords.latitude;
          self.weatherLon = pos.coords.longitude;
          self.weatherMode = "geo";
          localStorage.setItem("loopy_weather_mode", "geo");
          self.carregarClima();
        },
        function () {
          self.weatherMode = "denied";
          localStorage.setItem("loopy_weather_mode", "denied");
          var ciutatDesada = localStorage.getItem("loopy_weather_city") || "";
          self.weatherCiutat = ciutatDesada.trim() || "Barcelona";
          self.carregarClima();
        },
        { timeout: 8000, maximumAge: 60000 }
      );
    },

    /**
     * Crida quan l'usuari vol tornar a usar geolocalització.
     */
    usarGeolocal: function () {
      this.weatherGlobal = null;
      this.intentarGeolocal();
    },

    /**
     * Crida quan l'usuari vol passar al mode manual.
     */
    passarAManual: function () {
      this.weatherMode = "manual";
      this.weatherLat = null;
      this.weatherLon = null;
      localStorage.setItem("loopy_weather_mode", "manual");
    },

    /**
     * Carrega el clima. Usa lat/lon si mode=geo, sinó usa la ciutat.
     */
    carregarClima: async function () {
      var self = this;
      self.weatherCarregant = true;
      try {
        var url = "/api/external/weather?";
        if (self.weatherMode === "geo" && self.weatherLat !== null && self.weatherLon !== null) {
          url += "lat=" + encodeURIComponent(self.weatherLat) + "&lon=" + encodeURIComponent(self.weatherLon);
        } else {
          var ciutat = (self.weatherCiutat || "Barcelona").trim();
          url += "city=" + encodeURIComponent(ciutat);
        }
        var resposta = await authFetch(url, {});
        var dades = await resposta.json();
        if (resposta.ok && dades && typeof dades === "object") {
          self.weatherGlobal = dades;
          if (dades.ok === true && dades.city && self.weatherMode !== "geo") {
            self.weatherCiutat = dades.city;
          }
        } else {
          self.weatherGlobal = { ok: false };
        }
      } catch (e) {
        self.weatherGlobal = null;
      } finally {
        self.weatherCarregant = false;
      }
    },
    mapHabitsHistoric: function (habitsJson) {
      var completats = [];
      var pendents = [];
      if (!habitsJson || !Array.isArray(habitsJson)) return [];
      for (var i = 0; i < habitsJson.length; i++) {
        var h = habitsJson[i];
        var meta = h.metadata && typeof h.metadata === "object" ? h.metadata : {};
        var objectiu = h.objectiu_vegades != null ? Number(h.objectiu_vegades) : (meta.objectiu_vegades != null ? Number(meta.objectiu_vegades) : 1);
        var ac = !!h.acabado;
        var mapped = {
          id: h.id,
          nom: h.titol || h.nom || "",
          titol: h.titol,
          icona: h.icona,
          color: h.color,
          dificultat: h.dificultat,
          categoria_id: h.categoria_id,
          categoriaId: h.categoria_id,
          recordatori: h.recordatori || meta.recordatori || "",
          momentDia: meta.moment_dia || meta.momentDia || h.moment_dia || h.momentDia || "",
          moment_dia: meta.moment_dia || "",
          frequenciaTipus: h.frequencia_tipus || meta.frequencia_tipus || "diaria",
          objectiuVegades: objectiu,
          unitat: h.unitat || meta.unitat || "vegades",
          prioritari: meta.prioritari === true,
          metadata: meta
        };
        if (ac) completats.push(mapped);
        else pendents.push(mapped);
      }
      return pendents.concat(completats);
    },
    sincronitzarHistoricDesDeRuta: async function () {
      var self = this;
      self.snapshotHistoric = null;
      self.errorSnapshotHistoric = "";
      self.gameStore.historicOverrides = null;
      var q = self.$route && self.$route.query ? self.$route.query.date : null;
      if (!q || typeof q !== "string") {
        return;
      }
      var cal = useCalendar();
      if (cal.isAfterToday(q) || !cal.parseDate(q)) {
        await navigateTo({ path: "/home", query: {} });
        return;
      }
      var d = cal.parseDate(q);
      var avui = new Date();
      var avui0 = new Date(avui.getFullYear(), avui.getMonth(), avui.getDate());
      if (d.getTime() === avui0.getTime()) {
        await navigateTo({ path: "/home", query: {} });
        return;
      }
      self.carregantSnapshotHistoric = true;
      try {
        var store = useCalendarStore();
        var snap = await store.fetchDaySnapshot(q);
        if (!snap) {
          await navigateTo({ path: "/home", query: {} });
          return;
        }
        self.snapshotHistoric = snap;
        var m = snap.mascota_json || {};
        var eco = snap.economia_json || {};
        self.gameStore.historicOverrides = {
          xpActualNivel: m.xp_actual_nivel != null ? Number(m.xp_actual_nivel) : 0,
          xpObjetivoNivel: m.xp_objetivo_nivel != null ? Number(m.xp_objetivo_nivel) : 1000,
          nivell: m.nivell != null ? Number(m.nivell) : 1,
          xpTotal: m.xp_total != null ? Number(m.xp_total) : 0,
          ratxa: m.ratxa != null ? Number(m.ratxa) : 0,
          monedes: m.monedes != null ? Number(m.monedes) : 0,
          monedesGuanyades: eco.monedes_guanyades_avui != null ? Number(eco.monedes_guanyades_avui) : 0,
          xpGuanyada: eco.xp_guanyada_avui != null ? Number(eco.xp_guanyada_avui) : 0,
          data: q,
        };
      } finally {
        self.carregantSnapshotHistoric = false;
      }
    },
    tornarHistorialCalendari: async function () {
      this.gameStore.historicOverrides = null;
      var store = useCalendarStore();
      var q = this.dataHistorialDia || (this.$route.query && this.$route.query.date);
      if (q && typeof q === "string") {
        var parts = String(q).split("-");
        if (parts.length >= 2) {
          store.selectedYear = parseInt(parts[0], 10);
          store.selectedMonth = parseInt(parts[1], 10);
        }
      }
      await navigateTo("/calendar");
    },
    anarAlCalendari: function () {
      if (this.vistaHistorialDia) return;
      navigateTo("/calendar");
    },
    anarAlInventari: function () {
      if (this.vistaHistorialDia) return;
      navigateTo("/inventari");
    },
    logout: async function() {
      await useAuthStore().logout();
      navigateTo("/auth/login");
    },
    obtenirProgres: function (habitId) {
      if (this.dataHistorialDia && this.snapshotHistoric && this.snapshotHistoric.habits_json) {
        var arr = this.snapshotHistoric.habits_json;
        for (var j = 0; j < arr.length; j++) {
          if (arr[j].id === habitId) {
            var h = arr[j];
            var meta = h.metadata && typeof h.metadata === "object" ? h.metadata : {};
            var objectiu = h.objectiu_vegades != null ? Number(h.objectiu_vegades) : (meta.objectiu_vegades != null ? Number(meta.objectiu_vegades) : 1);
            return h.acabado ? objectiu : 0;
          }
        }
        return 0;
      }
      var mapa = this.gameStore.habitProgress || {};
      return (mapa[habitId] && mapa[habitId].progress) || 0;
    },
    habitCompletatAvui: function (habitId) {
      if (this.dataHistorialDia && this.snapshotHistoric && this.snapshotHistoric.habits_json) {
        var arr2 = this.snapshotHistoric.habits_json;
        for (var k = 0; k < arr2.length; k++) {
          if (arr2[k].id === habitId) return !!arr2[k].acabado;
        }
        return false;
      }
      var mapa2 = this.gameStore.habitProgress || {};
      return !!(mapa2[habitId] && mapa2[habitId].completed_today);
    },

    /**
     * Obre el modal de progrés per a un hàbit.
     */
    obrirModalHabit: function (habit) {
      if (this.vistaHistorialDia) return;
      this.habitSeleccionat = habit;
      this.esObertModalHabit = true;
    },

    /**
     * Obre modal de detalls d'un hàbit.
     */
    obrirModalDetallsHabit: function (habit) {
      if (this.vistaHistorialDia) return;
      this.habitDetallsSeleccionat = habit;
      this.esObertModalDetalls = true;
      this.weatherContextDetalls = this.weatherGlobal || null;
    },

    /**
     * Tanca el modal de progrés.
     */
    tancarModalHabit: function () {
      this.esObertModalHabit = false;
      this.habitSeleccionat = null;
    },

    /**
     * Tanca el modal de detalls de l'hàbit.
     */
    tancarModalDetallsHabit: function () {
      this.esObertModalDetalls = false;
      this.habitDetallsSeleccionat = null;
      this.weatherContextDetalls = null;
    },
    iniciarSessioFocus: function (habit) {
      if (this.vistaHistorialDia) return;
      var habitTarget = habit || this.habitDetallsSeleccionat;
      if (!habitTarget || !habitTarget.id) {
        return;
      }
      if (this.habitCompletatAvui(habitTarget.id)) {
        this.mostrarAvis("Aquest hàbit ja està completat avui.");
        return;
      }
      this.tancarModalDetallsHabit();
      navigateTo("/focus/" + habitTarget.id);
    },

    /**
     * Tanca el modal de ratxa trencada.
     */
    tancarModalRatxa: function () {
      this.esObertModalRatxa = false;
      this.ratxaAnteriorModal = 0;
    },

    tancarModalRatxaCelebracio: function () {
      this.esObertModalRatxaCelebracio = false;
    },

    /**
     * Actualitza el progrés local al store (per feedback immediat a la UI).
     */
    actualitzarProgresLocal: function (habitId, progress, completedToday) {
      this.gameStore.actualitzarProgresHabit(habitId, progress, completedToday);
    },

    /**
     * Incrementa el progrés de l'hàbit seleccionat. Actualització optimista + enviar al backend.
     */
    incrementarHabit: function () {
      if (this.vistaHistorialDia) return;
      if (!this.habitSeleccionat) return;
      var id = this.habitSeleccionat.id;
      if (this.habitCompletatAvui(id)) return;
      var current = this.obtenirProgres(id);
      var max = this.habitSeleccionat.objectiuVegades || 1;
      if (current >= max) return;
      var habitActions = useHabitActions();
      habitActions.incrementarProgres(id, 1, max);
    },
    incrementarHabitInline: function (habit) {
      if (this.vistaHistorialDia) return;
      if (!habit) return;
      this.habitSeleccionat = habit;
      this.incrementarHabit();
    },
    completarHabitInline: function (habit) {
      if (this.vistaHistorialDia) return;
      if (!habit) return;
      var id = habit.id;
      var objectiu = habit.objectiuVegades || 1;
      this.habitSeleccionat = habit;
      var habitActions = useHabitActions();
      habitActions.confirmarCompletat(id, objectiu);
    },

    /**
     * Decrementa el progrés de l'hàbit seleccionat.
     * Si restar faria que l'hàbit deixi d'estar completat, mostra avís amb SweetAlert.
     */
    decrementarHabit: function () {
      if (this.vistaHistorialDia) return;
      if (!this.habitSeleccionat) return;
      var id = this.habitSeleccionat.id;
      var progressActual = this.progresModal;
      if (this.habitCompletatAvui(id)) return;
      if (progressActual <= 0) return;
      var max = this.habitSeleccionat.objectiuVegades || 1;
      var habitActions = useHabitActions();
      habitActions.incrementarProgres(id, -1, max);
    },
    decrementarHabitInline: function (habit) {
      if (this.vistaHistorialDia) return;
      if (!habit) return;
      this.habitSeleccionat = habit;
      this.decrementarHabit();
    },

    /**
     * Comprova si un hàbit s'està processant actualment.
     */
    comvprovarSiSestaProcessant: function (idHabit) {
      return this.procesantHabits.indexOf(idHabit) >= 0;
    },

    /**
     * Confirma la finalització de l'hàbit seleccionat.
     * Usa socket si està connectat; sinó, fallback via API.
     */
    confirmarHabit: async function () {
      var self = this;
      if (self.vistaHistorialDia) return;
      if (!this.habitSeleccionat) return;
      var habitId = this.habitSeleccionat.id;
      var objectiu = this.objectiuModal || 1;
      var habitActions = useHabitActions();
      self.procesantHabits.push(habitId);
      self.errorMissatge = "";
      try {
        await habitActions.confirmarCompletat(habitId, objectiu);
        self.tancarModalHabit();
      } catch (err) {
        console.error("Error completant hàbit:", err);
        self.errorMissatge = "Error inesperat en completar l'hàbit.";
      } finally {
        self.procesantHabits = self.procesantHabits.filter(function (id) { return id !== habitId; });
      }
      setTimeout(function () {
        self.gameStore.obtenirEstatJoc();
      }, 1200);
    },

    /**
     * Mostra avís quan l'hàbit no està completat.
     */
    mostrarAvisIncomplet: function () {
      this.mostrarAvis("Has de completar l'objectiu abans de finalitzar l'hàbit.");
    },

    /**
     * Mostra alert genèrica.
     */
    mostrarAvis: function (text) {
      this.$swal.fire({
        icon: 'info',
        title: 'Atenció',
        text: text
      });
    },
    inicialitzarSocket: function () {
      var homeSocketUi = useHomeSocketUi(this);
      this.socket = homeSocketUi.connectarSocketHome();
    },

    /**
     * Obre el modal de la ruleta (si està disponible).
     */
    obrirModalRuleta: function () {
      if (this.vistaHistorialDia) return;
      if (!this.canSpinRoulette) {
        return;
      }
      var host = this.$refs.ruletaDailySpin;
      if (host && typeof host.iniciarTirada === 'function') {
        host.iniciarTirada();
      }
    },

    /**
     * Tanca el modal de la ruleta.
     */
    tancarModalRuleta: function () {
      this.esObertModalRuleta = false;
      this.aturarSpinRuleta();
      this.ruletaProcessant = false;
    },

    /**
     * Calcula l'angle de cada segment.
     */
    obtenirAngleRuleta: function () {
      return 360 / this.ruletaPremis.length;
    },

    /**
     * Genera el gradient de la ruleta.
     */
    obtenirGradientRuleta: function () {
      var parts = [];
      var angle = this.obtenirAngleRuleta();
      var i;
      for (i = 0; i < this.ruletaPremis.length; i++) {
        var start = angle * i;
        var end = angle * (i + 1);
        var color = this.ruletaColors[i % this.ruletaColors.length];
        parts.push(color + " " + start + "deg " + end + "deg");
      }
      return "conic-gradient(" + parts.join(", ") + ")";
    },

    /**
     * Estil per a les etiquetes de la ruleta.
     */
    estilEtiquetaRuleta: function (index) {
      var angle = this.obtenirAngleRuleta();
      var rot = angle * index + angle / 2;
      return {
        transform: "rotate(" + rot + "deg) translateY(-120px) rotate(" + (-rot) + "deg)"
      };
    },

    /**
     * Envia la tirada de ruleta via socket.
     */
    tirarRuleta: function () {
      if (!this.canSpinRoulette || this.ruletaProcessant) {
        return;
      }
      if (!this.socket) {
        return;
      }
      this.ruletaProcessant = true;
      this.ruletaPremiSeleccionat = this.seleccionarPremiRuleta();
      this.iniciarSpinRuleta();
      this.enviarResultatRuleta();
      this.aterrarRuleta();
    },

    /**
     * Selecciona un premi aleatori de la ruleta.
     */
    seleccionarPremiRuleta: function () {
      if (!this.ruletaPremis || this.ruletaPremis.length === 0) {
        return null;
      }
      var index = Math.floor(Math.random() * this.ruletaPremis.length);
      return this.ruletaPremis[index];
    },

    /**
     * Envia el premi seleccionat al backend via socket.
     */
    enviarResultatRuleta: function () {
      if (!this.socket) {
        return;
      }
      if (!this.ruletaPremiSeleccionat) {
        return;
      }
      this.socket.emit("roulette_spin", {
        prize: this.ruletaPremiSeleccionat
      });
    },

    /**
     * Atura el gir continu i fa aterrar la ruleta al premi.
     */
    aterrarRuleta: function () {
      var self = this;
      if (!self.ruletaPremiSeleccionat) {
        return;
      }
      setTimeout(function () {
        self.aturarSpinRuleta();
        var angle = self.obtenirAngleRuleta();
        var index = 0;
        var i;
        for (i = 0; i < self.ruletaPremis.length; i++) {
          if (self.ruletaPremis[i].key === self.ruletaPremiSeleccionat.key) {
            index = i;
            break;
          }
        }
        var targetAngle = index * angle + angle / 2;
        var rotacioActual = self.ruletaRotacio % 360;
        if (rotacioActual < 0) {
          rotacioActual = rotacioActual + 360;
        }
        var delta = (360 - targetAngle - rotacioActual) % 360;
        if (delta < 0) {
          delta = delta + 360;
        }
        var rotacioFinal = rotacioActual + 360 * 5 + delta;
        self.ruletaRotacio = rotacioFinal;

        setTimeout(function () {
          var label;
          if (self.ruletaPremiSeleccionat && self.ruletaPremiSeleccionat.label) {
            label = self.ruletaPremiSeleccionat.label;
          } else {
            label = "...";
          }
          self.mostrarAlertaRuleta(self.$t('home.roulette_won_title'), self.$t('home.roulette_won_text', { premi: label }), "success");
        }, self.ruletaDuracioMs);
      }, 600);
    },

    /**
     * Inicia un gir continu fins rebre resultat.
     */
    iniciarSpinRuleta: function () {
      var self = this;
      self.aturarSpinRuleta();
      self.ruletaSpinActiva = true;
      self.ruletaSpinTimer = setInterval(function () {
        self.ruletaRotacio = (self.ruletaRotacio + self.ruletaSpinStepDeg) % 360;
      }, self.ruletaSpinIntervalMs);
    },

    /**
     * Atura el gir continu si està actiu.
     */
    aturarSpinRuleta: function () {
      if (this.ruletaSpinTimer) {
        clearInterval(this.ruletaSpinTimer);
        this.ruletaSpinTimer = null;
      }
      this.ruletaSpinActiva = false;
    },

    /**
     * Gestiona el resultat de la ruleta.
     */
    gestionarResultatRuleta: function (data) {
      var self = this;
      if (self.$route && self.$route.path === '/roulette') {
        return;
      }
      if (self.gameStore.ruletaAnimant) {
        return;
      }
      self.aturarSpinRuleta();
      self.ruletaProcessant = false;
      if (!data) return;
      if (data.error) {
        self.mostrarAlertaRuleta("Ruleta", data.error, "error");
        return;
      }
      self.gameStore.canSpinRoulette = false;
      if (data.ruleta_ultima_tirada !== undefined) {
        self.gameStore.ruletaUltimaTirada = data.ruleta_ultima_tirada;
      }
      self.gameStore.obtenirEstatJoc();
      var premiLabel = data.label || data.premi_text || data.premi_valor || "";
      if (premiLabel) {
        self.$swal.fire({
          icon: "success",
          title: self.$t("home.roulette_won_title") || "Enhorabona!",
          text: self.$t("home.roulette_won_text", { premi: premiLabel }) || "Has rebut " + premiLabel + "!"
        });
      }
    },

    /**
     * Mostra SweetAlert per la ruleta.
     */
    mostrarAlertaRuleta: function (titol, text, icona) {
      this.$swal.fire({
        title: titol,
        text: text,
        icon: icona || "success"
      });
    },

    /**
     * Mostra SweetAlert quan es completa un hàbit.
     */
    mostrarAlertaHabitCompletat: function () {
    },

    /**
     * Mostra el modal de celebració quan es puja de nivell.
     */
    mostrarAlertaLevelUp: function (data) {
      this.nivellLevelUpModal = data && data.nivell ? data.nivell : this.nivell;
      this.esObertModalLevelUp = true;
    },

    tancarModalLevelUp: function () {
      this.esObertModalLevelUp = false;
    },

    mostrarAlertaMissioCompletada: function () {
      var missio = this.gameStore.missioDiaria;
      this.missioTitolModal = missio && missio.titol ? missio.titol : "";
      this.missioRecompensaXp = missio && missio.recompensa_xp ? missio.recompensa_xp : 20;
      this.missioRecompensaMonedes = missio && missio.recompensa_monedes ? missio.recompensa_monedes : 10;
      this.esObertModalMissio = true;
    },
    tancarModalMissio: function () {
      this.esObertModalMissio = false;
    },
    obrirModalLogros: function () {
      if (this.vistaHistorialDia) return;
      var self = this;
      self.esObertModalLogros = true;
      self.logroStore.carregarLogros().then(function () {
      }).catch(function (err) {
        console.error("Error carregant logros al modal:", err);
      });
    },
    tancarModalLogros: function () { this.esObertModalLogros = false; },
    enviarSpinRuleta: function () {
      if (this.vistaHistorialDia) return;
      if (this.socket) {
        this.socket.emit("roulette_spin", {});
      }
    }
  }
};
</script>

<style scoped>
/* Bloc CSS vàlid: en alguns builds Docker/Vite un SFC sense <style> pot deixar descriptors antics
   i PostCSS arriba a interpretar el template com CSS (error "Unknown word $t"). */
.home-page-root {
  isolation: isolate;
}
</style>
