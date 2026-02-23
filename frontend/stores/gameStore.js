import { defineStore } from 'pinia';

// Constants de configuració
var TEMPS_ESPERA_MS = 5000;
var XP_BASE = 10;
var XP_PER_DIFICULTAT = {
  facil: 100,
  media: 250,
  dificil: 400
};

/**
 * Store principal del joc per gestionar el progrés de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useGameStore = defineStore('game', {
  state: function () {
    return {
      usuariId: null,
      ratxa: 0,
      ratxaMaxima: 0,
      xpTotal: 0,
      monedes: 0,
      nivell: 1,
      habits: [],
      missioDiaria: null,
      missioCompletada: false
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

      if (urlApi.endsWith('/')) {
        base = urlApi.slice(0, -1);
      } else {
        base = urlApi;
      }

      return base + cami;
    },

    /**
     * Completa un hàbit i gestiona la comunicació via Socket i fetch.
     */
    completarHabit: async function (idHabit, socket) {
      var self = this;
      var hàbit;
      var i;

      // A. Validar socket
      if (!socket) {
        throw new Error('Socket no disponible');
      }

      // B. Cercar l'hàbit localment
      for (i = 0; i < self.habits.length; i++) {
        if (self.habits[i].id === idHabit) {
          hàbit = self.habits[i];
          break;
        }
      }

      if (!hàbit) {
        return false;
      }

      // C. Processar via Promise clàssica per al socket
      return new Promise(function (resolve) {
        var idTemps;

        // Funció de gestió de la resposta del socket
        var gestionarResposta = function (resposta) {
          socket.off('update_xp', gestionarResposta);
          clearTimeout(idTemps);

          // Validar resposta del backend com confirmació de CUD
          if (resposta && resposta.xp_total !== undefined && resposta.ratxa_actual !== undefined) {
            hàbit.completat = true;

            // Refrescar estat des del backend (font de veritat)
            self.obtenirEstatJoc()
              .then(function () {
                resolve(true);
              })
              .catch(function () {
                resolve(true); // Encara que falli el refresh, el socket ha confirmat
              });
          } else {
            resolve(false);
          }
        };

        // Timeout de seguretat
        idTemps = setTimeout(function () {
          socket.off('update_xp', gestionarResposta);
          resolve(false);
        }, TEMPS_ESPERA_MS);

        // Escoltar resposta i emetre esdeveniment
        socket.on('update_xp', gestionarResposta);

        socket.emit('habit_completed', {
          user_id: self.usuariId,
          habit_id: idHabit,
          data: new Date().toISOString()
        });
      });
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
     * Estableix l'ID de l'usuari.
     */
    assignarUsuariId: function (id) {
      this.usuariId = id;
    },

    /**
     * Estableix el nivell actual.
     */
    assignarNivell: function (nouNivell) {
      this.nivell = nouNivell;
    },

    /**
     * Marca la missió diària com a completada.
     * Cridat quan arriba mission_completed via socket.
     */
    marcarMissioCompletada: function () {
      this.missioCompletada = true;
    },

    /**
     * Obté la missió diària des de l'API del backend.
     * Reutilitza game-state (mateix endpoint que obtenirEstatJoc).
     */
    obtenirMissioDiaria: async function () {
      var dades = await this.obtenirEstatJoc();
      return dades;
    },

    /**
     * Registra el listener per rebre mission_completed del backend.
     * Quan arriba l'emit amb success, actualitza missioCompletada.
     * Opcionalment crida callback (ex. per mostrar SweetAlert).
     */
    registrarListenerMissionCompletada: function (socket, callback) {
      var self = this;

      if (!socket) {
        return;
      }

      socket.on("mission_completed", function (data) {
        if (data && data.success === true) {
          self.marcarMissioCompletada();
          self.obtenirEstatJoc();
          if (typeof callback === "function") {
            callback();
          }
        }
      });
    },

    /**
     * Obté els hàbits des de l'API de Laravel.
     * GET /api/habits. Retorna llista mapejada al format del frontend.
     */
    obtenirHabitos: async function () {
      var self = this;
      var url;
      var resposta;
      var dadesBrutes;
      var llistaHabits;
      var h;
      var mapejats = [];

      try {
        url = self.construirUrlApi('/api/habits');
        resposta = await fetch(url, {
          headers: { Accept: 'application/json' },
          mode: 'cors'
        });

        if (!resposta.ok) {
          throw new Error("Error en obtenir hàbits: " + resposta.status);
        }

        dadesBrutes = await resposta.json();

        if (Array.isArray(dadesBrutes)) {
          llistaHabits = dadesBrutes;
        } else {
          if (dadesBrutes.data !== undefined) {
            llistaHabits = dadesBrutes.data;
          } else {
            llistaHabits = [];
          }
        }

        for (var i = 0; i < llistaHabits.length; i++) {
          h = llistaHabits[i];
          mapejats.push({
            id: h.id,
            nom: h.titol || 'Sense nom',
            descripcio: (h.frequencia_tipus || '') + " - Dificultat: " + (h.dificultat || ''),
            completat: false,
            recompensaXP: XP_PER_DIFICULTAT[h.dificultat] || XP_BASE,
            usuariId: h.usuari_id,
            plantillaId: h.plantilla_id,
            dificultat: h.dificultat,
            frequenciaTipus: h.frequencia_tipus,
            diesSetmana: h.dies_setmana,
            objectiuVegades: h.objectiu_vegades
          });
        }

        self.habits = mapejats;
        return self.habits;
      } catch (error) {
        console.error('Error fetching habits:', error);
        self.habits = [];
        return [];
      }
    },

    /**
     * Obté l'estat del joc (XP, Ratxa, Monedes, Missió) des de l'API de Laravel.
     * GET /api/game-state. Retorna: xp_total, ratxa_actual, ratxa_maxima, monedes, missio_diaria, missio_completada.
     */
    obtenirEstatJoc: async function () {
      var self = this;
      var url;
      var resposta;
      var dades;
      var d;

      try {
        url = self.construirUrlApi('/api/game-state');
        resposta = await fetch(url, {
          headers: { Accept: 'application/json' },
          mode: 'cors'
        });

        if (!resposta.ok) {
          throw new Error('Error en obtenir estat: ' + resposta.status);
        }

        dades = await resposta.json();

        if (!dades) {
          return null;
        }

        if (dades.data !== undefined) {
          d = dades.data;
        } else {
          d = dades;
        }

        if (d.xp_total !== undefined) {
          self.xpTotal = Number(d.xp_total);
        }
        if (d.ratxa_actual !== undefined) {
          self.ratxa = Number(d.ratxa_actual);
        }
        if (d.ratxa_maxima !== undefined) {
          self.ratxaMaxima = Number(d.ratxa_maxima);
        }
        if (d.monedes !== undefined) {
          self.monedes = Number(d.monedes);
        }
        if (d.missio_diaria !== undefined) {
          self.missioDiaria = d.missio_diaria;
        }
        if (d.missio_completada !== undefined) {
          self.missioCompletada = Boolean(d.missio_completada);
        }

        return d;
      } catch (error) {
        console.error('Error fetching game-state:', error);
        return null;
      }
    }
  }
});
