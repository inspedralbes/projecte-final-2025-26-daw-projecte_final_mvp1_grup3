'use strict';


/**
 * Modul JavaScript ES5: socketServer.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var socketIo = require('socket.io');
var jwtAuth = require('../middleware/jwtAuth');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Crea instància Socket.io amb middleware JWT.
 *
 * @param {object} server - http.Server
 * @returns {object} io
 */
function crearSocketServer(server) {
  var io = new socketIo.Server(server, {
    cors: {
      origin: '*',
      methods: ['GET', 'POST']
    }
  });

  io.use(jwtAuth);
  return io;
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  crearSocketServer: crearSocketServer
};
