'use strict';


/**
 * Modul JavaScript ES5: snapshotQueue.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Pont de compatibilitat: re-exporta el scheduler de domini Scheduler.
 */
var snapshotScheduler = require('../domains/Scheduler/snapshotScheduler');

module.exports = {
  initSnapshotScheduler: snapshotScheduler.initSnapshotScheduler
};
