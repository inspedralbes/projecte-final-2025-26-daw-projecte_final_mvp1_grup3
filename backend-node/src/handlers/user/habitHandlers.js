"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var habitQueue = require("../../queues/habitQueue");

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Registra els listeners d'esdeveniments d'hàbits.
 * habit_action, habit_completed, habit_progress, habit_complete → habits_queue
 *
 * @param {object} io - Instància Socket.io
 * @param {object} socket - Socket del client
 */
function register(io, socket) {
  socket.on("habit_action", async function (payload) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("habit_action: usuari no autenticat");
        return;
      }
      socket.join("user_" + userId);
      await habitQueue.pushToLaravel(payload.action, userId, payload);
    } catch (error) {
      console.error("Error gestionant habit_action:", error);
    }
  });

  socket.on("habit_completed", async function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("habit_completed: usuari no autenticat");
        return;
      }
      socket.join("user_" + userId);
      var payload = { habit_id: data.habit_id, data: data.data };
      await habitQueue.pushToLaravel("TOGGLE", userId, payload);
    } catch (error) {
      console.error("Error gestionant habit_completed:", error);
    }
  });

  socket.on("habit_progress", async function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("habit_progress: usuari no autenticat");
        return;
      }
      socket.join("user_" + userId);
      var payload = { habit_id: data.habit_id, valor: data.valor };
      await habitQueue.pushToLaravel("PROGRESS", userId, payload);
    } catch (error) {
      console.error("Error gestionant habit_progress:", error);
    }
  });

  socket.on("habit_complete", async function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("habit_complete: usuari no autenticat");
        return;
      }
      socket.join("user_" + userId);
      var payload = { habit_id: data.habit_id, data: data.data };
      await habitQueue.pushToLaravel("COMPLETE", userId, payload);
    } catch (error) {
      console.error("Error gestionant habit_complete:", error);
    }
  });

  socket.on("habit_focus_update", async function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("habit_focus_update: usuari no autenticat");
        return;
      }
      socket.join("user_" + userId);
      var payload = {
        habit_id: data && data.habit_id ? data.habit_id : null,
        focus_mode: data && data.mode ? data.mode : null,
        focus_minutes: data && data.minutes ? data.minutes : 0,
        focus_event: data && data.event ? data.event : "update",
        data: data && data.data ? data.data : null
      };
      await habitQueue.pushToLaravel("FOCUS_UPDATE", userId, payload);
    } catch (error) {
      console.error("Error gestionant habit_focus_update:", error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
