'use strict';


/**
 * Modul JavaScript ES5: adminQueue.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Pont de compatibilitat: re-exporta el publisher de domini Admin.
 */
var adminQueuePublisher = require('../domains/Admin/publishers/adminQueuePublisher');

module.exports = {
  pushToLaravel: adminQueuePublisher.pushToLaravel
};
