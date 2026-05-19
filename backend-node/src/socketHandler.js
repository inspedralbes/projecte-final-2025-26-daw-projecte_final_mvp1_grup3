'use strict';


/**
 * Modul JavaScript ES5: socketHandler.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var presenceSocketHandlers = require('./domains/User/handlers/presenceSocketHandlers');
var habitSocketHandlers = require('./domains/Habits/handlers/habitSocketHandlers');
var plantillaSocketHandlers = require('./domains/Plantilles/handlers/plantillaSocketHandlers');
var rouletteSocketHandlers = require('./domains/Roulette/handlers/rouletteSocketHandlers');
var userRegisterSocketHandlers = require('./domains/User/handlers/userRegisterSocketHandlers');
var socialPostSocketHandlers = require('./domains/Social/handlers/socialPostSocketHandlers');
var chatSocketHandlers = require('./domains/Social/handlers/chatSocketHandlers');
var friendshipSocketHandlers = require('./domains/Social/handlers/friendshipSocketHandlers');
var clanSocketHandlers = require('./domains/Social/handlers/clanSocketHandlers');
var adminSocketHandlers = require('./domains/Admin/handlers/adminSocketHandlers');
var adminConnectedSocketHandlers = require('./domains/Admin/handlers/adminConnectedSocketHandlers');
var webrtcSignalSocketHandlers = require('./domains/WebRTC/handlers/webrtcSignalSocketHandlers');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Orquestador pur: registra handlers per domini.
 *
 * @param {object} io
 */
function init(io) {
  io.on('connection', function (socket) {
    console.log('Client connectat:', socket.id);

    presenceSocketHandlers.configurarPresencia(io, socket);

    habitSocketHandlers.register(io, socket);
    plantillaSocketHandlers.register(io, socket);
    rouletteSocketHandlers.register(io, socket);
    userRegisterSocketHandlers.register(io, socket);
    socialPostSocketHandlers.register(io, socket);
    chatSocketHandlers.register(io, socket);
    friendshipSocketHandlers.register(io, socket);
    clanSocketHandlers.register(io, socket);
    adminSocketHandlers.register(io, socket);
    adminConnectedSocketHandlers.register(io, socket);
    webrtcSignalSocketHandlers.register(io, socket);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  init: init
};
