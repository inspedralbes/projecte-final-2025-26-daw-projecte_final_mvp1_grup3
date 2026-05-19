'use strict';


/**
 * Modul JavaScript ES5: plantillaQueue.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Pont de compatibilitat: re-exporta el publisher de domini Plantilles.
 */
var plantillaQueuePublisher = require('../domains/Plantilles/publishers/plantillaQueuePublisher');

module.exports = {
  pushToLaravel: plantillaQueuePublisher.pushToLaravel
};
