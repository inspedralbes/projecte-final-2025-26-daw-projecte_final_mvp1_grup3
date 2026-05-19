'use strict';


/**
 * Modul JavaScript ES5: rouletteQueue.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Pont de compatibilitat: re-exporta el publisher de domini Roulette.
 */
var rouletteQueuePublisher = require('../domains/Roulette/publishers/rouletteQueuePublisher');

module.exports = {
  enviarALaravel: rouletteQueuePublisher.enviarALaravel
};
