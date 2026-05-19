/**
 * Modul JavaScript ES5: usuarisConnectats.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

"use strict";

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

/**
 * Map: userId -> { nom, email, connected_at, socketId }
 * Per llistar usuaris connectats a l'admin.
 * Compartit entre userRegisterHandler i adminConnectedHandler.
 */
var usuarisConnectats = {};

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = usuarisConnectats;
