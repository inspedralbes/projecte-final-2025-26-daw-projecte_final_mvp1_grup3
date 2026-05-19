'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var plantillaQueuePublisher = require('../publishers/plantillaQueuePublisher');
var socketUserId = require('../../../shared/socketUserId');
var socketRooms = require('../../../shared/socketRooms');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('plantilla_action', async function (payload) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        userId = 1;
      }
      socketRooms.joinUserRoom(socket, userId);
      await plantillaQueuePublisher.pushToLaravel(payload.action, userId, payload);
    } catch (error) {
      console.error('Error gestionant plantilla_action:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
