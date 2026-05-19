'use strict';

/**
 * Pont de compatibilitat: re-exporta el scheduler de domini Scheduler.
 */
var snapshotScheduler = require('../domains/Scheduler/snapshotScheduler');

module.exports = {
  initSnapshotScheduler: snapshotScheduler.initSnapshotScheduler
};
