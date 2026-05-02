"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var rouletteQueue = require("../../queues/rouletteQueue");

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Registra els listeners d'esdeveniments de la ruleta.
 * roulette_spin → roulette_queue
 *
 * @param {object} io - Instància Socket.io
 * @param {object} socket - Socket del client
 */
function register(io, socket) {
  socket.on("roulette_spin", async function (data) {
    try {
      var usuariId = socket.decoded_token && socket.decoded_token.user_id;
      if (!usuariId) {
        console.warn("roulette_spin: usuari no autenticat");
        return;
      }
      socket.join("user_" + usuariId);
      await rouletteQueue.enviarALaravel(usuariId, data || {});
    } catch (error) {
      console.error("Error gestionant roulette_spin:", error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
