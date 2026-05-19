'use strict';


/**
 * Modul JavaScript ES5: emitToUser.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Emet un esdeveniment a la sala user_X.
 *
 * @param {object} io
 * @param {string|number} userId
 * @param {string} eventName
 * @param {object} payload
 */
function emitirANotificarUsuari(io, userId, eventName, payload) {
  io.to('user_' + userId).emit(eventName, payload);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  emitirANotificarUsuari: emitirANotificarUsuari
};
