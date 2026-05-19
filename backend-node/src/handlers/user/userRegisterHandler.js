'use strict';


/**
 * Modul JavaScript ES5: userRegisterHandler.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


var userRegisterSocketHandlers = require('../../domains/User/handlers/userRegisterSocketHandlers');

module.exports = {
  register: userRegisterSocketHandlers.register
};
