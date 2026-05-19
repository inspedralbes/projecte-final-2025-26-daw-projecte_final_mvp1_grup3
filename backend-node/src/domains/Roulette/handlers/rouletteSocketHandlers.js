'use strict';


/**
 * Modul JavaScript ES5: rouletteSocketHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var rouletteQueuePublisher = require('../publishers/rouletteQueuePublisher');
var socketUserId = require('../../../shared/socketUserId');
var socketRooms = require('../../../shared/socketRooms');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('roulette_spin', async function (data) {
    try {
      var usuariId = socketUserId.obtenirUserIdSocket(socket);
      if (!usuariId) {
        console.warn('roulette_spin: usuari no autenticat');
        return;
      }
      socketRooms.joinUserRoom(socket, usuariId);
      var payloadData = {};
      if (data) {
        payloadData = data;
      }
      await rouletteQueuePublisher.enviarALaravel(usuariId, payloadData);
    } catch (error) {
      console.error('Error gestionant roulette_spin:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
