"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var usuarisConnectats = require("../../shared/usuarisConnectats");

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Registra els listeners per al registre d'usuaris connectats.
 * user_register: actualitza usuarisConnectats.
 * disconnect: elimina l'usuari de usuarisConnectats.
 *
 * @param {object} io - Instància Socket.io
 * @param {object} socket - Socket del client
 */
function register(io, socket) {
  socket.on("user_register", function (data) {
    var userId;
    if (socket.decoded_token && socket.decoded_token.user_id) {
      userId = String(socket.decoded_token.user_id);
    } else {
      userId = String(socket.id);
    }
    var nom = (data && data.nom) ? data.nom : "Usuari";
    var email = (data && data.email) ? data.email : "";
    usuarisConnectats[userId] = {
      nom: nom,
      email: email,
      connected_at: new Date().toISOString(),
      socketId: socket.id
    };
    socket.userId = userId;
  });

  socket.on("disconnect", function () {
    if (socket.userId && usuarisConnectats[socket.userId]) {
      delete usuarisConnectats[socket.userId];
    }
    console.log("Client desconnectat:", socket.id);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
