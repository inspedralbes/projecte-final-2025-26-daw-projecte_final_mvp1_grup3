'use strict';

/**
 * Pont de compatibilitat: re-exporta el publisher de domini Plantilles.
 */
var plantillaQueuePublisher = require('../domains/Plantilles/publishers/plantillaQueuePublisher');

module.exports = {
  pushToLaravel: plantillaQueuePublisher.pushToLaravel
};
