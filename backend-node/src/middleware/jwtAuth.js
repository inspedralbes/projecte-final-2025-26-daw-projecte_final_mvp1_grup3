'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var jwt = require('jsonwebtoken');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Middleware d'autenticació JWT per a Socket.io.
 * A. Recuperar token del handshake.
 * B. Validar i decodificar el token.
 * C. Injectar la carrega al socket.
 */
function jwtAuth(socket, next) {
  // A. Recuperar token del handshake
  var token = null;
  if (socket.handshake && socket.handshake.auth && socket.handshake.auth.token) {
    token = socket.handshake.auth.token;
  }
  if (!token && socket.handshake && socket.handshake.query && socket.handshake.query.token) {
    token = socket.handshake.query.token;
  }

  var clauSecreta = process.env.JWT_SECRET || 'secret';

  if (!token) {
    console.warn('Middleware JWT: No s\'ha proporcionat cap token.');
    return next(new Error('Authentication required'));
  }

  // B. Validar i decodificar el token
  jwt.verify(token, clauSecreta, function (err, payloadToken) {
    if (err) {
      console.error('Middleware JWT: Token invàlid:', err.message);
      return next(new Error('Authentication required'));
    }

    // C. Injectar carrega al socket
    socket.decoded_token = payloadToken;
    if (payloadToken.user_id) {
      console.log('Middleware JWT: Usuari ' + payloadToken.user_id + ' autenticat.');
    }
    if (payloadToken.admin_id) {
      console.log('Middleware JWT: Admin ' + payloadToken.admin_id + ' autenticat.');
    }
    next();
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = jwtAuth;
