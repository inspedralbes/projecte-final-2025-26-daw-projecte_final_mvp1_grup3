import { useGameStore } from '~/stores/gameStore.js';
import { useSocketBridge } from '~/composables/socket/useSocketBridge.js';
import { authFetch, getBaseUrl } from '~/composables/useApi.js';

/**
 * Accions CUD d'hàbits amb UI optimista + socket.
 */
export function useHabitActions() {
  var gameStore = useGameStore();
  var socketBridge = useSocketBridge();

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

  async function confirmarCompletat(habitId, objectiu) {
    if (!habitId) {
      return false;
    }
    var snapshot = crearSnapshotProgres(habitId);
    var obj = objectiu || 1;
    aplicarProgresOptimista(habitId, obj, true);

    if (socketBridge.estaConnectat()) {
      socketBridge.emitir('habit_complete', {
        habit_id: habitId,
        data: new Date().toISOString()
      });
      return true;
    }

    var ok = await completarViaApi(habitId);
    if (!ok) {
      revertirProgres(habitId, snapshot);
    }
    return ok;
  }

  async function completarViaApi(habitId) {
    var base = getBaseUrl();
    var url = base + '/api/habits/complete';
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
      if (dades.success === true) {
        if (dades.xp_update) {
          gameStore.actualitzarDesDeXpUpdate(dades.xp_update);
        }
        return true;
      }
      return false;
    } catch (e) {
      console.error('Error completar hàbit via API:', e);
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
