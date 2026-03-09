/**
 * Mappers centralitzats per transformar respostes de l'API al format del frontend.
 * Única font de veritat per al mapeig de models.
 */

var XP_BASE = 10;
var XP_PER_DIFICULTAT = {
  facil: 100,
  mitja: 250,
  media: 250,
  dificil: 400
};
var MONEDES_PER_DIFICULTAT = {
  facil: 2,
  mitja: 5,
  media: 5,
  dificil: 10
};

/**
 * Mapeja un hàbit de l'API al format complet (formulari, detalls).
 */
function mapHabitFromApi(hàbit) {
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
    diesSetmana: Array.isArray(hàbit.dies_setmana) ? hàbit.dies_setmana : [],
    objectiuVegades: hàbit.objectiu_vegades || 1,
    unitat: hàbit.unitat || "",
    usuariId: hàbit.usuari_id || null,
    plantillaId: hàbit.plantilla_id || null,
    categoriaId: hàbit.categoria_id || null,
    completat: !!hàbit.completat,
    descripcio: (hàbit.frequencia_tipus || "") + " - Dificultat: " + (hàbit.dificultat || ""),
    recompensaXP: XP_PER_DIFICULTAT[hàbit.dificultat] || XP_BASE,
    recompensaMonedes: MONEDES_PER_DIFICULTAT[hàbit.dificultat] || 2
  };
}

/**
 * Mapeja un hàbit de l'API al format compacte per home (llista, targetes).
 */
function mapHabitFromApiForHome(h) {
  var diesSetmana = Array.isArray(h.dies_setmana) ? h.dies_setmana : [];
  return {
    id: h.id,
    nom: h.titol || "Sense nom",
    descripcio: (h.frequencia_tipus || "") + " - Dificultat: " + (h.dificultat || ""),
    completat: !!h.completat,
    diesSetmana: diesSetmana,
    recompensaXP: XP_PER_DIFICULTAT[h.dificultat] || XP_BASE,
    recompensaMonedes: MONEDES_PER_DIFICULTAT[h.dificultat] || 2,
    dificultat: h.dificultat,
    objectiuVegades: h.objectiu_vegades || 1,
    unitat: h.unitat || ""
  };
}

/**
 * Mapeja una plantilla de l'API al format del frontend.
 * Els hàbits imbricats es mapegen amb mapHabitFromApi.
 */
function mapPlantillaFromApi(plantilla, mapHabitFn) {
  var titolPlantilla = plantilla.titol || "Sense títol";
  var categoriaPlantilla = plantilla.categoria || "Altres";
  var esPublicaPlantilla = plantilla.hasOwnProperty("es_publica") ? !!plantilla.es_publica : false;
  var creadorIdPlantilla = plantilla.creador_id ? parseInt(plantilla.creador_id, 10) : 1;
  var mappedHabits = [];
  var i;

  if (plantilla.habits && Array.isArray(plantilla.habits)) {
    var mapper = mapHabitFn || mapHabitFromApi;
    for (i = 0; i < plantilla.habits.length; i++) {
      mappedHabits.push(mapper(plantilla.habits[i]));
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
}

/**
 * Mapeja un logro de l'API (pass-through si no cal transformació).
 */
function mapLogroFromApi(logro) {
  return logro;
}

/**
 * Mapeja l'objecte game_state de l'API al format del frontend.
 */
function mapGameStateFromApi(gs) {
  if (!gs) {
    return null;
  }
  var result = {};
  if (gs.xp_total !== undefined) result.xp_total = gs.xp_total;
  if (gs.nivell !== undefined) result.nivell = gs.nivell;
  if (gs.xp_actual_nivel !== undefined) result.xp_actual_nivel = gs.xp_actual_nivel;
  if (gs.xp_objetivo_nivel !== undefined) result.xp_objetivo_nivel = gs.xp_objetivo_nivel;
  if (gs.ratxa_actual !== undefined) result.ratxa_actual = gs.ratxa_actual;
  if (gs.ratxa_maxima !== undefined) result.ratxa_maxima = gs.ratxa_maxima;
  if (gs.monedes !== undefined) result.monedes = gs.monedes;
  if (gs.can_spin_roulette !== undefined) result.can_spin_roulette = !!gs.can_spin_roulette;
  if (gs.ruleta_ultima_tirada !== undefined) result.ruleta_ultima_tirada = gs.ruleta_ultima_tirada;
  if (gs.missio_diaria !== undefined) result.missio_diaria = gs.missio_diaria;
  if (gs.missio_completada !== undefined) result.missio_completada = gs.missio_completada;
  if (gs.missio_progres !== undefined) result.missio_progres = gs.missio_progres;
  if (gs.missio_objectiu !== undefined) result.missio_objectiu = gs.missio_objectiu;
  return result;
}

/**
 * Mapeja un objecte de progrés d'hàbit de l'API.
 */
function mapHabitProgressFromApi(hp) {
  return {
    habit_id: hp.habit_id,
    progress: hp.progress || 0,
    completed_today: !!hp.completed_today
  };
}

/**
 * Mapeja una llista de progrés a un mapa { habit_id -> { progress, completed_today } }.
 */
function mapHabitProgressListToMap(progressList) {
  var mapa = {};
  var i;
  for (i = 0; i < progressList.length; i++) {
    var item = progressList[i];
    mapa[item.habit_id] = {
      progress: item.progress || 0,
      completed_today: !!item.completed_today
    };
  }
  return mapa;
}

export {
  mapHabitFromApi,
  mapHabitFromApiForHome,
  mapPlantillaFromApi,
  mapLogroFromApi,
  mapGameStateFromApi,
  mapHabitProgressFromApi,
  mapHabitProgressListToMap,
  XP_PER_DIFICULTAT,
  MONEDES_PER_DIFICULTAT
};
