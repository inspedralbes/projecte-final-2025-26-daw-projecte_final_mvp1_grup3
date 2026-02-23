import { defineStore } from "pinia";

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
      return {
        id: plantilla.id,
        titol: plantilla.titol || "Sense títol",
        categoria: plantilla.categoria || "Altres",
        esPublica: plantilla.es_publica || false,
        creadorId: plantilla.creador_id || 1
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
     */
    obtenirPlantillesDesDeApi: async function () {
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

        // B. Realitzar la petició
        resposta = await fetch(base + "/api/plantilles");

        if (!resposta.ok) {
          throw new Error("Error en obtenir plantilles: " + resposta.status);
        }

        dadesBrutes = await resposta.json();

        // C. Processar les dades
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
