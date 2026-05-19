import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';
import { useSocketBridge } from '~/composables/socket/useSocketBridge.js';
import { useAuthStore } from '~/stores/useAuthStore.js';
import { flushPendingFocusEvents } from '~/composables/user/useFocusEventQueue.js';

/**
 * Registra callbacks UI de home per al feedback centralitzat.
 * Retorna funció de neteja per beforeUnmount.
 */
export function useHomeSocketUi(homeVm) {
  var uiCallbacks = useSocketUiCallbacks();
  var bridge = useSocketBridge();

  function onStreakBroken(payload) {
    var anterior = 0;
    if (payload && payload.ratxa_anterior) {
      anterior = payload.ratxa_anterior;
    }
    homeVm.ratxaAnteriorModal = anterior;
    homeVm.esObertModalRatxa = true;
  }

  function onLevelUp(data) {
    if (homeVm.mostrarAlertaLevelUp) {
      homeVm.mostrarAlertaLevelUp(data);
    }
  }

  function onRouletteResult(data) {
    if (homeVm.gestionarResultatRuleta) {
      homeVm.gestionarResultatRuleta(data);
    }
  }

  function onHabitCompleteAlert() {
    if (homeVm.mostrarAlertaHabitCompletat) {
      homeVm.mostrarAlertaHabitCompletat();
    }
  }

  function onMissionComplete() {
    if (homeVm.mostrarAlertaMissioCompletada) {
      homeVm.mostrarAlertaMissioCompletada();
    }
  }

  function onHabitError(message) {
    if (homeVm.mostrarAvis) {
      homeVm.mostrarAvis(message);
    }
  }

  uiCallbacks.registrarStreakBroken(onStreakBroken);
  uiCallbacks.registrarLevelUp(onLevelUp);
  uiCallbacks.registrarRouletteResult(onRouletteResult);
  uiCallbacks.registrarHabitCompleteAlert(onHabitCompleteAlert);
  uiCallbacks.registrarMissionComplete(onMissionComplete);
  uiCallbacks.registrarHabitError(onHabitError);

  function connectarSocketHome() {
    var socket = bridge.obtenirSocket();
    if (!socket) {
      return null;
    }
    var authStore = useAuthStore();
    if (!socket.connected && authStore.token && authStore.isAuthenticated) {
      bridge.connectarAmbToken(authStore.token);
    }
    if (socket.connected) {
      flushPendingFocusEvents(socket);
    }
    socket.on('connect', function () {
      flushPendingFocusEvents(socket);
    });
    return socket;
  }

  function netejar() {
    uiCallbacks.eliminarRouletteResult(onRouletteResult);
  }

  return {
    connectarSocketHome: connectarSocketHome,
    netejar: netejar
  };
}
