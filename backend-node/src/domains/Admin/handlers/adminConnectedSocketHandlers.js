'use strict';


/**
 * Modul JavaScript ES5: adminConnectedSocketHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var usuarisConnectats = require('../../../shared/usuarisConnectats');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('admin:request_connected', function () {
    var llista = [];
    var userId;
    for (userId in usuarisConnectats) {
      if (usuarisConnectats.hasOwnProperty(userId)) {
        var u = usuarisConnectats[userId];
        llista.push({
          user_id: userId,
          nom: u.nom || '',
          email: u.email || '',
          connected_at: u.connected_at || null
        });
      }
    }
    socket.emit('admin:connected_users', llista);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
