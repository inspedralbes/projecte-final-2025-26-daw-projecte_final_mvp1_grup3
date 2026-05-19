'use strict';


/**
 * Modul JavaScript ES5: httpServer.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var http = require('http');
var sendJson = require('../infra/http/sendJson');
var onboardingHttpRoutes = require('../domains/Onboarding/http/onboardingHttpRoutes');
var userHttpRoutes = require('../domains/User/http/userHttpRoutes');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Crea servidor HTTP amb CORS i delegació de rutes per domini.
 *
 * @param {object|null} genAI
 * @returns {object} http.Server
 */
function crearServidorHttp(genAI) {
  var server = http.createServer(function (req, res) {
    var url = req.url || '';
    var method = req.method || 'GET';
    console.log('Node received:', method, url);

    res.on('error', function (err) {
      console.error('Response error:', err);
    });

    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (method === 'OPTIONS') {
      res.writeHead(204);
      res.end();
      return;
    }

    if (onboardingHttpRoutes.gestionarPeticio(req, res, genAI)) {
      return;
    }

    if (userHttpRoutes.gestionarPeticio(req, res)) {
      return;
    }

    sendJson.enviarJson(res, 200, { status: 'Node Backend actiu' });
  });

  return server;
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  crearServidorHttp: crearServidorHttp
};
