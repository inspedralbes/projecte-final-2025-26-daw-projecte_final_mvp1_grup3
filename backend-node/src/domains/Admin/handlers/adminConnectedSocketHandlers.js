'use strict';

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
