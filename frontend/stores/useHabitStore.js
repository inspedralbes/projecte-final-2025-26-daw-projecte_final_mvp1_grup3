import { defineStore } from "pinia";
import { authFetch } from "~/utils/authFetch.js";

/**
 * Store per a la gestió dels hàbits de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useHabitStore = defineStore("habit", {
  state: function () {
    return {
      habits: [],
      loading: false,
      error: null,
    };
  },
  actions: {
    /**
     * Transforma les dades de l'API al format del frontend.
     */
    mapejarHabitDesDeApi: function (hàbit) {
      var freq = hàbit.frequencia_tipus || "";
      var freqMapejada = freq;

      if (freq === "diaria") {
        freqMapejada = "Diari";
      } else if (freq === "semanal") {
        freqMapejada = "Setmanal";
      } else if (freq === "mensual") {
        freqMapejada = "Mensual";
      } else if (freq === "especifica") {
        freqMapejada = "Dies específics";
      }

      return {
        id: hàbit.id,
        nom: hàbit.titol || "Sense nom",
        frequencia: freqMapejada,
        recordatori: hàbit.recordatori || "",
        icona: hàbit.icona || "📝",
        color: hàbit.color || "#10B981",
        dificultat: hàbit.dificultat || null,
        diesSetmana: Array.isArray(hàbit.dies_setmana)
          ? hàbit.dies_setmana
          : [],
        objectiuVegades: hàbit.objectiu_vegades || 1,
        unitat: hàbit.unitat || "",
        usuariId: hàbit.usuari_id || null,
        plantillaId: hàbit.plantilla_id || null,
        categoriaId: hàbit.categoria_id || null,
      };
    },

    /**
     * Estableix la llista d'hàbits a partir de dades de l'API.
     */
    establirHabitsDesDeApi: function (llistaHabits) {
      var mapejats = [];
      var i;

      // A. Iterar usant bucle clàssic
      for (i = 0; i < llistaHabits.length; i++) {
        mapejats.push(this.mapejarHabitDesDeApi(llistaHabits[i]));
      }
      this.habits = mapejats;
    },

    /**
     * Obté els hàbits des de l'API de Laravel via fetch.
     */
    obtenirHabitsDesDeApi: async function () {
      var resposta;
      var dadesBrutes;
      var llista;

      this.loading = true;
      this.error = null;

      try {
        // A. Realitzar la petició amb cookies i refresh automàtic
        resposta = await authFetch("/api/habits/all", {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir hàbits: " + resposta.status);
        }

        dadesBrutes = await resposta.json();

        // C. Processar les dades
        if (Array.isArray(dadesBrutes)) {
          llista = dadesBrutes;
        } else {
          llista = dadesBrutes.data || [];
        }

        this.establirHabitsDesDeApi(llista);
        return this.habits;
      } catch (e) {
        this.error = e.message;
        this.habits = [];
        return [];
      } finally {
        this.loading = false;
      }
    },

    /**
     * Afegeix un nou hàbit (Simulació amb rollback).
     */
    afegirHabit: function (nouHabit) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));

      try {
        if (!nouHabit.id) {
          nouHabit.id = Date.now();
        }
        this.habits.push(nouHabit);
      } catch (e) {
        this.error = e.message;
        this.habits = snapshot;
      }
    },

    /**
     * Actualitza un hàbit existent a la llista local.
     */
    actualitzarHabit: function (habitActualitzat) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var i;

      try {
        for (i = 0; i < this.habits.length; i++) {
          if (this.habits[i].id === habitActualitzat.id) {
            this.habits[i] = habitActualitzat;
            break;
          }
        }
      } catch (e) {
        this.error = e.message;
        this.habits = snapshot;
      }
    },

    /**
     * Afegeix o actualitza un hàbit segons si ja existeix.
     */
    guardarOActualitzarHabit: function (hàbit) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var trobat = false;
      var i;

      try {
        for (i = 0; i < this.habits.length; i++) {
          if (this.habits[i].id === hàbit.id) {
            this.habits[i] = hàbit;
            trobat = true;
            break;
          }
        }
        if (!trobat) {
          this.habits.push(hàbit);
        }
      } catch (e) {
        this.error = e.message;
        this.habits = snapshot;
      }
    },

    /**
     * Elimina un hàbit de la llista local.
     */
    eliminarHabit: function (idHabit) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var novaLlista = [];
      var i;

      try {
        for (i = 0; i < this.habits.length; i++) {
          if (this.habits[i].id !== idHabit) {
            novaLlista.push(this.habits[i]);
          }
        }
        this.habits = novaLlista;
      } catch (e) {
        this.error = e.message;
        this.habits = snapshot;
      }
    },
  },
});
