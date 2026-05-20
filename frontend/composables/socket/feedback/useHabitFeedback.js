/**
 * Modul JavaScript ES5: useHabitFeedback.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { useGameStore } from '~/stores/gameStore.js';
import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';

/**
 * Processa habit_action_confirmed: reconcilia gameStore i delega callbacks UI.
 */
function processarHabitActionConfirmed(payload) {
  var gameStore = useGameStore();
  var uiCallbacks = useSocketUiCallbacks();

  if (!payload || payload.success !== true) {
    var msg = '';
    if (payload && payload.message) {
      msg = payload.message;
    }
    if (payload && payload.action === 'COMPLETE') {
      var habitIdFail = payload.habit_id;
      if (payload.habit && payload.habit.id) {
        habitIdFail = payload.habit.id;
      }
      if (habitIdFail) {
        var progFail = payload.progress;
        if (progFail === undefined) {
          progFail = gameStore.obtenirProgresValor(habitIdFail);
        }
        var completatFail = payload.completed_today === true;
        gameStore.actualitzarProgresHabit(habitIdFail, progFail, completatFail);
      }
    }
    if (msg && msg.indexOf('objectiu abans') < 0) {
      if (payload.action !== 'COMPLETE' || payload.completed_today !== true) {
        uiCallbacks.invocarHabitError(msg);
      }
    }
    return;
  }

  if (payload.xp_update && typeof payload.xp_update === 'object') {
    gameStore.actualitzarDesDeXpUpdate(payload.xp_update);
  }

  if (payload.action === 'PROGRESS' && payload.progress !== undefined) {
    var habitIdProgress = payload.habit_id;
    if (payload.habit && payload.habit.id) {
      habitIdProgress = payload.habit.id;
    }
    gameStore.actualitzarProgresHabit(habitIdProgress, payload.progress, payload.completed_today);
  }

  if (payload.action === 'COMPLETE' && payload.success === true) {
    var habitIdOk = payload.habit_id;
    if (payload.habit && payload.habit.id) {
      habitIdOk = payload.habit.id;
      var progComplete = payload.progress;
      if (progComplete === undefined) {
        progComplete = gameStore.obtenirProgresValor(payload.habit.id);
      }
      gameStore.actualitzarProgresHabit(payload.habit.id, progComplete, true);
    }
    if (habitIdOk) {
      gameStore.marcarAnimacioHabitCompletat(habitIdOk);
    }
    uiCallbacks.invocarHabitCompleteAlert();
    gameStore.obtenirProgresHabits();
  }

  if (payload.action === 'FOCUS_UPDATE' && payload.completed_today === true) {
    if (payload.habit && payload.habit.id) {
      var progFocus = payload.progress || 0;
      gameStore.actualitzarProgresHabit(payload.habit.id, progFocus, true);
    }
    if (payload.xp_update && typeof payload.xp_update === 'object') {
      gameStore.actualitzarDesDeXpUpdate(payload.xp_update);
    }
  }

  var missionData = payload.mission_completed;
  if (missionData && (missionData.success === true || missionData.success === 'true')) {
    gameStore.missioCompletada = true;
    if (missionData.missio_objectiu !== undefined) {
      gameStore.missioProgres = missionData.missio_objectiu;
      gameStore.missioObjectiu = missionData.missio_objectiu;
    }
    if (missionData.xp_update && typeof missionData.xp_update === 'object') {
      gameStore.actualitzarDesDeXpUpdate(missionData.xp_update);
    }
    uiCallbacks.invocarMissionComplete(missionData);
  }

  uiCallbacks.invocarHabitConfirmed(payload);
}

/**
 * Registra el listener global habit_action_confirmed (una sola vegada).
 */
export function registrarHabitFeedback(socket) {
  if (!socket || socket._loopyHabitFeedbackRegistrat) {
    return;
  }
  socket._loopyHabitFeedbackRegistrat = true;
  socket.on('habit_action_confirmed', processarHabitActionConfirmed);
}
