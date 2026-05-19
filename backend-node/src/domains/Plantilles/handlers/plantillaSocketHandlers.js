'use strict';


/**
 * Modul JavaScript ES5: plantillaSocketHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


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
