import { defineStore } from "pinia";

// Constants de configuració
var TEMPS_ESPERA_MS = 5000;
var XP_BASE = 10;
var XP_PER_DIFICULTAT = {
  facil: 100,
  media: 250,
  dificil: 400,
};

/**
 * Store principal del joc per gestionar el progrés de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useGameStore = defineStore("game", {
  state: function () {
    return {
      userId: 1,
      racha: 0,
      ratxaMaxima: 0,
      xpTotal: 0,
      monedes: 0,
      nivell: 1,
      habits: [],
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
     */
    completarHabit: async function (idHabit, socket) {
      var self = this;
      var habitCercat;
      var i;

      if (!socket) {
        throw new Error("Socket no disponible");
      }

      for (i = 0; i < self.habits.length; i++) {
        if (self.habits[i].id === idHabit) {
          habitCercat = self.habits[i];
          break;
        }
      }

      if (!habitCercat) {
        return false;
      }

      return new Promise(function (resolve) {
        var idTemps;

        var gestionarResposta = function (resposta) {
          socket.off("update_xp", gestionarResposta);
          clearTimeout(idTemps);

          if (
            resposta &&
            (resposta.xp_total !== undefined || resposta.success === true)
          ) {
            habitCercat.completat = true;
            self.obtenirEstatJoc().then(function () {
              resolve(true);
            });
          } else {
            resolve(false);
          }
        };

        idTemps = setTimeout(function () {
          socket.off("update_xp", gestionarResposta);
          resolve(false);
        }, TEMPS_ESPERA_MS);

        socket.on("update_xp", gestionarResposta);

        socket.emit("habit_completed", {
          habit_id: idHabit,
          data: new Date().toISOString(),
        });
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
     */
    registrarListenerMissionCompletada: function (socket, callback) {
      if (socket) {
        socket.on("mission_completed", function (data) {
          console.log("🎯 Missió completada detectada per socket");
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

      try {
        url = self.construirUrlApi("/api/habits");
        var authStore = useAuthStore();
        resposta = await fetch(url, {
          headers: authStore.getAuthHeaders(),
          mode: "cors",
        });

        if (resposta.status === 401) {
          authStore.logout();
          await navigateTo("/Login");
          return [];
        }

        if (!resposta.ok) {
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
            recompensaXP: XP_PER_DIFICULTAT[h.dificultat] || XP_BASE,
            dificultat: h.dificultat,
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
          await navigateTo("/Login");
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
          if (dades.ratxa_actual !== undefined) {
            self.racha = dades.ratxa_actual;
          }
          if (dades.monedes !== undefined) {
            self.monedes = dades.monedes;
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
  },
});
