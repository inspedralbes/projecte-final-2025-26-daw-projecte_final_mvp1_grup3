"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var adminQueue = require("../../queues/adminQueue");

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Registra els listeners d'esdeveniments d'admin.
 * admin_join: assigna sala admin_X
 * admin_action → admin_queue
 *
 * @param {object} io - Instància Socket.io
 * @param {object} socket - Socket del client
 */
function register(io, socket) {
  socket.on("admin_join", function (payload) {
    var adminId = socket.decoded_token && socket.decoded_token.admin_id;
    var role = socket.decoded_token && socket.decoded_token.role;
    if (role !== "admin" || !adminId) {
      console.warn("admin_join: token no vàlid per admin");
      return;
    }
    socket.adminId = adminId;
    socket.join("admin_" + adminId);
    console.log("Admin " + adminId + " units a la sala admin_" + adminId);
  });

  socket.on("admin_action", async function (payload) {
    try {
      var adminId = socket.decoded_token && socket.decoded_token.admin_id;
      var role = socket.decoded_token && socket.decoded_token.role;
      if (role !== "admin" || !adminId) {
        console.warn("admin_action: token no vàlid per admin");
        return;
      }
      socket.join("admin_" + adminId);
      await adminQueue.pushToLaravel(
        payload.action || "CREATE",
        adminId,
        payload.entity || "plantilla",
        payload.data || {}
      );
    } catch (error) {
      console.error("Error gestionant admin_action:", error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
