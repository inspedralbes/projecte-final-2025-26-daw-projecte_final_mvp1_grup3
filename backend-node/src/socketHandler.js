"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var habitQueue = require("./queues/habitQueue");
var plantillaQueue = require("./queues/plantillaQueue");
var adminQueue = require("./queues/adminQueue");

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

/**
 * Map: usuariId -> { nom, email, connected_at, socketId }
 * Per llistar usuaris connectats a l'admin.
 */
var usuarisConnectats = {};

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Defineix la lògica de recepció de missatges dels clients.
 * A. Escoltar connexions de Socket.io.
 * B. Gestionar events d'usuari.
 * C. Gestionar events d'admin.
 */
function init(io) {
  io.on("connection", function (socket) {
    console.log("Client connectat:", socket.id);

    socket.on("habit_action", async function (carrega) {
      try {
        // A. Recuperar usuari del token
        var usuariId = socket.decoded_token && socket.decoded_token.user_id;
        // B. Si no hi ha usuari, sortir
        if (!usuariId) {
          console.warn("habit_action: usuari no autenticat");
          return;
        }
        // C. Enviar acció a Redis
        socket.join("user_" + usuariId);
        await habitQueue.pushToLaravel(carrega.action, usuariId, carrega);
      } catch (error) {
        console.error("Error gestionant habit_action:", error);
      }
    });

    socket.on("plantilla_action", async function (carrega) {
      try {
        // A. Recuperar usuari del token (o per defecte)
        var usuariId = 1;
        if (socket.decoded_token && socket.decoded_token.user_id) {
          usuariId = socket.decoded_token.user_id;
        }
        // B. Enviar acció a Redis
        socket.join("user_" + usuariId);
        await plantillaQueue.pushToLaravel(carrega.action, usuariId, carrega);
      } catch (error) {
        console.error("Error gestionant plantilla_action:", error);
      }
    });

    // Escolta quan el frontend comunica un hàbit completat
    socket.on("habit_completed", async function (dades) {
      try {
        // A. Recuperar usuari del token
        var usuariId = socket.decoded_token && socket.decoded_token.user_id;
        // B. Si no hi ha usuari, sortir
        if (!usuariId) {
          console.warn("habit_completed: usuari no autenticat");
          return;
        }
        // C. Preparar carrega de toggle
        var carrega = {
          habit_id: dades.habit_id,
          data: dades.data
        };
        socket.join("user_" + usuariId);
        await habitQueue.pushToLaravel("TOGGLE", usuariId, carrega);
      } catch (error) {
        console.error("Error gestionant habit_completed:", error);
      }
    });

    socket.on("admin_join", function (carrega) {
      // A. Recuperar rol i admin_id del token
      var administradorId = socket.decoded_token && socket.decoded_token.admin_id;
      var rol = socket.decoded_token && socket.decoded_token.role;
      // B. Validar rol d'admin
      if (rol !== "admin" || !administradorId) {
        console.warn("admin_join: token no vàlid per admin");
        return;
      }
      // C. Afegir a la sala d'admin
      socket.administradorId = administradorId;
      socket.join("admin_" + administradorId);
      console.log("Admin " + administradorId + " units a la sala admin_" + administradorId);
    });

    socket.on("admin_action", async function (carrega) {
      try {
        // A. Recuperar rol i admin_id del token
        var administradorId = socket.decoded_token && socket.decoded_token.admin_id;
        var rol = socket.decoded_token && socket.decoded_token.role;
        // B. Validar rol d'admin
        if (rol !== "admin" || !administradorId) {
          console.warn("admin_action: token no vàlid per admin");
          return;
        }
        // C. Preparar valors per defecte
        var accio = "CREATE";
        var entitat = "plantilla";
        var dades = {};
        if (carrega && carrega.action) {
          accio = carrega.action;
        }
        if (carrega && carrega.entity) {
          entitat = carrega.entity;
        }
        if (carrega && carrega.data) {
          dades = carrega.data;
        }
        // D. Enviar acció a Redis
        socket.join("admin_" + administradorId);
        await adminQueue.pushToLaravel(accio, administradorId, entitat, dades);
      } catch (error) {
        console.error("Error gestionant admin_action:", error);
      }
    });

    socket.on("admin:request_connected", function () {
      // A. Construir llista d'usuaris connectats
      var llista = [];
      for (var usuariId in usuarisConnectats) {
        if (usuarisConnectats.hasOwnProperty(usuariId)) {
          var u = usuarisConnectats[usuariId];
          var nom = "";
          var email = "";
          var connectat = null;
          if (u.nom) {
            nom = u.nom;
          }
          if (u.email) {
            email = u.email;
          }
          if (u.connected_at) {
            connectat = u.connected_at;
          }
          llista.push({
            user_id: usuariId,
            nom: nom,
            email: email,
            connected_at: connectat
          });
        }
      }
      // B. Retornar resposta a l'admin
      socket.emit("admin:connected_users", llista);
    });

    socket.on("user_register", function (dades) {
      // A. Determinar user_id
      var usuariId = null;
      if (socket.decoded_token && socket.decoded_token.user_id) {
        usuariId = String(socket.decoded_token.user_id);
      } else {
        usuariId = String(socket.id);
      }
      // B. Determinar nom i email
      var nom = "Usuari";
      var email = "";
      if (dades && dades.nom) {
        nom = dades.nom;
      }
      if (dades && dades.email) {
        email = dades.email;
      }
      // C. Registrar usuari connectat
      usuarisConnectats[usuariId] = {
        nom: nom,
        email: email,
        connected_at: new Date().toISOString(),
        socketId: socket.id
      };
      socket.usuariId = usuariId;
    });

    socket.on("disconnect", function () {
      // A. Eliminar usuari connectat si existeix
      if (socket.usuariId && usuarisConnectats[socket.usuariId]) {
        delete usuarisConnectats[socket.usuariId];
      }
      console.log("Client desconnectat:", socket.id);
    });
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  init: init
};
