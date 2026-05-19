'use strict';


/**
 * Modul JavaScript ES5: habitQueue.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Pont de compatibilitat: re-exporta el publisher de domini Habits.
 */
var habitQueuePublisher = require('../domains/Habits/publishers/habitQueuePublisher');

module.exports = {
  pushToLaravel: habitQueuePublisher.pushToLaravel
};
