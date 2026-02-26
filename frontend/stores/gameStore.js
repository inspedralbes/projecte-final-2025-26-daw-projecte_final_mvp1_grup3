import { defineStore } from "pinia";

// Constants de configuració
var TEMPS_ESPERA_MS = 5000;
var XP_BASE = 10;
var XP_PER_DIFICULTAT = {
  facil: 100,
  mitja: 250,
  dificil: 400
};

/**
 * Store principal del joc per gestionar el progrés de l'usuari.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
export var useGameStore = defineStore("game", function () {
  // Estat
  var userId = ref(null);
  var ratxa = ref(0);
  var ratxaMaxima = ref(0);
  var xpTotal = ref(0);
  var monedes = ref(0);
  var nivell = ref(1);
  var habits = ref([]);
  var missioDiaria = ref(null);
  var missioCompletada = ref(false);

  /**
   * Construeix una URL de l'API a partir d'un camí.
   */
  function construirUrlApi(cami) {
    var configuracio = useRuntimeConfig();
    var urlApi = configuracio.public.apiUrl;
    var base;

    if (urlApi.endsWith("/")) {
      base = urlApi.slice(0, -1);
    } else {
      base = urlApi;
    }

    return base + cami;
  }

  /**
   * Completa un hàbit i gestiona la comunicació via Socket.
   */
  async function completarHabit(idHabit, socket) {
    var habitCercat;
    var i;

    if (!socket) {
      throw new Error("Socket no disponible");
    }

    for (i = 0; i < habits.value.length; i++) {
      if (habits.value[i].id === idHabit) {
        habitCercat = habits.value[i];
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
          obtenirEstatJoc().then(function () {
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
        data: new Date().toISOString()
      });
    });
  }

  /**
   * Actualitza la ratxa localment.
   */
  function actualitzarRatxa(novaRatxa) {
    ratxa.value = novaRatxa;
  }

  /**
   * Actualitza l'XP localment.
   */
  function actualitzarXP(xp) {
    xpTotal.value = xp;
  }

  /**
   * Estableix l'ID de l'usuari (des del authStore).
   */
  function assignarUsuariId(id) {
    userId.value = id;
  }

  /**
   * Sincronitza usuariId des de l'authStore.
   */
  function sincronitzarUsuariId() {
    var authStore = useAuthStore();
    if (authStore.user && authStore.user.id) {
      userId.value = authStore.user.id;
    }
  }

  /**
   * Estableix el nivell actual.
   */
  function assignarNivell(nouNivell) {
    nivell.value = nouNivell;
  }

  /**
   * Registra un listener per a l'event de missió completada.
   */
  function registrarListenerMissionCompletada(socket, callback) {
    if (socket) {
      socket.on("mission_completed", function (data) {
        console.log("Missio completada detectada per socket");
        if (typeof callback === "function") {
          callback(data);
        }
      });
    }
  }

  /**
   * Obté els hàbits des de l'API de Laravel.
   */
  async function obtenirHabitos() {
    var url;
    var resposta;
    var dadesBrutes;
    var llistaHabits;
    var h;
    var mapejats = [];
    var i;

    try {
      url = construirUrlApi("/api/habits");
      var authStore = useAuthStore();
      resposta = await fetch(url, {
        headers: authStore.getAuthHeaders(),
        mode: "cors"
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
          dificultat: h.dificultat
        });
      }

      habits.value = mapejats;
      return habits.value;
    } catch (error) {
      console.error("Error fetching habits:", error);
      habits.value = [];
      return [];
    }
  }

  /**
   * Obté l'estat del joc (XP, Ratxa) des de l'API de Laravel.
   */
  async function obtenirEstatJoc() {
    var url;
    var resposta;
    var dades;

    try {
      url = construirUrlApi("/api/game-state");
      var authStore = useAuthStore();
      resposta = await fetch(url, {
        headers: authStore.getAuthHeaders(),
        mode: "cors"
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
          xpTotal.value = dades.xp_total;
        }
        if (dades.ratxa_actual !== undefined) {
          ratxa.value = dades.ratxa_actual;
        }
        if (dades.monedes !== undefined) {
          monedes.value = dades.monedes;
        }
        if (dades.missio_diaria !== undefined) {
          missioDiaria.value = dades.missio_diaria;
        }
        if (dades.missio_completada !== undefined) {
          missioCompletada.value = dades.missio_completada;
        }
      }
      return dades;
    } catch (error) {
      console.error("Error fetching game-state:", error);
      return null;
    }
  }

  return {
    userId: userId,
    ratxa: ratxa,
    ratxaMaxima: ratxaMaxima,
    xpTotal: xpTotal,
    monedes: monedes,
    nivell: nivell,
    habits: habits,
    missioDiaria: missioDiaria,
    missioCompletada: missioCompletada,
    construirUrlApi: construirUrlApi,
    completarHabit: completarHabit,
    actualitzarRatxa: actualitzarRatxa,
    actualitzarXP: actualitzarXP,
    assignarUsuariId: assignarUsuariId,
    sincronitzarUsuariId: sincronitzarUsuariId,
    assignarNivell: assignarNivell,
    registrarListenerMissionCompletada: registrarListenerMissionCompletada,
    obtenirHabitos: obtenirHabitos,
    obtenirEstatJoc: obtenirEstatJoc
  };
});
