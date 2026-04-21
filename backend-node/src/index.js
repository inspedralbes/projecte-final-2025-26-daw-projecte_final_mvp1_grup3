'use strict';

/**
 * Servidor principal Node.js (Fase 2).
 */
var http = require('http');
var socketIo = require('socket.io');
var socketHandler = require('./socketHandler');
var feedbackSubscriber = require('./subscribers/feedbackSubscriber');
var jwtAuth = require('./middleware/jwtAuth');
var onboardingHandlers = require('./handlers/user/onboardingHandlers');

var PORT = process.env.PORT || 3001;
var GEMINI_API_KEY = process.env.GEMINI_API_KEY || '';

var genAI = null;
if (GEMINI_API_KEY) {
  var GoogleGenerativeAI = require('@google/generative-ai');
  genAI = new GoogleGenerativeAI(GEMINI_API_KEY);
}

// Crear servidor HTTP
var server = http.createServer(function (req, res) {
  var url = req.url || '';
  var method = req.method || 'GET';

  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (method === 'OPTIONS') {
    res.writeHead(204);
    res.end();
    return;
  }

  // Onboarding AI endpoint
  if (method === 'POST' && url === '/api/onboarding/generate') {
    var body = '';
    req.on('data', function (chunk) {
      body += chunk.toString();
    });
    req.on('end', function () {
      try {
        req.body = JSON.parse(body);
      } catch (e) {
        req.body = {};
      }
      onboardingHandlers.getOnboardingGenerateHandler(genAI)(req, res);
    });
    return;
  }

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
