'use strict';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Uneix el socket a la sala de l'usuari per rebre feedback.
 *
 * @param {object} socket
 * @param {string|number} userId
 */
function joinUserRoom(socket, userId) {
  socket.join('user_' + userId);
}

/**
 * Uneix el socket a la sala d'admin.
 *
 * @param {object} socket
 * @param {string|number} adminId
 */
function joinAdminRoom(socket, adminId) {
  socket.join('admin_' + adminId);
  socket.join('admins_broadcast');
}

/**
 * Uneix el socket a una sala de clan.
 *
 * @param {object} socket
 * @param {string|number} clanId
 */
function joinClanRoom(socket, clanId) {
  socket.join('clan_' + clanId);
}

/**
 * Construeix el nom de sala de xat privat entre dos usuaris.
 *
 * @param {string|number} userIdA
 * @param {string|number} userIdB
 * @returns {string}
 */
function nomSalaXatPrivat(userIdA, userIdB) {
  var ids = [String(userIdA), String(userIdB)];
  ids.sort();
  return ids.join('_');
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  joinUserRoom: joinUserRoom,
  joinAdminRoom: joinAdminRoom,
  joinClanRoom: joinClanRoom,
  nomSalaXatPrivat: nomSalaXatPrivat
};
