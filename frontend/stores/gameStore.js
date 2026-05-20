/**
 * Modul JavaScript ES5: gameStore.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { defineStore } from "pinia";
import { authFetch, getBaseUrl } from "~/composables/useApi.js";
import { useHabitStore } from "./useHabitStore.js";
import {
  mapGameStateFromApi,
  mapHabitProgressListToMap
} from "~/utils/mappers/apiMappers.js";
import {
  carregarCosmeticsDesDeStorage,
  desarCosmeticsAStorage,
} from "~/utils/cosmeticsStorage.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

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
      /** ID d'hàbit per disparar animació de completat (watch a HomeHabitsSection). */
      habitAnimacioCompletatId: null,
      missioDiaria: null,
      missioCompletada: false,
      missioProgres: 0,
      missioObjectiu: 1,
      monstre_tipus: null,
      /** Skin equipada (p.ex. gorra_monster); prové de game_state /api. */
      skinKey: null,
      /** Fons equipat (p.ex. fons_platja); prové de game_state /api. */
      fonsKey: null,
      /** True quan s'ha hidratat cache o rebut game_state del backend. */
      cosmeticsReady: false,
      streakIncrementedAvui: false,
      /** Bloqueja xp_update mentre la ruleta gira (espera l'animació). */
      ruletaAnimant: false,
      xpUpdatePendent: null,
      historicOverrides: null,
      /** Cosmètics del dia històric (calendari → home?date=). */
      historicCosmetics: null,
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
     * Aplica camps de game_state al store (XP, ratxa, cosmetics, etc.).
     */
    aplicarGameStateDesDeMap: function (gs) {
      if (!gs) {
        return;
      }
      if (gs.xp_total !== undefined) this.xpTotal = gs.xp_total;
      if (gs.nivell !== undefined) this.nivell = gs.nivell;
      if (gs.xp_actual_nivel !== undefined) this.xpActualNivel = gs.xp_actual_nivel;
      if (gs.xp_objetivo_nivel !== undefined) this.xpObjetivoNivel = gs.xp_objetivo_nivel;
      if (gs.ratxa_actual !== undefined) this.ratxa = gs.ratxa_actual;
      if (gs.ratxa_maxima !== undefined) this.ratxaMaxima = gs.ratxa_maxima;
      if (gs.monedes !== undefined) this.monedes = gs.monedes;
      if (gs.can_spin_roulette !== undefined) this.canSpinRoulette = gs.can_spin_roulette;
      if (gs.ruleta_ultima_tirada !== undefined) this.ruletaUltimaTirada = gs.ruleta_ultima_tirada;
      if (gs.missio_diaria !== undefined) this.missioDiaria = gs.missio_diaria;
      if (gs.missio_completada !== undefined) this.missioCompletada = gs.missio_completada;
      if (gs.missio_progres !== undefined) this.missioProgres = gs.missio_progres;
      if (gs.missio_objectiu !== undefined) this.missioObjectiu = gs.missio_objectiu;
      if (gs.monstre_tipus !== undefined) this.monstre_tipus = gs.monstre_tipus;
      if (gs.streak_incremented !== undefined) this.streakIncrementedAvui = !!gs.streak_incremented;
      if (gs.skin_key !== undefined) {
        this.skinKey = gs.skin_key;
      }
      if (gs.fons_key !== undefined) {
        this.fonsKey = gs.fons_key;
      }
      this.cosmeticsReady = true;
      this.persistirCosmetics();
    },

    /**
     * Hidrata skin/fons des del cache local (síncron, abans del fetch API).
     */
    hidratarCosmeticsDesDeStorage: function () {
      var authStore = useAuthStore();
      var usuari = authStore.user;
      if (!usuari || usuari.id == null) {
        return;
      }
      var cache = carregarCosmeticsDesDeStorage(usuari.id);
      if (!cache) {
        return;
      }
      if (cache.skinKey !== undefined) {
        this.skinKey = cache.skinKey;
      }
      if (cache.fonsKey !== undefined) {
        this.fonsKey = cache.fonsKey;
      }
    },

    /**
     * Desa skin/fons al localStorage per al refresh sense flash.
     */
    persistirCosmetics: function () {
      var authStore = useAuthStore();
      var usuari = authStore.user;
      if (!usuari || usuari.id == null) {
        return;
      }
      desarCosmeticsAStorage(usuari.id, this.skinKey, this.fonsKey);
    },

    /**
     * Actualitza skin/fons equipats (p.ex. després d'equipar a la botiga).
     */
    establirCosmeticsEquipats: function (skinKey, fonsKey) {
      if (skinKey !== undefined) {
        this.skinKey = skinKey;
      }
      if (fonsKey !== undefined) {
        this.fonsKey = fonsKey;
      }
      this.cosmeticsReady = true;
      this.persistirCosmetics();
    },

    /**
     * Completa un hàbit i gestiona la comunicació via Socket.
     * Completa un hàbit i gestiona la comunicació via Socket.
     */
    /**
     * Actualitza progrés local d'un hàbit (optimistic UI / reconciliació).
     */
    actualitzarProgresHabit: function (habitId, progress, completedToday) {
      if (!habitId) {
        return;
      }
      var mapa = this.habitProgress || {};
      mapa[habitId] = {
        progress: progress,
        completed_today: !!completedToday
      };
      this.habitProgress = Object.assign({}, mapa);
    },

    /**
     * Llegeix el valor de progrés actual d'un hàbit.
     */
    obtenirProgresValor: function (habitId) {
      var mapa = this.habitProgress || {};
      if (mapa[habitId]) {
        return mapa[habitId].progress || 0;
      }
      return 0;
    },

    /**
     * Dispara l'animació de tick verd a la targeta de l'hàbit.
     */
    marcarAnimacioHabitCompletat: function (habitId) {
      var self = this;
      if (!habitId) {
        return;
      }
      self.habitAnimacioCompletatId = habitId;
      setTimeout(function () {
        if (self.habitAnimacioCompletatId === habitId) {
          self.habitAnimacioCompletatId = null;
        }
      }, 2200);
    },

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
     * Completa un hàbit via socket o API (fallback).
     * Retorna Promise que resol amb true si s'ha enviat, false si no.
     */
    completarHabit: function (idHabit, socket) {
      var self = this;
      if (socket && socket.connected) {
        this.confirmarHabit(idHabit, socket);
        return Promise.resolve(true);
      }
      return this.completarHabitViaApi(idHabit);
    },

    /**
     * Fallback: completa un hàbit via API quan el socket no està connectat.
     * Actualitza el store amb xp_update de la resposta.
     */
    completarHabitViaApi: async function (idHabit) {
      var self = this;
      var url = this.construirUrlApi("/api/habits/complete");
      try {
        var resposta = await authFetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            habit_id: idHabit,
            data: new Date().toISOString()
          }),
          mode: "cors"
        });
        var dades = await resposta.json();
        if (dades.success === true) {
          if (dades.xp_update) {
            self.actualitzarDesDeXpUpdate(dades.xp_update);
          }
          if (dades.mission_completed && dades.mission_completed.success) {
            self.missioCompletada = true;
            if (dades.mission_completed.missio_objectiu !== undefined) {
              self.missioProgres = dades.mission_completed.missio_objectiu;
              self.missioObjectiu = dades.mission_completed.missio_objectiu;
            }
            if (dades.mission_completed.xp_update) {
              self.actualitzarDesDeXpUpdate(dades.mission_completed.xp_update);
            }
          }
          return true;
        }
        return false;
      } catch (e) {
        console.error("Error completar hàbit via API:", e);
        return false;
      }
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
     * @param {Object} dades - { xp_total, ratxa_actual, ratxa_maxima, monedes, nivell, xp_actual_nivel, xp_objetivo_nivel }
     */
    actualitzarDesDeXpUpdate: function (dades) {
      if (!dades) {
        return;
      }
      if (this.ruletaAnimant) {
        this.xpUpdatePendent = dades;
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
      if (dades.nivell !== undefined) {
        this.nivell = dades.nivell;
      }
      if (dades.xp_actual_nivel !== undefined) {
        this.xpActualNivel = dades.xp_actual_nivel;
      }
      if (dades.xp_objetivo_nivel !== undefined) {
        this.xpObjetivoNivel = dades.xp_objetivo_nivel;
      }
      if (dades.monstre_tipus !== undefined) {
        this.monstre_tipus = dades.monstre_tipus;
      }
      if (dades.streak_incremented !== undefined) {
        this.streakIncrementedAvui = !!dades.streak_incremented;
      }
    },

    iniciarAnimacioRuleta: function () {
      this.ruletaAnimant = true;
      this.xpUpdatePendent = null;
    },

    finalitzarAnimacioRuleta: function () {
      this.ruletaAnimant = false;
      if (this.xpUpdatePendent) {
        this.actualitzarDesDeXpUpdate(this.xpUpdatePendent);
        this.xpUpdatePendent = null;
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
        if (dades && dades.data && typeof dades.data === "object") {
          dades = dades.data;
        }
        if (dades) {
          self.aplicarGameStateDesDeMap(mapGameStateFromApi(dades));
        } else {
          self.cosmeticsReady = true;
        }
        return dades;
      } catch (error) {
        console.error("Error fetching game-state:", error);
        self.cosmeticsReady = true;
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
          self.aplicarGameStateDesDeMap(mapGameStateFromApi(gs));
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