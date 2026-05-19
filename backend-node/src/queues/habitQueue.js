'use strict';

/**
 * Pont de compatibilitat: re-exporta el publisher de domini Habits.
 */
var habitQueuePublisher = require('../domains/Habits/publishers/habitQueuePublisher');

module.exports = {
  pushToLaravel: habitQueuePublisher.pushToLaravel
};
