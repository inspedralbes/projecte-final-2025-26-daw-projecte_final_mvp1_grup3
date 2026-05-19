'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var usuarisConnectats = require('../../../shared/usuarisConnectats');
var socketUserId = require('../../../shared/socketUserId');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('user_register', function (data) {
    var userId = socketUserId.obtenirUserIdSocket(socket);
    if (!userId) {
      userId = String(socket.id);
    } else {
      userId = String(userId);
    }
    var nom = 'Usuari';
    var email = '';
    if (data && data.nom) {
      nom = data.nom;
    }
    if (data && data.email) {
      email = data.email;
    }
    usuarisConnectats[userId] = {
      nom: nom,
      email: email,
      connected_at: new Date().toISOString(),
      socketId: socket.id
    };
    socket.userId = userId;
  });

  socket.on('disconnect', function () {
    if (socket.userId && usuarisConnectats[socket.userId]) {
      delete usuarisConnectats[socket.userId];
    }
    console.log('Client desconnectat:', socket.id);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
