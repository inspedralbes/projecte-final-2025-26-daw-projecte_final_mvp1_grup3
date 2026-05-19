'use strict';

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
