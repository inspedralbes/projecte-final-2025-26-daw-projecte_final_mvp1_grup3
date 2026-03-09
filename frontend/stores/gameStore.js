import { defineStore } from "pinia";
import { authFetch, getBaseUrl } from "~/composables/useApi.js";
import { useHabitStore } from "./useHabitStore.js";
import {
  mapGameStateFromApi,
  mapHabitProgressListToMap
} from "~/utils/mappers/apiMappers.js";

// Constants de configuració
var TEMPS_ESPERA_MS = 5000;

/**
 * Store principal del joc per gestionar el progrés de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useGameStore = defineStore("game", {
  state: function () {
    return {
      userId: null,
      ratxa: 0,
      ratxaMaxima: 0,
      xpTotal: 0,
      monedes: 0,
      canSpinRoulette: false,
      ruletaUltimaTirada: null,
      nivell: 1,
      xpActualNivel: 0,
      xpObjetivoNivel: 1000,
      habitProgress: {},
      missioDiaria: null,
      missioCompletada: false,
      missioProgres: 0,
      missioObjectiu: 1,
    };
  },

  actions: {
    /**
     * Construeix una URL de l'API a partir d'un camí.
     */
    construirUrlApi: function (cami) {
      var base = getBaseUrl();
      var camiNorm = cami.indexOf('/') === 0 ? cami : '/' + cami;
      return base + camiNorm;
    },

    /**
     * Completa un hàbit i gestiona la comunicació via Socket.
     * Completa un hàbit i gestiona la comunicació via Socket.
     */
    /**
     * Envia un increment/decrement de progrés via socket.
     */
    enviarProgresHabit: function (idHabit, delta, socket) {
      if (!socket) {
        throw new Error("Socket no disponible");
      }
      socket.emit("habit_progress", {
        habit_id: idHabit,
        valor: delta,
      });
    },

    /**
     * Confirma la finalització d'un hàbit (només si està al 100%).
     */
    confirmarHabit: function (idHabit, socket) {
      if (!socket) {
        throw new Error("Socket no disponible");
      }
      socket.emit("habit_complete", {
        habit_id: idHabit,
        data: new Date().toISOString(),
      });
    },

    /**
     * Completa un hàbit (alias que emet habit_complete).
     * La ratxa i XP s'actualitzen via socket (update_xp, habit_action_confirmed).
     */
    completarHabit: function (idHabit, socket) {
      if (!socket) {
        return Promise.resolve(false);
      }
      this.confirmarHabit(idHabit, socket);
      return Promise.resolve(true);
    },

    /**
     * Actualitza la ratxa localment.
     */
    actualitzarRatxa: function (novaRatxa) {
      this.ratxa = novaRatxa;
    },

    /**
     * Actualitza l'XP localment.
     */
    actualitzarXP: function (xp) {
      this.xpTotal = xp;
    },

    /**
     * Actualitza l'estat del joc des del payload xp_update rebut per socket
     * (habit completat, ruleta, etc.).
     * @param {Object} dades - { xp_total, ratxa_actual, ratxa_maxima, monedes }
     */
    actualitzarDesDeXpUpdate: function (dades) {
      if (!dades) {
        return;
      }
      if (dades.xp_total !== undefined) {
        this.xpTotal = dades.xp_total;
      }
      if (dades.ratxa_actual !== undefined) {
        this.ratxa = dades.ratxa_actual;
      }
      if (dades.ratxa_maxima !== undefined) {
        this.ratxaMaxima = dades.ratxa_maxima;
      }
      if (dades.monedes !== undefined) {
        this.monedes = dades.monedes;
      }
    },

    /**
     * Estableix l'ID de l'usuari (des del authStore).
     */
    assignarUsuariId: function (id) {
      this.userId = id;
    },

    /**
     * Sincronitza usuariId des de l'authStore.
     */
    sincronitzarUsuariId: function () {
      var authStore = useAuthStore();
      if (authStore.user && authStore.user.id) {
        this.userId = authStore.user.id;
      }
    },

    /**
     * Estableix el nivell actual.
     */
    assignarNivell: function (nouNivell) {
      this.nivell = nouNivell;
    },

    /**
     * Registra un listener per a l'event de missió completada.
     */
    registrarListenerMissionCompletada: function (socket, callback) {
      var self = this;
      if (socket) {
        socket.on("mission_completed", function (data) {
          console.log("Missio completada detectada per socket", data);
          self.missioCompletada = true;
          if (data && data.missio_objectiu !== undefined) {
            self.missioProgres = data.missio_objectiu;
            self.missioObjectiu = data.missio_objectiu;
          } else {
            self.missioProgres = 1;
            self.missioObjectiu = 1;
          }
          if (data && data.xp_update && typeof data.xp_update === "object") {
            self.actualitzarDesDeXpUpdate(data.xp_update);
          }
          if (typeof callback === "function") {
            callback(data);
          }
        });
      }
    },

    /**
     * Elimina el listener de missió completada.
     */
    desregistrarListenerMissionCompletada: function (socket) {
      if (socket) {
        socket.off("mission_completed");
      }
    },
    /**
     * Obté els hàbits des de l'API de Laravel.
     */
    obtenirHabitos: async function () {
      var self = this;
      var url;
      var resposta;
      var dadesBrutes;
      var llistaHabits;
      var h;
      var mapejats = [];
      var i;

      try {
        url = self.construirUrlApi("/api/habits");
        resposta = await authFetch(url, {
          mode: "cors"
        });

        if (!resposta.ok) {
          throw new Error("Error en obtenir hàbits");
        }

        dadesBrutes = await resposta.json();

        if (Array.isArray(dadesBrutes)) {
          llistaHabits = dadesBrutes;
        } else {
          llistaHabits = dadesBrutes.data || [];
        }

        var habitStore = useHabitStore();
        habitStore.establirHabitsDesDeApi(llistaHabits);
        return habitStore.habits;
      } catch (error) {
        console.error("Error fetching habits:", error);
        useHabitStore().establirHabitsDesDeApi([]);
        return [];
      }
    },

    /**
     * Obté l'estat del joc (XP, Ratxa) des de l'API de Laravel.
     * Obté l'estat del joc (XP, Ratxa) des de l'API de Laravel.
     */
    obtenirEstatJoc: async function () {
      var self = this;
      var url;
      var resposta;
      var dades;

      try {
        url = self.construirUrlApi("/api/game-state");
        resposta = await authFetch(url, {
          mode: "cors"
        });
        if (!resposta.ok) {
          throw new Error("Error en obtenir estat");
        }

        dades = await resposta.json();
        if (dades) {
          var gs = mapGameStateFromApi(dades);
          if (gs) {
            if (gs.xp_total !== undefined) self.xpTotal = gs.xp_total;
            if (gs.nivell !== undefined) self.nivell = gs.nivell;
            if (gs.xp_actual_nivel !== undefined) self.xpActualNivel = gs.xp_actual_nivel;
            if (gs.xp_objetivo_nivel !== undefined) self.xpObjetivoNivel = gs.xp_objetivo_nivel;
            if (gs.ratxa_actual !== undefined) self.ratxa = gs.ratxa_actual;
            if (gs.ratxa_maxima !== undefined) self.ratxaMaxima = gs.ratxa_maxima;
            if (gs.monedes !== undefined) self.monedes = gs.monedes;
            if (gs.can_spin_roulette !== undefined) self.canSpinRoulette = gs.can_spin_roulette;
            if (gs.ruleta_ultima_tirada !== undefined) self.ruletaUltimaTirada = gs.ruleta_ultima_tirada;
            if (gs.missio_diaria !== undefined) self.missioDiaria = gs.missio_diaria;
            if (gs.missio_completada !== undefined) self.missioCompletada = gs.missio_completada;
            if (gs.missio_progres !== undefined) self.missioProgres = gs.missio_progres;
            if (gs.missio_objectiu !== undefined) self.missioObjectiu = gs.missio_objectiu;
          }
        }
        return dades;
      } catch (error) {
        console.error("Error fetching game-state:", error);
        return null;
      }
    },

    /**
     * Carrega totes les dades de la home des del endpoint consolidat /api/user/home.
     * Centralitza game_state, habits, progress i logros en una sola petició.
     */
    carregarDadesHome: async function () {
      var self = this;
      var url;
      var resposta;
      var dades;
      var gs;
      var h;
      var hp;
      var mapejats = [];
      var mapaProgress = {};
      var i;

      try {
        url = self.construirUrlApi("/api/user/home");
        resposta = await authFetch(url, { mode: "cors" });
        if (!resposta.ok) {
          throw new Error("Error en carregar dades home");
        }
        dades = await resposta.json();

        if (!dades) {
          return null;
        }

        /* Laravel pot retornar les dades dins d'un wrapper "data" */
        if (dades.data && typeof dades.data === "object") {
          dades = dades.data;
        }

        gs = dades.game_state || {};
        if (gs) {
          var gsMap = mapGameStateFromApi(gs);
          if (gsMap.xp_total !== undefined) self.xpTotal = gsMap.xp_total;
          if (gsMap.nivell !== undefined) self.nivell = gsMap.nivell;
          if (gsMap.xp_actual_nivel !== undefined) self.xpActualNivel = gsMap.xp_actual_nivel;
          if (gsMap.xp_objetivo_nivel !== undefined) self.xpObjetivoNivel = gsMap.xp_objetivo_nivel;
          if (gsMap.ratxa_actual !== undefined) self.ratxa = gsMap.ratxa_actual;
          if (gsMap.ratxa_maxima !== undefined) self.ratxaMaxima = gsMap.ratxa_maxima;
          if (gsMap.monedes !== undefined) self.monedes = gsMap.monedes;
          if (gsMap.can_spin_roulette !== undefined) self.canSpinRoulette = gsMap.can_spin_roulette;
          if (gsMap.ruleta_ultima_tirada !== undefined) self.ruletaUltimaTirada = gsMap.ruleta_ultima_tirada;
          if (gsMap.missio_diaria !== undefined) self.missioDiaria = gsMap.missio_diaria;
          if (gsMap.missio_completada !== undefined) self.missioCompletada = gsMap.missio_completada;
          if (gsMap.missio_progres !== undefined) self.missioProgres = gsMap.missio_progres;
          if (gsMap.missio_objectiu !== undefined) self.missioObjectiu = gsMap.missio_objectiu;
        }

        h = dades.habits || [];
        var habitStore = useHabitStore();
        habitStore.establirHabitsDesDeApi(h);

        hp = dades.habit_progress || [];
        self.habitProgress = mapHabitProgressListToMap(hp);

        return dades;
      } catch (error) {
        console.error("Error carregant dades home:", error);
        return null;
      }
    },

    /**
     * Carrega el progrés d'avui per a tots els hàbits.
     */
    obtenirProgresHabits: async function () {
      var self = this;
      var url;
      var resposta;
      var dades;
      try {
        url = self.construirUrlApi("/api/habits/progress");
        resposta = await authFetch(url, {
          mode: "cors"
        });
        if (!resposta.ok) {
          throw new Error("Error en obtenir progrés");
        }

        dades = await resposta.json();
        if (Array.isArray(dades)) {
          self.habitProgress = mapHabitProgressListToMap(dades);
        }
        return self.habitProgress;
      } catch (error) {
        console.error("Error fetching progress:", error);
        return {};
      }
    },
  },
});