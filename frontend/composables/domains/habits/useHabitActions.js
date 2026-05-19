/**
 * Modul JavaScript ES5: useHabitActions.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { useGameStore } from '~/stores/gameStore.js';
import { useSocketBridge } from '~/composables/socket/useSocketBridge.js';
import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';
import { authFetch, getBaseUrl } from '~/composables/useApi.js';

/**
 * Accions CUD d'hàbits: progrés via socket; completar via API (persistència + missió).
 */
export function useHabitActions() {
  var gameStore = useGameStore();
  var socketBridge = useSocketBridge();
  var uiCallbacks = useSocketUiCallbacks();

  function crearSnapshotProgres(habitId) {
    var mapa = gameStore.habitProgress || {};
    var actual = mapa[habitId];
    if (!actual) {
      return { progress: 0, completed_today: false };
    }
    return JSON.parse(JSON.stringify(actual));
  }

  function aplicarProgresOptimista(habitId, progress, completedToday) {
    gameStore.actualitzarProgresHabit(habitId, progress, completedToday);
  }

  function revertirProgres(habitId, snapshot) {
    if (!snapshot) {
      return;
    }
    gameStore.actualitzarProgresHabit(habitId, snapshot.progress, snapshot.completed_today);
  }

  function incrementarProgres(habitId, delta, objectiuMax) {
    if (!habitId) {
      return;
    }
    var snapshot = crearSnapshotProgres(habitId);
    var current = gameStore.obtenirProgresValor(habitId);
    var max = objectiuMax || 1;
    if (current >= max) {
      return;
    }
    var nou = current + delta;
    if (nou < 0) {
      nou = 0;
    }
    aplicarProgresOptimista(habitId, nou, false);
    if (socketBridge.estaConnectat()) {
      socketBridge.emitir('habit_progress', { habit_id: habitId, valor: delta });
    } else {
      revertirProgres(habitId, snapshot);
    }
  }

  function aplicarMissioCompletada(dadesMissio) {
    if (!dadesMissio) {
      return;
    }
    gameStore.missioCompletada = true;
    if (dadesMissio.missio_objectiu !== undefined) {
      gameStore.missioProgres = dadesMissio.missio_objectiu;
      gameStore.missioObjectiu = dadesMissio.missio_objectiu;
    }
    if (dadesMissio.xp_update && typeof dadesMissio.xp_update === 'object') {
      gameStore.actualitzarDesDeXpUpdate(dadesMissio.xp_update);
    }
    uiCallbacks.invocarMissionComplete(dadesMissio);
  }

  /**
   * Confirma el completat al backend (API) abans de marcar completed_today a la UI.
   */
  async function confirmarCompletat(habitId, objectiu) {
    if (!habitId) {
      return false;
    }
    var snapshot = crearSnapshotProgres(habitId);
    var obj = objectiu || 1;
    var missioAbans = gameStore.missioCompletada === true;

    var ok = await completarViaApi(habitId, obj, missioAbans);
    if (!ok) {
      revertirProgres(habitId, snapshot);
      return false;
    }
    return true;
  }

  async function completarViaApi(habitId, objectiu, missioAbans) {
    var base = getBaseUrl();
    var url = base + '/api/habits/complete';
    var obj = objectiu || 1;
    var missioJaEraCompletada = missioAbans === true;

    try {
      var resposta = await authFetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          habit_id: habitId,
          data: new Date().toISOString()
        }),
        mode: 'cors'
      });
      var dades = await resposta.json();
      if (dades && dades.data && typeof dades.data === 'object') {
        dades = dades.data;
      }

      var exit = (dades.success === true || dades.completed_today === true);
      if (!exit) {
        if (dades.message && dades.message.indexOf('objectiu abans') < 0) {
          uiCallbacks.invocarHabitError(dades.message);
        }
        return false;
      }

      gameStore.actualitzarProgresHabit(habitId, obj, true);
      if (dades.xp_update) {
        gameStore.actualitzarDesDeXpUpdate(dades.xp_update);
      }
      if (dades.mission_completed && dades.mission_completed.success === true) {
        aplicarMissioCompletada(dades.mission_completed);
        missioJaEraCompletada = true;
      }

      try {
        await gameStore.obtenirProgresHabits();
        await gameStore.obtenirEstatJoc();
      } catch (syncErr) {
        console.warn('No s\'ha pogut sincronitzar estat després de completar:', syncErr);
      }

      if (!missioJaEraCompletada && gameStore.missioCompletada === true) {
        aplicarMissioCompletada({ success: true });
      }

      gameStore.marcarAnimacioHabitCompletat(habitId);
      uiCallbacks.invocarHabitCompleteAlert();

      return true;
    } catch (e) {
      console.error('Error completar hàbit via API:', e);
      uiCallbacks.invocarHabitError('No s\'ha pogut completar l\'hàbit. Torna-ho a provar.');
      return false;
    }
  }

  function emitirHabitAction(action, payload) {
    var body = payload || {};
    body.action = action;
    socketBridge.emitir('habit_action', body);
  }

  return {
    incrementarProgres: incrementarProgres,
    confirmarCompletat: confirmarCompletat,
    emitirHabitAction: emitirHabitAction,
    aplicarProgresOptimista: aplicarProgresOptimista,
    revertirProgres: revertirProgres
  };
}
