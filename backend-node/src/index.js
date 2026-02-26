'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var http = require('http');
var socketIo = require('socket.io');
var socketHandler = require('./socketHandler');
var feedbackSubscriber = require('./subscribers/feedbackSubscriber');
var jwtAuth = require('./middleware/jwtAuth');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var portServidor = process.env.PORT || 3001;
var servidorHttp = null;
var io = null;

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Crea el servidor HTTP bàsic de salut.
 * A. Definir el handler de resposta.
 * B. Retornar la instància del servidor.
 */
function crearServidorHttp() {
  var servidor = http.createServer(function (req, res) {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ status: 'Node Backend actiu' }));
  });

  return servidor;
}

/**
 * Inicialitza Socket.io amb la configuració CORS.
 * A. Crear el servidor de sockets.
 * B. Retornar la instància de Socket.io.
 */
function crearServidorSocket(servidor) {
  var servidorSockets = new socketIo.Server(servidor, {
    cors: {
      origin: '*',
      methods: ['GET', 'POST']
    }
  });

  return servidorSockets;
}

/**
 * Funció d'arrencada orquestrada.
 * A. Crear servidor HTTP i Socket.io.
 * B. Aplicar middleware JWT.
 * C. Iniciar subscripció de feedback.
 * D. Iniciar handlers de socket.
 * E. Arrencar servidor.
 */
async function arrencarServidor() {
  // A. Crear servidor HTTP i Socket.io
  servidorHttp = crearServidorHttp();
  io = crearServidorSocket(servidorHttp);

  // B. Aplicar middleware d'autenticació
  io.use(jwtAuth);

  // C. Escolta feedback de Laravel (Redis Subscribe)
  await feedbackSubscriber.init(io);

  // D. Escolta esdeveniments dels clients (Sockets)
  socketHandler.init(io);

  // E. Arrencar servidor
  servidorHttp.listen(portServidor, '0.0.0.0', function () {
    console.log('Servidor Node actiu al port', portServidor);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

arrencarServidor().catch(function (error) {
  console.error('Error en arrencarServidor:', error);
});
