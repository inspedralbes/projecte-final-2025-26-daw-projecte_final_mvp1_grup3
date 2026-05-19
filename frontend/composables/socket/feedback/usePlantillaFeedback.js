/**
 * Modul JavaScript ES5: usePlantillaFeedback.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

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
