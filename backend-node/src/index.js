'use strict';

/**
 * Servidor principal Node.js (Fase 2).
 */
var http = require('http');
var socketIo = require('socket.io');
var socketHandler = require('./socketHandler');
var feedbackSubscriber = require('./subscribers/feedbackSubscriber');
var jwtAuth = require('./middleware/jwtAuth');

var PORT = process.env.PORT || 3001;

// Crear servidor HTTP
var server = http.createServer(function (req, res) {
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ status: 'Node Backend actiu' }));
});

// Inicialitzar Socket.io
var io = new socketIo.Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST']
  }
});

// Aplicar middleware d'autenticació
io.use(jwtAuth);

/**
 * Funció d'arrencada orquestrada.
 */
async function bootstrap() {
  // 1. Escolta feedback de Laravel (Redis Subscribe)
  await feedbackSubscriber.init(io);

  // 2. Escolta esdeveniments dels clients (Sockets)
  socketHandler.init(io);

  // 3. Arrencar servidor
  server.listen(PORT, '0.0.0.0', function () {
    console.log('Servidor Node actiu al port', PORT);
  });
}

bootstrap().catch(function (error) {
  console.error('Error en bootstrap:', error);
});
