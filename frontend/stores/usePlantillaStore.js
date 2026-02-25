import { defineStore } from "pinia";
import { useHabitStore } from "./useHabitStore";

/**
 * Store per a la gestió de les plantilles d'hàbits.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var usePlantillaStore = defineStore("plantilla", {
  state: function () {
    return {
      plantilles: [],
      loading: false,
      error: null,
    };
  },
  actions: {
    /**
     * Transforma les dades de l'API al format del frontend.
     */
    mapejarPlantillaDesDeApi: function (plantilla) {
      var titolPlantilla;
      var categoriaPlantilla;
      var esPublicaPlantilla;
      var creadorIdPlantilla;
      var mappedHabits = [];
      var habitStore = useHabitStore();
      var i;

      // A. Assignar títol o valor per defecte
      if (plantilla.titol) {
        titolPlantilla = plantilla.titol;
      } else {
        titolPlantilla = "Sense títol";
      }

      // B. Assignar categoria o valor per defecte
      if (plantilla.categoria) {
        categoriaPlantilla = plantilla.categoria;
      } else {
        categoriaPlantilla = "Altres";
      }

      // C. Assignar estat de publicació o valor per defecte
      if (plantilla.hasOwnProperty('es_publica')) {
        esPublicaPlantilla = !!plantilla.es_publica;
      } else {
        esPublicaPlantilla = false;
      }

      // D. Assignar creadorId o valor per defecte (1), forçant a enter
      if (plantilla.creador_id) {
        creadorIdPlantilla = parseInt(plantilla.creador_id, 10);
      } else {
        creadorIdPlantilla = 1;
      }

      // E. Mapejar hàbits si existeixen
      if (plantilla.habits && Array.isArray(plantilla.habits)) {
        for (i = 0; i < plantilla.habits.length; i++) {
          mappedHabits.push(habitStore.mapejarHabitDesDeApi(plantilla.habits[i]));
        }
      }

      return {
        id: plantilla.id,
        titol: titolPlantilla,
        categoria: categoriaPlantilla,
        esPublica: esPublicaPlantilla,
        creadorId: creadorIdPlantilla,
        habits: mappedHabits
      };
    },

    /**
     * Estableix la llista de plantilles a partir de dades de l'API.
     */
    establirPlantillesDesDeApi: function (llistaPlantilles) {
      var mapejats = [];
      var i;

      // A. Iterar usant bucle clàssic
      for (i = 0; i < llistaPlantilles.length; i++) {
        mapejats.push(this.mapejarPlantillaDesDeApi(llistaPlantilles[i]));
      }
      this.plantilles = mapejats;
    },

    /**
     * Obté les plantilles des de l'API de Laravel via fetch.
     * @param {string} filter - 'all' per a totes les plantilles (públiques + de l'usuari), 'my' per a només les de l'usuari.
     * @param {number|null} userId - L'ID de l'usuari actual, necessari si el filtre és 'my'.
     */
    obtenirPlantillesDesDeApi: async function (filter, userId) {
      var configuracio;
      var urlApi;
      var base;
      var resposta;
      var dadesBrutes;
      var llista;
      var queryParams = [];

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

        // B. Construir query parameters
        if (filter) {
          queryParams.push("filter=" + filter);
        }
        if (userId) {
          queryParams.push("user_id=" + userId);
        }

        var fullUrl = base + "/api/plantilles";
        if (queryParams.length > 0) {
          fullUrl += "?" + queryParams.join("&");
        }

        // C. Realitzar la petició
        resposta = await fetch(fullUrl);

        if (!resposta.ok) {
          throw new Error("Error en obtenir plantilles: " + resposta.status);
        }

        dadesBrutes = await resposta.json();

        // D. Processar les dades
        if (Array.isArray(dadesBrutes)) {
          llista = dadesBrutes;
        } else {
          llista = dadesBrutes.data || [];
        }

        this.establirPlantillesDesDeApi(llista);
        return this.plantilles;
      } catch (e) {
        this.error = e.message;
        this.plantilles = [];
        return [];
      } finally {
        this.loading = false;
      }
    }
  },
});
