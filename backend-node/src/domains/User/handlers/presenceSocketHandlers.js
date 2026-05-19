'use strict';


/**
 * Modul JavaScript ES5: presenceSocketHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var socketUserId = require('../../../shared/socketUserId');
var socketRooms = require('../../../shared/socketRooms');
var onlineUsersRegistry = require('../../../shared/onlineUsersRegistry');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Configura presència online al connectar i desconnectar.
 *
 * @param {object} io
 * @param {object} socket
 */
function configurarPresencia(io, socket) {
  var userId = socketUserId.obtenirUserIdSocket(socket);
  if (userId) {
    socketRooms.joinUserRoom(socket, userId);
    console.log('Usuari ' + userId + ' unit a la sala user_' + userId);
    onlineUsersRegistry.marcarUsuariOnline(io, userId);
  }

  onlineUsersRegistry.registrarGetOnlineUsers(socket);

  socket.on('disconnect', function () {
    if (userId) {
      onlineUsersRegistry.marcarUsuariOffline(io, userId);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  configurarPresencia: configurarPresencia
};
