"use strict";

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Emet el feedback a la sala user_X segons el payload.
 *
 * @param {object} io - Instància Socket.io
 * @param {object} payload - Missatge de Redis (user_id, type, action, event, etc.)
 */
function emit(io, payload) {
  var userId = payload.user_id;
  var type = payload.type;
  var action = payload.action;

  if (payload.event === "streak_broken") {
    io.to("user_" + userId).emit("streak_broken", payload);
    console.log("Streak broken enviat a la sala user_" + userId);
    return;
  }

  if (payload.xp_update) {
    console.log("[RATXA_DEBUG] Node emet update_xp:", JSON.stringify({
      ratxa_actual: payload.xp_update.ratxa_actual,
      ratxa_maxima: payload.xp_update.ratxa_maxima,
      user_id: userId
    }));
    io.to("user_" + userId).emit("update_xp", payload.xp_update);
  }

  if (payload.level_up) {
    io.to("user_" + userId).emit("level_up", payload.level_up);
  }

  if (payload.mission_completed) {
    io.to("user_" + userId).emit("mission_completed", payload.mission_completed);
  }

  if (payload.roulette_result) {
    io.to("user_" + userId).emit("roulette_result", payload.roulette_result);
  }

  if (payload.action === "PARTIAL_XP") {
    return;
  }

  if (type !== "ROULETTE") {
    var eventName = (type === "PLANTILLA") ? "plantilla_action_confirmed" : "habit_action_confirmed";
    io.to("user_" + userId).emit(eventName, payload);
  }

  console.log("Feedback enviat a la sala user_" + userId + " per l acció " + action);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  emit: emit
};
