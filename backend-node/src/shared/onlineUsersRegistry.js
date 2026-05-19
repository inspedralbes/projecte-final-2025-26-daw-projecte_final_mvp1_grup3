'use strict';

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var onlineUsers = {};

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Marca un usuari com a en línia i emet user_status.
 *
 * @param {object} io
 * @param {string|number} userId
 */
function marcarUsuariOnline(io, userId) {
  onlineUsers[String(userId)] = true;
  io.emit('user_status', { userId: userId, online: true });
}

/**
 * Marca un usuari com a fora de línia.
 *
 * @param {object} io
 * @param {string|number} userId
 */
function marcarUsuariOffline(io, userId) {
  delete onlineUsers[String(userId)];
  io.emit('user_status', { userId: userId, online: false });
}

/**
 * Registra listener get_online_users al socket.
 *
 * @param {object} socket
 */
function registrarGetOnlineUsers(socket) {
  socket.on('get_online_users', function (callback) {
    if (typeof callback === 'function') {
      var llista = [];
      var clau;
      for (clau in onlineUsers) {
        if (onlineUsers.hasOwnProperty(clau) && onlineUsers[clau] === true) {
          llista.push(clau);
        }
      }
      callback(llista);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  marcarUsuariOnline: marcarUsuariOnline,
  marcarUsuariOffline: marcarUsuariOffline,
  registrarGetOnlineUsers: registrarGetOnlineUsers
};
