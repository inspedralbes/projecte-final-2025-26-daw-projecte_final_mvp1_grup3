'use strict';

/**
 * Pont de compatibilitat: re-exporta handlers de domini Habits.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 */
var habitSocketHandlers = require('../../domains/Habits/handlers/habitSocketHandlers');

module.exports = {
  register: habitSocketHandlers.register
};
