"use strict";

/**
 * Gestor d'esdeveniments de Socket.io.
 */
var habitQueue = require("./queues/habitQueue");
var plantillaQueue = require("./queues/plantillaQueue");
var adminQueue = require("./queues/adminQueue");

/**
 * Map: userId -> { nom, email, connected_at, socketId }
 * Per llistar usuaris connectats a l'admin.
 */
var usuarisConnectats = {};

/**
 * Defineix la lògica de recepció de missatges dels clients.
 */
function init(io) {
  io.on("connection", function (socket) {
    console.log("Client connectat:", socket.id);

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

    socket.on("plantilla_action", async function (payload) {
      try {
        var userId;
        if (socket.decoded_token && socket.decoded_token.user_id) {
          userId = socket.decoded_token.user_id;
        } else {
          userId = 1; // Default to user 1 in dev mode
        }
        socket.join("user_" + userId);
        await plantillaQueue.pushToLaravel(payload.action, userId, payload);
      } catch (error) {
        console.error("Error gestionant plantilla_action:", error);
      }
    });

    // Escolta quan el frontend comunica un hàbit completat
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
          payload.data || {},
        );
      } catch (error) {
        console.error("Error gestionant admin_action:", error);
      }
    });

    socket.on("admin:request_connected", function () {
      var llista = [];
      for (var userId in usuarisConnectats) {
        if (usuarisConnectats.hasOwnProperty(userId)) {
          var u = usuarisConnectats[userId];
          llista.push({
            user_id: userId,
            nom: u.nom || "",
            email: u.email || "",
            connected_at: u.connected_at || null,
          });
        }
      }
      socket.emit("admin:connected_users", llista);
    });

    socket.on("user_register", function (data) {
      var userId =
        socket.decoded_token && socket.decoded_token.user_id
          ? String(socket.decoded_token.user_id)
          : String(socket.id);
      var nom = data && data.nom ? data.nom : "Usuari";
      var email = data && data.email ? data.email : "";
      usuarisConnectats[userId] = {
        nom: nom,
        email: email,
        connected_at: new Date().toISOString(),
        socketId: socket.id,
      };
      socket.userId = userId;
    });

    socket.on("disconnect", function () {
      if (socket.userId && usuarisConnectats[socket.userId]) {
        delete usuarisConnectats[socket.userId];
      }
      console.log("Client desconnectat:", socket.id);
    });
  });
}

module.exports = {
  init: init,
};
