import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";
import { mapPlantillaFromApi, mapHabitFromApi } from "~/utils/mappers/apiMappers.js";

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
     * Estableix la llista de plantilles a partir de dades de l'API.
     */
    establirPlantillesDesDeApi: function (llistaPlantilles) {
      var mapejats = [];
      var i;
      for (i = 0; i < llistaPlantilles.length; i++) {
        mapejats.push(mapPlantillaFromApi(llistaPlantilles[i], mapHabitFromApi));
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


        var fullUrl = base + "/api/plantilles";
        if (queryParams.length > 0) {
          fullUrl += "?" + queryParams.join("&");
        }

        // C. Realitzar la petició amb cookies i refresh automàtic
        resposta = await authFetch(fullUrl, {});

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
