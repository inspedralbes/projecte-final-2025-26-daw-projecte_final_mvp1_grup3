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
      userId: null,
      racha: 0,
      xpTotal: 0,
      nivell: 1,
      habitos: []
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
     * Estableix l'ID de l'usuari.
     */
    setUserId: function (id) {
      this.userId = id;
    },

    /**
     * Obté els hàbits des de l'API de Laravel.
     */
    fetchHabitos: async function () {
      var self = this;
      var url;
      var resposta;
      var dadesBrutes;
      var llistaHabits;
      var h;
      var mapejats = [];
      var i;

      try {
        url = self.construirUrlApi('/api/habits');
        resposta = await fetch(url);

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
            nombre: h.titol || 'Sense nom',
            descripcion: (h.frequencia_tipus || '') + " - Dificultat: " + (h.dificultat || ''),
            completado: false,
            xpReward: XP_PER_DIFICULTAT[h.dificultat] || XP_BASE,
            dificultat: h.dificultat
          });
        }

        self.habitos = mapejats;
        return self.habitos;
      } catch (error) {
        console.error('Error fetching habits:', error);
        self.habitos = [];
        return [];
      }
    },

    /**
     * Obté l'estat del joc (XP, Ratxa) des de l'API de Laravel.
     */
    fetchGameState: async function () {
      var self = this;
      var url;
      var resposta;
      var dades;

      try {
        url = self.construirUrlApi('/api/game-state');
        resposta = await fetch(url);

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
        }
        return dades;
      } catch (error) {
        console.error('Error fetching game-state:', error);
        return null;
      }
    },

    /**
     * Completa un hàbit i gestiona la comunicació via Socket.
     */
    completHabit: async function (idHabit, socket) {
      var self = this;
      var habitCercat;
      var i;

      if (!socket) {
        throw new Error('Socket no disponible');
      }

      for (i = 0; i < self.habitos.length; i++) {
        if (self.habitos[i].id === idHabit) {
          habitCercat = self.habitos[i];
          break;
        }
      }

      if (!habitCercat) {
        return false;
      }

      return new Promise(function (resolve) {
        var idTemps;

        var gestionarResposta = function (resposta) {
          socket.off('update_xp', gestionarResposta);
          clearTimeout(idTemps);

          if (resposta && resposta.xp_total !== undefined) {
            habitCercat.completado = true;
            self.fetchGameState().then(function () {
              resolve(true);
            });
          } else {
            resolve(false);
          }
        };

        idTemps = setTimeout(function () {
          socket.off('update_xp', gestionarResposta);
          resolve(false);
        }, TEMPS_ESPERA_MS);

        socket.on('update_xp', gestionarResposta);

        socket.emit('habit_completed', {
          user_id: self.userId,
          habit_id: idHabit,
          data: new Date().toISOString()
        });
      });
    }
  }
});