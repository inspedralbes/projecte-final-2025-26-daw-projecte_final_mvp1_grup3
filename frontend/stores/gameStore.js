import { defineStore } from "pinia";

// Constants de configuració
var TEMPS_ESPERA_MS = 5000;
var XP_BASE = 10;
var XP_PER_DIFICULTAT = {
  facil: 100,
  mitja: 250,
  dificil: 400,
};

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
      habits: [],
      habitProgress: {},
      missioDiaria: null,
      missioCompletada: false,
    };
  },

  actions: {
    /**
     * Construeix una URL de l'API a partir d'un camí.
     */
    construirUrlApi: function (cami) {
      var configuracio = useRuntimeConfig();
      var urlApi = configuracio.public.apiUrl;
      var base;

      if (urlApi.endsWith("/")) {
        base = urlApi.slice(0, -1);
      } else {
        base = urlApi;
      }

      return base + cami;
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
     * Actualitza la ratxa localment.
     */
    actualitzarRatxa: function (novaRatxa) {
      this.racha = novaRatxa;
    },

    /**
     * Actualitza l'XP localment.
     */
    actualitzarXP: function (xp) {
      this.xpTotal = xp;
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
     * Registra un listener per a l'event de missió completada.
     */
    registrarListenerMissionCompletada: function (socket, callback) {
      if (socket) {
        socket.on("mission_completed", function (data) {
          console.log("Missio completada detectada per socket");
          if (typeof callback === "function") {
            callback(data);
          }
        });
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
      var i;

      try {
        url = self.construirUrlApi("/api/habits");
        var authStore = useAuthStore();
        resposta = await fetch(url, {
          headers: authStore.getAuthHeaders(),
          mode: "cors",
        });

        if (resposta.status === 401) {
          authStore.logout();
          await navigateTo("/login");
          return [];
        }

        if (!resposta.ok) {
          throw new Error("Error en obtenir hàbits");
          throw new Error("Error en obtenir hàbits");
        }

        dadesBrutes = await resposta.json();

        if (Array.isArray(dadesBrutes)) {
          llistaHabits = dadesBrutes;
        } else {
          llistaHabits = dadesBrutes.data || [];
        }

        for (i = 0; i < llistaHabits.length; i++) {
          h = llistaHabits[i];
          mapejats.push({
            id: h.id,
            nom: h.titol || "Sense nom",
            descripcio:
              (h.frequencia_tipus || "") +
              " - Dificultat: " +
              (h.dificultat || ""),
            completat: !!h.completat,
            diesSetmana: Array.isArray(h.dies_setmana) ? h.dies_setmana : [],
            recompensaXP: XP_PER_DIFICULTAT[h.dificultat] || XP_BASE,
            dificultat: h.dificultat,
            objectiuVegades: h.objectiu_vegades || 1,
            unitat: h.unitat || "",
          });
        }

        self.habits = mapejats;
        return self.habits;
      } catch (error) {
        console.error("Error fetching habits:", error);
        self.habits = [];
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
        var authStore = useAuthStore();
        resposta = await fetch(url, {
          headers: authStore.getAuthHeaders(),
          mode: "cors",
        });

        if (resposta.status === 401) {
          authStore.logout();
          await navigateTo("/login");
          return null;
        }
        if (!resposta.ok) {
          throw new Error("Error en obtenir estat");
        }

        dades = await resposta.json();

        if (dades) {
          if (dades.xp_total !== undefined) {
            self.xpTotal = dades.xp_total;
          }
          if (dades.nivell !== undefined) {
            self.nivell = dades.nivell;
          }
          if (dades.xp_actual_nivel !== undefined) {
            self.xpActualNivel = dades.xp_actual_nivel;
          }
          if (dades.xp_objetivo_nivel !== undefined) {
            self.xpObjetivoNivel = dades.xp_objetivo_nivel;
          }
          if (dades.ratxa_actual !== undefined) {
            this.ratxa = dades.ratxa_actual;
          }
          if (dades.monedes !== undefined) {
            self.monedes = dades.monedes;
          }
          if (dades.can_spin_roulette !== undefined) {
            self.canSpinRoulette = !!dades.can_spin_roulette;
          }
          if (dades.ruleta_ultima_tirada !== undefined) {
            self.ruletaUltimaTirada = dades.ruleta_ultima_tirada;
          }
          if (dades.missio_diaria !== undefined) {
            self.missioDiaria = dades.missio_diaria;
          }
          if (dades.missio_completada !== undefined) {
            self.missioCompletada = dades.missio_completada;
          }
        }
        return dades;
      } catch (error) {
        console.error("Error fetching game-state:", error);
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
        var authStore = useAuthStore();
        resposta = await fetch(url, {
          headers: authStore.getAuthHeaders(),
          mode: "cors",
        });

        if (resposta.status === 401) {
          authStore.logout();
          await navigateTo("/login");
          return {};
        }
        if (!resposta.ok) {
          throw new Error("Error en obtenir progrés");
        }

        dades = await resposta.json();
        if (Array.isArray(dades)) {
          var mapa = {};
          var i;
          for (i = 0; i < dades.length; i++) {
            mapa[dades[i].habit_id] = {
              progress: dades[i].progress || 0,
              completed_today: !!dades[i].completed_today,
            };
          }
          self.habitProgress = mapa;
        }
        return self.habitProgress;
      } catch (error) {
        console.error("Error fetching progress:", error);
        return {};
      }
    },
  },
});