<template>
  <main class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Grid Principal -->
      <div class="grid grid-cols-12 gap-6">
        <!-- COSTAT ESQUERRE: Missions i Perfil -->
        <div class="col-span-3 space-y-6">
          <!-- Targeta Missions Diàries -->
          <div
            class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-400"
          >
            <div class="flex items-center gap-2 mb-4">
              <div
                class="w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center"
              >
                <span class="text-white text-sm">✓</span>
              </div>
              <h2
                class="text-sm font-bold text-gray-800 uppercase tracking-wide"
              >
                Missions Diàries
              </h2>
            </div>

            <div class="space-y-3">
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-700 font-semibold text-sm">
                  <template v-if="missioDiaria && missioDiaria.titol">
                    {{ missioDiaria.titol }}
                  </template>
                  <template v-else>
                    Carregant...
                  </template>
                </p>
                <p class="text-2xl font-bold text-orange-500">
                  <template v-if="missioCompletada">1/1</template>
                  <template v-else>0/1</template>
                </p>
              </div>
            </div>

            <!-- Divisor -->
            <div class="h-px bg-gray-200 my-4"></div>

            <!-- Perfil Usuari -->
            <div class="text-center">
              <div
                class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 mx-auto mb-3 flex items-center justify-center"
              >
                <span class="text-3xl"></span>
              </div>
              <h3 class="font-bold text-gray-800 text-sm">Nom</h3>
              <p class="text-xs text-gray-500 mb-2">Etiqueta</p>
              <div
                class="flex justify-center items-center gap-1 text-xs text-gray-600"
              >
                <span>Lv {{ nivell }}</span>
                <div class="w-20 h-1 bg-gray-200 rounded-full overflow-hidden">
                  <div
                    class="h-1 bg-blue-500"
                    :style="{ width: percentatgeNivell + '%' }"
                  ></div>
                </div>
                <span class="text-[10px] text-gray-500">
                  {{ xpActualNivel }}/{{ xpObjetivoNivel }}
                </span>
              </div>
            </div>
          </div>

          <!-- Targeta Últims Assoliments -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3
              class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4"
            >
              Últims Assoliments
            </h3>
            <div class="flex justify-around items-center relative group min-h-[48px]">
              <template v-if="ultimsLogros.length > 0">
                <div
                  v-for="logro in ultimsLogros"
                  :key="logro.id"
                  class="w-12 h-12 rounded-full flex items-center justify-center text-lg hover:scale-110 transition shadow-inner"
                  :class="logro.obtingut ? 'bg-orange-100' : 'bg-gray-100 opacity-40'"
                  :title="logro.nom"
                >
                  {{ logro.obtingut ? '🏅' : '🔒' }}
                </div>
              </template>
              <template v-else>
                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-xs text-gray-300 border border-dashed border-gray-200">?</div>
                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-xs text-gray-300 border border-dashed border-gray-200">?</div>
                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-xs text-gray-300 border border-dashed border-gray-200">?</div>
              </template>
              
              <!-- Botó per veure tots els logros -->
              <button 
                @click="obrirModalLogros"
                class="absolute -right-2 -bottom-2 w-8 h-8 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 hover:scale-110 transition flex items-center justify-center font-bold text-xl"
                title="Veure tots els logros"
              >
                +
              </button>
            </div>
          </div>

          <!-- Icona Ruleta Diària -->
          <div
            class="bg-white rounded-2xl shadow-lg p-4 flex items-center justify-center cursor-pointer transition"
            :class="classeIconaRuleta"
            @click="obrirModalRuleta"
            title="Ruleta diària"
          >
            <span class="text-sm font-bold">RULETA</span>
          </div>
        </div>

        <!-- CENTRE: El teu Monstre -->
        <div class="col-span-6 space-y-6">
          <!-- Targeta El teu Monstre -->
          <div
            class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative"
          >
            <!-- Contingut -->
            <div class="relative z-10 w-full">
              <div class="flex items-center justify-between w-full mb-4">
                <div>
                  <h2 class="text-lg font-bold text-gray-800">
                    EL TEU MONSTRE
                  </h2>
                  <p class="text-xs text-gray-500">Lv 1</p>
                </div>
                <div class="text-right">
                  <p class="text-2xl font-bold">Ratxa: {{ ratxa }}</p>
                  <p class="text-xs font-bold text-yellow-600 mb-1">Ratxa Màxima: {{ ratxaMaxima }}</p>
                  <p class="text-sm text-green-600">XP Total: {{ xpTotal }}</p>
                  <p class="text-sm text-amber-600">Monedes: {{ monedes }}</p>
                </div>
              </div>

              <!-- Imatge Monstre -->
              <div
                class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative"
                :style="estilFons"
                style="min-width: 450px"
              >
                <div
                  class="w-40 h-40 rounded-xl flex items-center justify-center mb-6 overflow-hidden mx-auto"
                >
                  <img
                    v-if="imatgeMascota"
                    :src="imatgeMascota"
                    alt="El teu monstre"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
              <p class="text-center text-gray-600 text-sm mt-4">
                ¡Ho estàs fent genial!
              </p>
            </div>
          </div>
        </div>

        <!-- COSTAT DRET: Hàbits -->
        <div class="col-span-3 space-y-6">
          <!-- Capçalera Hàbits -->
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">HÀBITS</h2>
            <NuxtLink
              to="/habits"
              class="text-blue-500 text-xs font-semibold hover:underline"
              >VEURE TOT</NuxtLink
            >
          </div>

          <!-- Llista d'Hàbits -->
          <div class="space-y-3">
            <!-- Missatge d'error -->
            <div
              v-if="errorMissatge"
              class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
            >
              <span class="block sm:inline">{{ errorMissatge }}</span>
              <button
                @click="errorMissatge = ''"
                class="absolute top-0 bottom-0 right-0 px-4 py-3"
              >
                ✕
              </button>
            </div>

            <!-- Estat de càrrega -->
            <div
              v-if="estaCarregantHabits"
              class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-center"
            >
              <span>Carregant hàbits...</span>
            </div>

            <!-- Estat buit -->
            <div
              v-else-if="habitsDelDia.length === 0"
              class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-3 rounded text-center"
            >
              <span>No hi ha hàbits disponibles</span>
            </div>

            <!-- Llista d'hàbits -->
            <template v-else>
              <div
                v-for="hàbit in habitsDelDia"
                :key="hàbit.id"
                class="bg-white rounded-lg p-4 shadow flex items-center justify-between transition-all hover:shadow-md"
              >
                <div class="flex-1 mr-3">
                  <p class="font-semibold text-gray-800">{{ hàbit.nom }}</p>
                  <p class="text-xs text-gray-500 truncate">
                    {{ hàbit.descripcio }} • +{{ hàbit.recompensaXP }} XP
                  </p>
                  <p class="text-xs text-blue-600 font-semibold">
                    {{ obtenirProgres(hàbit.id) }}/{{ hàbit.objectiuVegades || 1 }}
                  </p>
                  <p
                    v-if="habitCompletatAvui(hàbit.id)"
                    class="text-xs text-green-600 font-semibold"
                  >
                    ✓ Completat
                  </p>
                </div>
                <button
                  @click="obrirModalHabit(hàbit)"
                  :disabled="comvprovarSiSestaProcessant(hàbit.id)"
                  class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed min-w-[110px]"
                >
                  <span v-if="comvprovarSiSestaProcessant(hàbit.id)">...</span>
                  <span v-else>Progrés</span>
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

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

    <!-- MODAL DE RULETA DIARIA -->
    <div v-if="esObertModalRuleta" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancarModalRuleta"></div>

      <div class="bg-white rounded-3xl w-full max-w-xl p-6 shadow-2xl relative">
        <button
          @click="tancarModalRuleta"
          class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600"
        >
          <span class="text-2xl">×</span>
        </button>

        <div class="flex flex-col items-center gap-4">
          <h2 class="text-lg font-bold text-gray-800">Ruleta diària</h2>
          <p class="text-xs text-gray-500">Fes click a la ruleta per tirar</p>

          <div class="relative">
            <div class="roulette-pointer"></div>
            <div
              class="roulette-wheel"
              :class="classeRuleta"
              :style="estilRuleta"
              @click="tirarRuleta"
            >
              <div
                v-for="(premi, index) in ruletaPremis"
                :key="premi.key"
                class="roulette-label"
                :style="estilEtiquetaRuleta(index)"
              >
                {{ premi.label }}
              </div>
            </div>
          </div>

          <p v-if="!canSpinRoulette" class="text-xs text-gray-400">
            Ruleta desactibada fins demà.
          </p>
        </div>
      </div>
    </div>

    <!-- MODAL DE LOGROS (Achivements) -->
    <div v-if="esObertModalLogros" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancarModalLogros"></div>
      
      <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[85vh] overflow-hidden shadow-2xl relative animate-in fade-in zoom-in duration-200 flex flex-col">
        <!-- Capçalera Modal -->
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-xl">
              🏆
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">Tots els Logros</h2>
              <p class="text-xs text-gray-500">Descobreix nous reptes i medalles</p>
            </div>
          </div>
          <button @click="tancarModalLogros" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600">
            <span class="text-2xl">×</span>
          </button>
        </div>

        <!-- Contingut Modal (Bento Grid) -->
        <div class="p-8 overflow-y-auto bg-gray-50/50 flex-1">
          <div v-if="logroStore.loading" class="flex flex-col items-center justify-center py-20 gap-4">
            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="text-gray-500 font-medium">Carregant la teva vitrina...</p>
          </div>

          <div v-else-if="logrosFiltrats.length === 0" class="text-center py-20 text-gray-400">
            <p class="text-4xl mb-4">🏜️</p>
            <p class="font-medium text-lg">Encara no hi ha logros disponibles.</p>
            <p class="text-sm">Torna més tard per veure noves missions!</p>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="logro in logrosFiltrats" 
              :key="logro.id"
              class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden"
              :class="{'opacity-60': !logro.obtingut}"
            >
              <!-- Icona/Medalla -->
              <div class="flex items-start justify-between mb-4">
                <div 
                  class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shadow-inner transition-transform group-hover:scale-110"
                  :class="logro.obtingut ? 'bg-gradient-to-br from-yellow-400 to-orange-500 text-white' : 'bg-gray-100 text-gray-400'"
                >
                  {{ logro.obtingut ? '🏅' : '🔒' }}
                </div>
                <div v-if="logro.obtingut" class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">
                  Desbloquejat
                </div>
              </div>

              <!-- Info -->
              <h4 class="font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">{{ logro.nom }}</h4>
              <p class="text-xs text-gray-500 leading-relaxed mb-4">{{ logro.descripcio }}</p>

              <!-- Progress (Simulat si ho requereix el tipus de logro) -->
              <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ logro.tipus || 'Especial' }}</span>
                <span v-if="logro.data_obtencio" class="text-[10px] text-gray-400">{{ logro.data_obtencio }}</span>
              </div>

              <!-- Efecte de fons per als desbloquejats -->
              <div v-if="logro.obtingut" class="absolute -right-4 -bottom-4 w-24 h-24 bg-yellow-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity -z-10"></div>
            </div>
          </div>
        </div>

        <!-- Peu Modal -->
        <div class="p-4 border-t border-gray-100 bg-white text-center">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-[0.2em]">Loopy Achivements System</p>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { useGameStore } from "~/stores/gameStore.js";
