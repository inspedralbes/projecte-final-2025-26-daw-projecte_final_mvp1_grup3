"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var plantillaQueue = require("../../queues/plantillaQueue");

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Registra els listeners d'esdeveniments de plantilles.
 * plantilla_action → plantilles_queue
 *
 * @param {object} io - Instància Socket.io
 * @param {object} socket - Socket del client
 */
function register(io, socket) {
  socket.on("plantilla_action", async function (payload) {
    try {
      var userId;
      if (socket.decoded_token && socket.decoded_token.user_id) {
        userId = socket.decoded_token.user_id;
      } else {
        userId = 1;
      }
      socket.join("user_" + userId);
      await plantillaQueue.pushToLaravel(payload.action, userId, payload);
    } catch (error) {
      console.error("Error gestionant plantilla_action:", error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
