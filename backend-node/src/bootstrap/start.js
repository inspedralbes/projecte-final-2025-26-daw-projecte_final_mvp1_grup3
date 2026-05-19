'use strict';


/**
 * Modul JavaScript ES5: start.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var httpServer = require('./httpServer');
var socketServer = require('./socketServer');
var socketHandler = require('../socketHandler');
var feedbackSubscriber = require('../subscribers/feedbackSubscriber');
var snapshotScheduler = require('../domains/Scheduler/snapshotScheduler');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var PORT = process.env.PORT || 3001;
var GEMINI_API_KEY = process.env.GEMINI_API_KEY || '';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function obtenirGenAI() {
  if (!GEMINI_API_KEY) {
    return null;
  }
  var GoogleGenerativeAI = require('@google/generative-ai');
  return new GoogleGenerativeAI(GEMINI_API_KEY);
}

/**
 * Arrencada orquestrada: Redis feedback → sockets → cron → listen.
 */
async function run() {
  var genAI = obtenirGenAI();
  var server = httpServer.crearServidorHttp(genAI);
  var io = socketServer.crearSocketServer(server);

  await feedbackSubscriber.init(io);
  socketHandler.init(io);
  snapshotScheduler.initSnapshotScheduler();

  server.listen(PORT, '0.0.0.0', function () {
    console.log('Servidor Node actiu al port', PORT);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  run: run
};
