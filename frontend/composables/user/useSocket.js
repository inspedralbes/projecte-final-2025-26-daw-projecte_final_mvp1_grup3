/**
 * Modul JavaScript ES5: useSocket.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * Fachada d'usuari: re-exporta useSocketBridge (compatibilitat AgentSocket).
 */
import { useSocketBridge } from '~/composables/socket/useSocketBridge.js';

export function useSocket() {
  var bridge = useSocketBridge();

  function enviarProgresHabit(idHabit, delta) {
    bridge.emitir('habit_progress', { habit_id: idHabit, valor: delta });
  }

  function confirmarHabit(idHabit) {
    bridge.emitir('habit_complete', {
      habit_id: idHabit,
      data: new Date().toISOString()
    });
  }

  function enviarSpinRuleta() {
    bridge.emitir('roulette_spin', {});
  }

  return {
    socket: bridge.obtenirSocket(),
    emitir: bridge.emitir,
    enviarProgresHabit: enviarProgresHabit,
    confirmarHabit: confirmarHabit,
    enviarSpinRuleta: enviarSpinRuleta,
    registrarListener: bridge.registrar,
    eliminarListener: bridge.eliminar,
    estaConnectat: bridge.estaConnectat
  };
}