import { useLogroStore } from "~/stores/useLogroStore.js";
import HabitProgressModal from "~/components/home/HabitProgressModal.vue";
import StreakBrokenModal from "~/components/home/StreakBrokenModal.vue";
import bosqueImg from "~/assets/img/Bosque.png";
import mascotaImg from "~/assets/img/Mascota.png";

/**
 * Pàgina principal del joc (Home).
 * Gestiona el visualitzador del monstre i la llista d'hàbits diaris.
 */
export default {
  components: {
    HabitProgressModal: HabitProgressModal,
    StreakBrokenModal: StreakBrokenModal
  },
  /**
   * Configuració de l'estat local.
   */
  data: function () {
    return {
      socket: null,
      procesantHabits: [],
      estaCarregantHabits: false,
      errorMissatge: "",
      imatgeMascota: mascotaImg,
      esObertModalLogros: false,
      esObertModalRuleta: false,
      esObertModalHabit: false,
      esObertModalRatxa: false,
      ratxaAnteriorModal: 0,
      habitSeleccionat: null,
      ruletaProcessant: false,
      ruletaSpinActiva: false,
      ruletaSpinTimer: null,
      ruletaSpinIntervalMs: 120,
      ruletaSpinStepDeg: 24,
      ruletaRotacio: 0,
      ruletaDuracioMs: 4000,
      ruletaPremiSeleccionat: null,
      ruletaPremis: [
        { key: "xp_50", type: "xp", amount: 50, label: "50 XP" },
        { key: "xp_150", type: "xp", amount: 150, label: "150 XP" },
        { key: "xp_500", type: "xp", amount: 500, label: "500 XP" },
        { key: "coins_1", type: "coins", amount: 1, label: "1 moneda" },
        { key: "coins_5", type: "coins", amount: 5, label: "5 monedes" },
        { key: "coins_10", type: "coins", amount: 10, label: "10 monedes" },
        { key: "shop_item", type: "shop_item", amount: null, label: "Objecte botiga" }
      ],
      ruletaColors: [
        "#fde68a",
        "#bfdbfe",
        "#fecaca",
        "#bbf7d0",
        "#e9d5ff",
        "#fed7aa",
        "#c7d2fe"
      ],
      estilFons: {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      }
    };
  },

  /**
   * Propietats computades.
   */
  computed: {
    gameStore: function () {
      return useGameStore();
    },
    ratxa: function () {
      return this.gameStore.ratxa;
    },
    ratxaMaxima: function () {
      return this.gameStore.ratxaMaxima;
    },
    xpTotal: function () {
      return this.gameStore.xpTotal;
    },
    nivell: function () {
      return this.gameStore.nivell || 1;
    },
    xpActualNivel: function () {
      return this.gameStore.xpActualNivel || 0;
    },
    xpObjetivoNivel: function () {
      return this.gameStore.xpObjetivoNivel || 1000;
    },
    percentatgeNivell: function () {
      if (!this.xpObjetivoNivel || this.xpObjetivoNivel <= 0) {
        return 0;
      }
      var percent = (this.xpActualNivel / this.xpObjetivoNivel) * 100;
      if (percent > 100) {
        percent = 100;
      }
      if (percent < 0) {
        percent = 0;
      }
      return Math.round(percent);
    },
    habits: function () {
      return this.gameStore.habits;
    },
    habitsDelDia: function () {
      var llista = this.habits || [];
      var idxDia = this.obtenirIndexDiaActual();
      var filtrats = [];
      var i;
      for (i = 0; i < llista.length; i++) {
        var dies = llista[i].diesSetmana;
        if (!Array.isArray(dies) || dies.length < 7) {
          filtrats.push(llista[i]);
        } else if (dies[idxDia] === true) {
          filtrats.push(llista[i]);
        }
      }
      return filtrats;
    },
    logroStore: function () {
        return useLogroStore();
    },
    logrosFiltrats: function () {
      return this.logroStore.logros;
    },
    ultimsLogros: function () {
      var obtinguts = [];
      var i;
      var l = this.logroStore.logros;
      for (i = 0; i < l.length; i++) {
        if (l[i].obtingut) {
          obtinguts.push(l[i]);
        }
      }
      // Si no n'hi ha cap de desbloquejat, mostrem els 3 primers com a 'bloquejats' o simplement els 3 primers
      if (obtinguts.length === 0) {
        return l.slice(0, 3);
      }
      return obtinguts.slice(-3);
    },
    missioDiaria: function () {
      return this.gameStore.missioDiaria;
    },
    missioCompletada: function () {
      return this.gameStore.missioCompletada;
    },
    monedes: function () {
      return this.gameStore.monedes;
    },
    canSpinRoulette: function () {
      return this.gameStore.canSpinRoulette;
    },
    ruletaUltimaTirada: function () {
      return this.gameStore.ruletaUltimaTirada;
    },
    classeIconaRuleta: function () {
      if (this.canSpinRoulette) {
        return "hover:shadow-xl";
      }
      return "grayscale opacity-60 cursor-not-allowed";
    },
    classeRuleta: function () {
      if (!this.canSpinRoulette || this.ruletaProcessant) {
        return "roulette-disabled";
      }
      return "";
    },
    estilRuleta: function () {
      var transicio;
      if (this.ruletaSpinActiva) {
        transicio = "transform 0.15s linear";
      } else {
        transicio = "transform " + (this.ruletaDuracioMs / 1000) + "s cubic-bezier(0.2, 0.8, 0.2, 1)";
      }
      return {
        transform: "rotate(" + this.ruletaRotacio + "deg)",
        transition: transicio,
        background: this.obtenirGradientRuleta()
      };
    },
    progresModal: function () {
      if (!this.habitSeleccionat) {
        return 0;
      }
      return this.obtenirProgres(this.habitSeleccionat.id);
    },
    objectiuModal: function () {
      if (!this.habitSeleccionat) {
        return 1;
      }
      return this.habitSeleccionat.objectiuVegades || 1;
    },
    unitatModal: function () {
      if (!this.habitSeleccionat) {
        return "vegades";
      }
      return this.habitSeleccionat.unitat || "vegades";
    }
  },

  /**
   * Inicialització del component.
   */
  mounted: function () {
    var self = this;
    
    // A. Assignar usuari des de l'authStore
    self.gameStore.sincronitzarUsuariId();

    // B. Carregar dades inicials de l'hàbit i estat
    self.estaCarregantHabits = true;
    Promise.all([
      self.gameStore.obtenirHabitos(),
      self.gameStore.obtenirProgresHabits(),
      self.gameStore.obtenirEstatJoc(),
      self.logroStore.carregarLogros()
    ])
    .then(function() {
        console.log("✅ Dades carregades correctament (Incloent Logros)");
    })
    .catch(function(error) {
        console.error("❌ Error carregant dades:", error);
        self.errorMissatge = "Error al carregar la informació del servidor.";
    })
    .finally(function() {
        self.estaCarregantHabits = false;
    });

    // C. Conectar Sockets
    self.inicialitzarSocket();
  },

  /**
   * Neteja abans de destruir el component.
   */
  beforeUnmount: function () {
    // El socket global es gestionat pel plugin, no el tanquem aquí
  },

  methods: {
    /**
     * Retorna l'índex del dia actual (0 = Dilluns ... 6 = Diumenge).
     */
    obtenirIndexDiaActual: function () {
      var avui = new Date();
      var dia = avui.getDay(); // 0 = Diumenge ... 6 = Dissabte
      return (dia + 6) % 7;
    },

    /**
     * Retorna progrés d'un hàbit.
     */
    obtenirProgres: function (habitId) {
      var mapa = this.gameStore.habitProgress || {};
      if (mapa[habitId]) {
        return mapa[habitId].progress || 0;
      }
      return 0;
    },

    /**
     * Retorna si l'hàbit està completat avui.
     */
    habitCompletatAvui: function (habitId) {
      var mapa = this.gameStore.habitProgress || {};
      if (mapa[habitId]) {
        return !!mapa[habitId].completed_today;
      }
      return false;
    },

    /**
     * Actualitza el progrés local al store.
     */
    actualitzarProgresLocal: function (habitId, progress, completed) {
      if (!habitId) {
        return;
      }
      var mapa = this.gameStore.habitProgress || {};
      if (!mapa[habitId]) {
        mapa[habitId] = { progress: 0, completed_today: false };
      }
      if (typeof progress === "number") {
        mapa[habitId].progress = progress;
      }
      if (typeof completed !== "undefined") {
        mapa[habitId].completed_today = !!completed;
      }
      this.gameStore.habitProgress = mapa;
    },

    /**
     * Obre el modal de progrés per a un hàbit.
     */
    obrirModalHabit: function (habit) {
      this.habitSeleccionat = habit;
      this.esObertModalHabit = true;
    },

    /**
     * Tanca el modal de progrés.
     */
    tancarModalHabit: function () {
      this.esObertModalHabit = false;
      this.habitSeleccionat = null;
    },

    /**
     * Tanca el modal de ratxa trencada.
     */
    tancarModalRatxa: function () {
      this.esObertModalRatxa = false;
      this.ratxaAnteriorModal = 0;
    },

    /**
     * Incrementa el progrés de l'hàbit seleccionat.
     */
    incrementarHabit: function () {
      if (!this.habitSeleccionat || !this.socket) {
        return;
      }
      this.gameStore.enviarProgresHabit(this.habitSeleccionat.id, 1, this.socket);
    },

    /**
     * Decrementa el progrés de l'hàbit seleccionat.
     * Si restar faria que l'hàbit deixi d'estar completat, mostra avís amb SweetAlert.
     */
    decrementarHabit: function () {
      if (!this.habitSeleccionat || !this.socket) {
        return;
      }
      var progressActual = this.progresModal;
      var objectiu = this.objectiuModal;
      var completatAvui = this.habitCompletatAvui(this.habitSeleccionat.id);
      if (completatAvui && (progressActual - 1) < objectiu) {
        this.mostrarAlertaRestarHabitCompletat(function () {
          this.gameStore.enviarProgresHabit(this.habitSeleccionat.id, -1, this.socket);
        }.bind(this));
        return;
      }
      this.gameStore.enviarProgresHabit(this.habitSeleccionat.id, -1, this.socket);
    },

    /**
     * Confirma la finalització de l'hàbit seleccionat.
     */
    confirmarHabit: function () {
      if (!this.habitSeleccionat || !this.socket) {
        return;
      }
      this.gameStore.confirmarHabit(this.habitSeleccionat.id, this.socket);
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
      if (typeof window !== "undefined" && window.alert) {
        window.alert(text);
      }
    },
    /**
     * Inicialitza la conexió de sockets.
     */
    inicialitzarSocket: function () {
      var self = this;
      var nuxtApp = useNuxtApp();
      
      // Utilitzem la instància global injectada pel plugin
      self.socket = nuxtApp.$socket;

      if (!self.socket) {
        console.error("❌ Socket global no disponible");
        return;
      }

      self.socket.on("connect", function () {
        console.log("✅ Conectat al servidor de sockets:", self.socket.id);
      });

      self.socket.on("update_xp", async function (data) {
        console.log("⭐ Recept feedback gamificació:", data);
        if (data) {
          self.gameStore.actualitzarDesDeXpUpdate(data);
        }
        try {
          await self.gameStore.obtenirEstatJoc();
        } catch (error) {
          console.error("❌ Error actualitzant estat:", error);
        }
      });

      self.socket.on("habit_action_confirmed", function (payload) {
        if (!payload || payload.success !== true) {
          if (payload && payload.message) {
            self.mostrarAvis(payload.message);
          }
          return;
        }
        if (payload.action === "PROGRESS" && payload.progress !== undefined) {
          var habitId = payload.habit && payload.habit.id ? payload.habit.id : payload.habit_id;
          self.actualitzarProgresLocal(habitId, payload.progress, payload.completed_today);
        }
        if (payload.action === "COMPLETE") {
          if (payload.habit && payload.habit.id) {
            self.actualitzarProgresLocal(payload.habit.id, self.obtenirProgres(payload.habit.id), true);
          }
          self.mostrarAlertaHabitCompletat();
        }
      });

      self.socket.on("streak_broken", function (payload) {
        var anterior = payload && payload.ratxa_anterior ? payload.ratxa_anterior : 0;
        self.ratxaAnteriorModal = anterior;
        self.esObertModalRatxa = true;
        self.gameStore.obtenirEstatJoc();
      });

      self.socket.on("level_up", function (data) {
        self.mostrarAlertaLevelUp(data);
      });

      self.socket.on("roulette_result", function (data) {
        self.gestionarResultatRuleta(data);
      });

      self.gameStore.registrarListenerMissionCompletada(self.socket, function () {
        self.mostrarAlertaMissioCompletada();
      });

      self.socket.on("disconnect", function () {
        console.log("❌ Desconectat del servidor de sockets");
      });
    },

    /**
     * Obre el modal de la ruleta (si està disponible).
     */
    obrirModalRuleta: function () {
      if (!this.canSpinRoulette) {
        return;
      }
      this.esObertModalRuleta = true;
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
            label = "un premi";
          }
          self.mostrarAlertaRuleta("Felicidades!", "Has recibido " + label + "!", "success");
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
      self.aturarSpinRuleta();
      if (!data) {
        self.ruletaProcessant = false;
        return;
      }
      if (data.error) {
        self.ruletaProcessant = false;
        self.mostrarAlertaRuleta("Ruleta", data.error, "error");
        return;
      }

      self.gameStore.canSpinRoulette = false;
      if (data.ruleta_ultima_tirada !== undefined) {
        self.gameStore.ruletaUltimaTirada = data.ruleta_ultima_tirada;
      }
      self.ruletaProcessant = false;
    },

    /**
     * Mostra SweetAlert per la ruleta.
     */
    mostrarAlertaRuleta: function (titol, text, icona) {
      var mostrarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: titol,
            text: text,
            icon: icona || "success"
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
     * Mostra SweetAlert quan es completa un hàbit.
     */
    mostrarAlertaHabitCompletat: function () {
      var mostrarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: "Felicidades!",
            text: "Has completado un hábito!",
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
     * Mostra SweetAlert quan l'usuari vol restar progrés d'un hàbit completat.
     * Adverteix que es restaran XP i monedes. Botons Confirmar i Cancel·lar.
     *
     * @param {Function} callbackOnConfirm - Cridat quan l'usuari prem Confirmar.
     */
    mostrarAlertaRestarHabitCompletat: function (callbackOnConfirm) {
      var self = this;
      var xp = this.habitSeleccionat && this.habitSeleccionat.recompensaXP ? this.habitSeleccionat.recompensaXP : 100;
      var monedes = this.habitSeleccionat && this.habitSeleccionat.recompensaMonedes ? this.habitSeleccionat.recompensaMonedes : 2;
      var monedesActuals = this.monedes || 0;
      var monedesDespres = monedesActuals - monedes;
      var textMonedes = "Se't restarien " + xp + " XP i " + monedes + " monedes.";
      if (monedesDespres < 0) {
        textMonedes = textMonedes + " El teu saldo de monedes quedaria en " + monedesDespres + ".";
      }

      var executarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: "Desfer la completació?",
            html: "<p>Si restes ara, l'hàbit deixarà d'estar completat i perdras la recompensa obtinguda.</p><p class=\"mt-2 font-semibold\">" + textMonedes + "</p><p class=\"mt-2 text-sm text-gray-500\">N'estàs segur que vols continuar?</p>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirmar",
            cancelButtonText: "Cancel·lar",
            confirmButtonColor: "#dc2626",
            cancelButtonColor: "#374151",
            customClass: {
              popup: "swal-restar-habit-popup",
              actions: "swal-restar-habit-actions",
              confirmButton: "swal-restar-habit-confirm",
              cancelButton: "swal-restar-habit-cancel"
            },
            buttonsStyling: true
          }).then(function (result) {
            if (result && result.isConfirmed && typeof callbackOnConfirm === "function") {
              var habitId = self.habitSeleccionat && self.habitSeleccionat.id;
              var nouProgress = self.progresModal - 1;
              self.actualitzarProgresLocal(habitId, nouProgress, false);
              callbackOnConfirm();
            }
          });
        }
      };

      if (typeof window !== "undefined" && window.Swal) {
        executarAlerta();
      } else if (typeof document !== "undefined") {
        var script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js";
        script.onload = executarAlerta;
        document.head.appendChild(script);
      }
    },

    /**
     * Mostra SweetAlert quan es puja de nivell.
     */
    mostrarAlertaLevelUp: function (data) {
      var nivell = data && data.nivell ? data.nivell : this.nivell;
      var bonusMonedes = data && data.bonus_monedes ? data.bonus_monedes : 10;
      var mostrarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: "Nivel aumentado!",
            text: "Has subido al nivel " + nivell + ". Has conseguido +" + bonusMonedes + " monedas.",
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
     * Mostra SweetAlert quan la missió diària s'ha completat.
     * Carrega SweetAlert2 des del CDN si encara no és disponible.
     */
    mostrarAlertaMissioCompletada: function () {
      var mostrarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: "Missió completada!",
            text: "Has completat la teva missió diària! +10 monedes i +20 XP",
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
     * Comprova si un hàbit s'està processant actualment.
     */
    comvprovarSiSestaProcessant: function (idHabit) {
      var trobat = false;
      var i;
      for (i = 0; i < this.procesantHabits.length; i++) {
        if (this.procesantHabits[i] === idHabit) {
          trobat = true;
          break;
        }
      }
      return trobat;
    },

    /**
     * Acció de completar un hàbit.
     */
    completarHabit: async function (idHabit) {
      var self = this;
      var success;

      try {
        // A. Marcar com a processant
        self.procesantHabits.push(idHabit);
        self.errorMissatge = "";

        // B. Cridar acció del store
        success = await self.gameStore.completarHabit(idHabit, self.socket);

        if (!success) {
          self.errorMissatge = "No s'ha pogut completar l'hàbit.";
        }
      } catch (error) {
        console.error("Error completant hàbit:", error);
        self.errorMissatge = "Error inesperat en completar l'hàbit.";
      } finally {
        // C. Treure de la llista de processant (clàssic)
        var novaLlista = [];
        var j;
        for (j = 0; j < self.procesantHabits.length; j++) {
            if (self.procesantHabits[j] !== idHabit) {
                novaLlista.push(self.procesantHabits[j]);
            }
        }
        self.procesantHabits = novaLlista;
      }
    },

    /**
     * Obre el modal de logros i carrega les dades des de l'API.
     */
    obrirModalLogros: function () {
        var self = this;
        self.esObertModalLogros = true;
        
        // Carregar logros (gestionat pel company)
        self.logroStore.carregarLogros()
            .then(function() {
                console.log("🏆 Logros carregats al modal");
            })
            .catch(function(err) {
                console.error("❌ Error carregant logros al modal:", err);
            });
    },

    /**
     * Tanca el modal de logros.
     */
    tancarModalLogros: function () {
        this.esObertModalLogros = false;
    }
  }
};
</script>

