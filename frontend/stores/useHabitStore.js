import { defineStore } from "pinia";
import { useAuthStore } from "./useAuthStore";

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
      return {
        id: hàbit.id,
        nom: hàbit.titol || "Sense nom",
        frequencia: hàbit.frequencia_tipus || "",
        recordatori: hàbit.recordatori || "",
        icona: hàbit.icona || "📝",
        color: hàbit.color || "#10B981",
        dificultat: hàbit.dificultat || null,
        diesSetmana: hàbit.dies_setmana || "",
        objectiuVegades: hàbit.objectiu_vegades || 1,
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
      var configuracio;
      var urlApi;
      var base;
      var resposta;
      var dadesBrutes;
      var llista;

      this.loading = true;
      this.error = null;

      try {
        configuracio = useRuntimeConfig();
        urlApi = configuracio.public.apiUrl;

        // A. Normalitzar la URL base
        if (urlApi.endsWith("/")) {
          base = urlApi.slice(0, -1);
        } else {
          base = urlApi;
        }

        // B. Realitzar la petició amb Authorization
        var authStore = useAuthStore();
        var headers = authStore.getAuthHeaders();
        console.log("[HabitStore] Headers:", headers);

        resposta = await fetch(base + "/api/habits", {
          headers: headers
        });

        if (resposta.status === 401) {
          authStore.logout();
          await navigateTo("/Login");
          this.habits = [];
          return [];
        }
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
