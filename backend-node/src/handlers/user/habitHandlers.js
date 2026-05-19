'use strict';

/**
 * Pont de compatibilitat: re-exporta handlers de domini Habits.
 */
var habitSocketHandlers = require('../../domains/Habits/handlers/habitSocketHandlers');

module.exports = {
  register: habitSocketHandlers.register
};
