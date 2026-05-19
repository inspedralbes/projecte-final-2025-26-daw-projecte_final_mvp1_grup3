'use strict';

/**
 * Pont de compatibilitat: re-exporta el publisher de domini Roulette.
 */
var rouletteQueuePublisher = require('../domains/Roulette/publishers/rouletteQueuePublisher');

module.exports = {
  enviarALaravel: rouletteQueuePublisher.enviarALaravel
};
