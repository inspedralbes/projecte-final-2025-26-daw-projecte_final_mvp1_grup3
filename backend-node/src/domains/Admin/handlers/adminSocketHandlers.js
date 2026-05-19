'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var adminQueuePublisher = require('../publishers/adminQueuePublisher');
var socketRooms = require('../../../shared/socketRooms');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('admin_join', function (payload) {
    var adminId = socket.decoded_token && socket.decoded_token.admin_id;
    var role = socket.decoded_token && socket.decoded_token.role;
    if (role !== 'admin' || !adminId) {
      console.warn('admin_join: token no vàlid per admin');
      return;
    }
    socket.adminId = adminId;
    socketRooms.joinAdminRoom(socket, adminId);
    console.log('Admin ' + adminId + ' units a la sala admin_' + adminId + ' i admins_broadcast');
  });

  socket.on('admin_action', async function (payload) {
    try {
      var adminId = socket.decoded_token && socket.decoded_token.admin_id;
      var role = socket.decoded_token && socket.decoded_token.role;
      if (role !== 'admin' || !adminId) {
        console.warn('admin_action: token no vàlid per admin');
        return;
      }
      socketRooms.joinAdminRoom(socket, adminId);
      var accio = 'CREATE';
      if (payload && payload.action) {
        accio = payload.action;
      }
      var entitat = 'plantilla';
      if (payload && payload.entity) {
        entitat = payload.entity;
      }
      var dades = {};
      if (payload && payload.data) {
        dades = payload.data;
      }
      await adminQueuePublisher.pushToLaravel(accio, adminId, entitat, dades);
    } catch (error) {
      console.error('Error gestionant admin_action:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
