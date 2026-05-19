'use strict';


/**
 * Modul JavaScript ES5: adminHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


var adminSocketHandlers = require('../../domains/Admin/handlers/adminSocketHandlers');

module.exports = {
  register: adminSocketHandlers.register
};
