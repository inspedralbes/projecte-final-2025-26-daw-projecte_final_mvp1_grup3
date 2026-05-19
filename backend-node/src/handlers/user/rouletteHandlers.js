'use strict';


/**
 * Modul JavaScript ES5: rouletteHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


var rouletteSocketHandlers = require('../../domains/Roulette/handlers/rouletteSocketHandlers');

module.exports = {
  register: rouletteSocketHandlers.register
};
