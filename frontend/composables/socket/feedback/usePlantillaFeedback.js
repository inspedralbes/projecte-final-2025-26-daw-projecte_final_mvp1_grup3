import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';

function onPlantillaActionConfirmed(payload) {
  useSocketUiCallbacks().invocarPlantillaConfirmed(payload);
}

/**
 * Registra plantilla_action_confirmed.
 */
export function registrarPlantillaFeedback(socket) {
  if (!socket || socket._loopyPlantillaFeedbackRegistrat) {
    return;
  }
  socket._loopyPlantillaFeedbackRegistrat = true;
  socket.on('plantilla_action_confirmed', onPlantillaActionConfirmed);
}
