'use strict';


/**
 * Modul JavaScript ES5: adminConnectedHandler.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


var adminConnectedSocketHandlers = require('../../domains/Admin/handlers/adminConnectedSocketHandlers');

module.exports = {
  register: adminConnectedSocketHandlers.register
};
