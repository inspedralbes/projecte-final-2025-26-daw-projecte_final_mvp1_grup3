"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var habitHandlers = require("./handlers/user/habitHandlers");
var plantillaHandlers = require("./handlers/user/plantillaHandlers");
var rouletteHandlers = require("./handlers/user/rouletteHandlers");
var userRegisterHandler = require("./handlers/user/userRegisterHandler");
var adminHandlers = require("./handlers/admin/adminHandlers");
var adminConnectedHandler = require("./handlers/admin/adminConnectedHandler");

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Inicialitza la gestió d'esdeveniments de sockets.
 * Orquestra el registre de tots els handlers per usuari i admin.
 *
 * @param {object} io - Instància Socket.io
 */
function init(io) {
  io.on("connection", function (socket) {
    console.log("Client connectat:", socket.id);

    // Unir usuari a la sala user_X per rebre feedback (update_xp, mission_completed, etc.)
    var userId = socket.decoded_token && socket.decoded_token.user_id;
    if (userId) {
      socket.join("user_" + userId);
      console.log("Usuari " + userId + " unit a la sala user_" + userId);
    }

    habitHandlers.register(io, socket);
    plantillaHandlers.register(io, socket);
    rouletteHandlers.register(io, socket);
    userRegisterHandler.register(io, socket);
    adminHandlers.register(io, socket);
    adminConnectedHandler.register(io, socket);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  init: init
};
