"use strict";

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Emet el feedback a la sala admin_X segons el payload.
 *
 * @param {object} io - Instància Socket.io
 * @param {object} payload - Missatge de Redis (admin_id, entity, action, success, data)
 */
function emit(io, payload) {
  var adminId = payload.admin_id;
  io.to("admin_" + adminId).emit("admin_action_confirmed", {
    admin_id: adminId,
    entity: payload.entity,
    action: payload.action,
    success: payload.success,
    data: payload.data
  });
  console.log("Admin feedback enviat a admin_" + adminId);
}

/**
 * Emet un esdeveniment de reports a tots els admins connectats.
 *
 * @param {object} io - Instància Socket.io
 * @param {object} payload - Missatge de Redis (entity, action, success, data)
 */
function emitBroadcast(io, payload) {
  io.to("admins_broadcast").emit("admin_report_updated", {
    entity: payload.entity,
    action: payload.action,
    success: payload.success,
    data: payload.data
  });
  console.log("Admin report broadcast enviat a admins_broadcast (" + payload.action + ")");
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  emit: emit,
  emitBroadcast: emitBroadcast
};
