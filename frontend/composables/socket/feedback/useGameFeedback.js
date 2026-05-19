import { useGameStore } from '~/stores/gameStore.js';
import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';

function onUpdateXp(data) {
  if (!data) {
    return;
  }
  try {
    var gameStore = useGameStore();
    if (gameStore && typeof gameStore.actualitzarDesDeXpUpdate === 'function') {
      gameStore.actualitzarDesDeXpUpdate(data);
    }
  } catch (e) {
    console.error('[GameFeedback] update_xp:', e);
  }
}

function onStreakBroken(payload) {
  var gameStore = useGameStore();
  var uiCallbacks = useSocketUiCallbacks();
  gameStore.obtenirEstatJoc();
  uiCallbacks.invocarStreakBroken(payload);
}

function onLevelUp(data) {
  useSocketUiCallbacks().invocarLevelUp(data);
}

function onMissionCompleted(data) {
  var gameStore = useGameStore();
  var uiCallbacks = useSocketUiCallbacks();
  if (data) {
    gameStore.missioCompletada = true;
    if (data.missio_objectiu !== undefined) {
      gameStore.missioProgres = data.missio_objectiu;
      gameStore.missioObjectiu = data.missio_objectiu;
    }
    if (data.xp_update && typeof data.xp_update === 'object') {
      gameStore.actualitzarDesDeXpUpdate(data.xp_update);
    }
  }
  uiCallbacks.invocarMissionComplete(data);
}

function onRouletteResult(data) {
  useSocketUiCallbacks().invocarRouletteResult(data);
}

/**
 * Registra listeners de joc (XP, ratxa, level up, ruleta, missió).
 */
export function registrarGameFeedback(socket) {
  if (!socket || socket._loopyGameFeedbackRegistrat) {
    return;
  }
  socket._loopyGameFeedbackRegistrat = true;
  socket.on('update_xp', onUpdateXp);
  socket.on('streak_broken', onStreakBroken);
  socket.on('level_up', onLevelUp);
  socket.on('mission_completed', onMissionCompleted);
  socket.on('roulette_result', onRouletteResult);
}
