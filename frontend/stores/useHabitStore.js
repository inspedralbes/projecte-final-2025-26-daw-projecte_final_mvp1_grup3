import { defineStore } from "pinia";
import { useAuthStore } from "./useAuthStore";

/**
 * Store per a la gestió dels hàbits de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useHabitStore = defineStore("habit", function () {
  // Estat
  var habits = ref([]);
  var loading = ref(false);
  var error = ref(null);

  /**
   * Transforma les dades de l'API al format del frontend.
   */
  function mapejarHabitDesDeApi(hàbit) {
    return {
      id: hàbit.id,
      nom: hàbit.titol || "Sense nom",
      frequencia: hàbit.frequencia_tipus || "",
      recordatori: hàbit.recordatori || "",
      icona: hàbit.icona || "nota",
      color: hàbit.color || "#10B981",
      dificultat: hàbit.dificultat || null,
      diesSetmana: hàbit.dies_setmana || "",
      objectiuVegades: hàbit.objectiu_vegades || 1,
      usuariId: hàbit.usuari_id || null,
      plantillaId: hàbit.plantilla_id || null,
      categoriaId: hàbit.categoria_id || null
    };
  }

  /**
   * Estableix la llista d'hàbits a partir de dades de l'API.
   */
  function establirHabitsDesDeApi(llistaHabits) {
    var mapejats = [];
    var i;

    // A. Iterar usant bucle clàssic
    for (i = 0; i < llistaHabits.length; i++) {
      mapejats.push(mapejarHabitDesDeApi(llistaHabits[i]));
    }
    habits.value = mapejats;
  }

  /**
   * Obté els hàbits des de l'API de Laravel via fetch.
   */
  async function obtenirHabitsDesDeApi() {
    var configuracio;
    var urlApi;
    var base;
    var resposta;
    var dadesBrutes;
    var llista;

    loading.value = true;
    error.value = null;

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
        habits.value = [];
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

      establirHabitsDesDeApi(llista);
      return habits.value;
    } catch (e) {
      error.value = e.message;
      habits.value = [];
      return [];
    } finally {
      loading.value = false;
    }
  }

  /**
   * Afegeix un nou hàbit (Simulació amb rollback).
   */
  function afegirHabit(nouHabit) {
    var snapshot = JSON.parse(JSON.stringify(habits.value));

    try {
      if (!nouHabit.id) {
        nouHabit.id = Date.now();
      }
      habits.value.push(nouHabit);
    } catch (e) {
      error.value = e.message;
      habits.value = snapshot;
    }
  }

  /**
   * Actualitza un hàbit existent a la llista local.
   */
  function actualitzarHabit(habitActualitzat) {
    var snapshot = JSON.parse(JSON.stringify(habits.value));
    var i;

    try {
      for (i = 0; i < habits.value.length; i++) {
        if (habits.value[i].id === habitActualitzat.id) {
          habits.value[i] = habitActualitzat;
          break;
        }
      }
    } catch (e) {
      error.value = e.message;
      habits.value = snapshot;
    }
  }

  /**
   * Afegeix o actualitza un hàbit segons si ja existeix.
   */
  function guardarOActualitzarHabit(hàbit) {
    var snapshot = JSON.parse(JSON.stringify(habits.value));
    var trobat = false;
    var i;

    try {
      for (i = 0; i < habits.value.length; i++) {
        if (habits.value[i].id === hàbit.id) {
          habits.value[i] = hàbit;
          trobat = true;
          break;
        }
      }
      if (!trobat) {
        habits.value.push(hàbit);
      }
    } catch (e) {
      error.value = e.message;
      habits.value = snapshot;
    }
  }

  /**
   * Elimina un hàbit de la llista local.
   */
  function eliminarHabit(idHabit) {
    var snapshot = JSON.parse(JSON.stringify(habits.value));
    var novaLlista = [];
    var i;

    try {
      for (i = 0; i < habits.value.length; i++) {
        if (habits.value[i].id !== idHabit) {
          novaLlista.push(habits.value[i]);
        }
      }
      habits.value = novaLlista;
    } catch (e) {
      error.value = e.message;
      habits.value = snapshot;
    }
  }

  return {
    habits: habits,
    loading: loading,
    error: error,
    mapejarHabitDesDeApi: mapejarHabitDesDeApi,
    establirHabitsDesDeApi: establirHabitsDesDeApi,
    obtenirHabitsDesDeApi: obtenirHabitsDesDeApi,
    afegirHabit: afegirHabit,
    actualitzarHabit: actualitzarHabit,
    guardarOActualitzarHabit: guardarOActualitzarHabit,
    eliminarHabit: eliminarHabit
  };
});
