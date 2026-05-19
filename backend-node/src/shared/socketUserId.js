'use strict';


/**
 * Modul JavaScript ES5: socketUserId.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Resol l'ID d'usuari des del token JWT del socket.
 *
 * @param {object|null} decoded
 * @returns {string|number|null}
 */
function resolveSocketUserId(decoded) {
  if (!decoded) {
    return null;
  }
  var id = null;
  if (decoded.user_id != null && decoded.user_id !== '') {
    id = decoded.user_id;
  } else {
    id = decoded.sub;
  }
  if (id == null || id === '') {
    return null;
  }
  return id;
}

/**
 * Obté user_id del socket autenticat.
 *
 * @param {object} socket
 * @returns {string|number|null}
 */
function obtenirUserIdSocket(socket) {
  return resolveSocketUserId(socket.decoded_token);
}

/**
 * Emet error d'autenticació per accions d'hàbits.
 *
 * @param {object} socket
 * @param {object} payload
 */
function emitirErrorAuthHabit(socket, payload) {
  var action = 'UNKNOWN';
  if (payload && typeof payload.action === 'string') {
    action = payload.action;
  }
  socket.emit('habit_action_confirmed', {
    action: action,
    success: false,
    error: 'SOCKET_AUTH'
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  resolveSocketUserId: resolveSocketUserId,
  obtenirUserIdSocket: obtenirUserIdSocket,
  emitirErrorAuthHabit: emitirErrorAuthHabit
};
