/**
 * Modul JavaScript ES5: usePlantillesPageSocket.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';

/**
 * Registra handler de plantilla_action_confirmed per a Plantilles.vue.
 *
 * @param {function} handler
 * @returns {function} neteja
 */
export function usePlantillesPageSocket(handler) {
  var uiCallbacks = useSocketUiCallbacks();
  if (typeof handler === 'function') {
    uiCallbacks.registrarPlantillaConfirmed(handler);
  }
  return function netejar() {
    if (typeof handler === 'function') {
      uiCallbacks.eliminarPlantillaConfirmed(handler);
    }
  };
}