<style scoped>
/* Estils locals per a la pàgina d'inici */
.roulette-wheel {
  width: 280px;
  height: 280px;
  border-radius: 50%;
  border: 6px solid #ffffff;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
  position: relative;
  cursor: pointer;
}

.roulette-disabled {
  filter: grayscale(1);
  opacity: 0.6;
  pointer-events: none;
}

.roulette-pointer {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 10px solid transparent;
  border-right: 10px solid transparent;
  border-bottom: 18px solid #111827;
  z-index: 10;
}

.roulette-label {
  position: absolute;
  top: 50%;
  left: 50%;
  transform-origin: center;
  width: 90px;
  margin-left: -45px;
  font-size: 10px;
  font-weight: 700;
  color: #111827;
  text-align: center;
  user-select: none;
  pointer-events: none;
}
</style>

<style>
/* Estils globals per als botons del SweetAlert de restar hàbit - sempre visibles */
.swal-restar-habit-popup .swal-restar-habit-confirm {
  opacity: 1 !important;
  visibility: visible !important;
  background-color: #dc2626 !important;
  color: white !important;
  border: none !important;
  padding: 0.625em 1.5em !important;
  font-weight: 600 !important;
}
.swal-restar-habit-popup .swal-restar-habit-cancel {
  opacity: 1 !important;
  visibility: visible !important;
  background-color: #374151 !important;
  color: white !important;
  border: none !important;
  padding: 0.625em 1.5em !important;
  font-weight: 600 !important;
}
</style>
