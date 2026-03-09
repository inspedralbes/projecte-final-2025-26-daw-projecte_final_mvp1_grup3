<template>
  <div class="relative w-full min-h-screen pb-12 overflow-y-auto">
    <!-- El header ja el proporciona el layout default.vue -->

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
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $t('home.level') }} {{ nivell }}</span>
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

    <LogrosModal
      :is-open="esObertModalLogros"
      :logros="logroStore.logros"
      @close="tancarModalLogros"
    />

    <RouletteModal
      :is-open="esObertModalRuleta"
      :can-spin="canSpinRoulette"
      @close="tancarModalRuleta"
      @spin="enviarSpinRuleta"
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
import LogrosModal from "~/components/home/LogrosModal.vue";
import RouletteModal from "~/components/home/RouletteModal.vue";
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
    LogrosModal,
    RouletteModal,
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
    unitatModal: function () { return this.habitSeleccionat ? this.habitSeleccionat.unitat || "vegades" : "vegades" }
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
     * Usa socket si està connectat; sinó, fallback via API.
     */
    confirmarHabit: async function () {
      var self = this;
      if (!this.habitSeleccionat) {
        return;
      }
      var habitId = this.habitSeleccionat.id;
      var objectiu = this.objectiuModal || 1;
      var usedApi = !this.socket || !this.socket.connected;
      var resultat = this.gameStore.completarHabit(habitId, this.socket);
      this.tancarModalHabit();
      if (resultat && typeof resultat.then === "function") {
        var ok = await resultat;
        if (ok && usedApi) {
          self.actualitzarProgresLocal(habitId, objectiu, true);
          self.mostrarAlertaHabitCompletat();
        }
        if (usedApi) {
          self.gameStore.obtenirProgresHabits().then(function (mapa) {
            if (mapa) {
              self.gameStore.habitProgress = mapa;
            }
          }).catch(function () {});
        }
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
    /**
     * Inicialitza la conexió de sockets.
     */
    inicialitzarSocket: function () {
      var self = this;
      self.socket = useNuxtApp().$socket;
      if (!self.socket) return;
      self.socket.on("roulette_result", function (payload) {
        console.log("Ruleta result rebuda:", payload);
        if (payload.success) {
          self.$swal.fire({
            icon: 'success',
            title: self.$t('home.roulette_won_title') || '¡Enhorabona!',
            text: self.$t('home.roulette_won_text', { premi: payload.premi_text || payload.premi_valor }) || 'Has guanyat un premi!'
          });
          self.gameStore.obtenirEstatJoc(); // Actualitzar canSpinRoulette i monedes
        }
      });

      self.socket.on("disconnect", function () {
        console.log("Socket desconnectat.");
      });

      self.socket.on("habit_action_confirmed", function (payload) {
        if (!payload || payload.success !== true) {
          if (payload && payload.message) {
            self.mostrarAvis(payload.message);
          }
          return;
        }
        if (payload.xp_update && typeof payload.xp_update === "object") {
          self.gameStore.actualitzarDesDeXpUpdate(payload.xp_update);
        }
        if (payload.action === "PROGRESS" && payload.progress !== undefined) {
          var habitId = payload.habit && payload.habit.id ? payload.habit.id : payload.habit_id;
          self.actualitzarProgresLocal(habitId, payload.progress, payload.completed_today);
        }
        if (payload.action === "COMPLETE") {
          console.log("[RATXA_DEBUG] habit_action_confirmed COMPLETE xp_update:", JSON.stringify(payload.xp_update));
          if (payload.habit && payload.habit.id) {
            self.actualitzarProgresLocal(payload.habit.id, self.obtenirProgres(payload.habit.id), true);
          }
          if (payload.xp_update && typeof payload.xp_update === "object") {
            self.gameStore.actualitzarDesDeXpUpdate(payload.xp_update);
          }
          self.mostrarAlertaHabitCompletat();
          var missionData = payload.mission_completed;
          if (missionData && (missionData.success === true || missionData.success === "true")) {
            self.gameStore.missioCompletada = true;
            if (missionData.missio_objectiu !== undefined) {
              self.gameStore.missioProgres = missionData.missio_objectiu;
              self.gameStore.missioObjectiu = missionData.missio_objectiu;
            }
            if (missionData.xp_update && typeof missionData.xp_update === "object") {
              self.gameStore.actualitzarDesDeXpUpdate(missionData.xp_update);
            }
            self.mostrarAlertaMissioCompletada();
          }
          // No cridar obtenirEstatJoc aquí: el payload del socket (xp_update) és la font de veritat.
          // Una petició API posterior podria sobreescriure la ratxa correcta amb dades antigues.
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
      this.$swal.fire({
        title: this.$t('home.habit_completed_title'),
        text: this.$t('home.habit_completed_text'),
        icon: "success"
      });
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
      var textMonedes = this.$t('home.confirm_undo_subtext', { xp: xp, monedes: monedes });
      if (monedesDespres < 0) {
        textMonedes += " " + this.$t('home.confirm_undo_balance', { monedes: monedesDespres });
      }

      this.$swal.fire({
        title: self.$t('home.confirm_undo_title'),
        html: "<p>" + self.$t('home.confirm_undo_text') + "</p><p class=\"mt-2 font-semibold\">" + textMonedes + "</p><p class=\"mt-2 text-sm text-gray-500\">" + self.$t('home.confirm_undo_footer') + "</p>",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: self.$t('home.confirm'),
        cancelButtonText: self.$t('home.cancel'),
        buttonsStyling: true
      }).then(function (result) {
        if (result && result.isConfirmed && typeof callbackOnConfirm === "function") {
          var habitId = self.habitSeleccionat && self.habitSeleccionat.id;
          var nouProgress = self.progresModal - 1;
          self.actualitzarProgresLocal(habitId, nouProgress, false);
          callbackOnConfirm();
        }
      });
    },
    /**
     * Mostra SweetAlert quan es puja de nivell.
     */
    mostrarAlertaLevelUp: function (data) {
      this.$swal.fire({
        title: this.$t('home.level_up_title'),
        text: this.$t('home.level_up_text', { nivell: data && data.nivell ? data.nivell : this.nivell, bonus: data && data.bonus_monedes ? data.bonus_monedes : 10 }),
        icon: "success"
      });
    },

    /**
     * Mostra SweetAlert quan la missió diària s'ha completat.
     */
    mostrarAlertaMissioCompletada: function () {
      this.$swal.fire({
        title: this.$t('home.mission_completed_title'),
        text: this.$t('home.mission_completed_text'),
        icon: "success"
      });
    },
    incrementarHabit: function () {
      if (!this.habitSeleccionat) return;
      var id = this.habitSeleccionat.id;
      var current = this.gameStore.habitProgress[id] ? this.gameStore.habitProgress[id].progress : 0;
      var max = this.habitSeleccionat.objectiuVegades || 1;
      if (current < max) {
        this.gameStore.habitProgress[id] = this.gameStore.habitProgress[id] || { progress: 0, completed_today: false };
        this.gameStore.habitProgress[id].progress = current + 1;
      }
    },
    decrementarHabit: function () {
      if (!this.habitSeleccionat) return;
      var id = this.habitSeleccionat.id;
      var current = this.gameStore.habitProgress[id] ? this.gameStore.habitProgress[id].progress : 0;
      if (current > 0) {
        this.gameStore.habitProgress[id].progress = current - 1;
      }
    },
    comvprovarSiSestaProcessant: function (habitId) {
      return false; // Implement correct processing logic if needed
    },
    confirmarHabit: function () {
      if (!this.habitSeleccionat) return;
      this.gameStore.confirmarHabit(this.habitSeleccionat.id, this.socket);
      this.tancarModalHabit();
    },
    obrirModalLogros: function () { this.esObertModalLogros = true; },
    tancarModalLogros: function () { this.esObertModalLogros = false; },
    obrirModalRuleta: function () { this.esObertModalRuleta = true; },
    tancarModalRuleta: function () { this.esObertModalRuleta = false; },
    enviarSpinRuleta: function () {
      if (this.socket) {
        this.socket.emit("roulette_spin", {});
      }
    },
    mostrarAvisIncomplet: function() {
      // SweetAlert logic for incomplete habit if needed
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
