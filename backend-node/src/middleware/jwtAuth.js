'use strict';

var jwt = require('jsonwebtoken');

/**
 * Middleware d'autenticació JWT per a Socket.io.
 */
function jwtAuth(socket, next) {
  var token = socket.handshake.auth.token || socket.handshake.query.token;
  var secret = process.env.JWT_SECRET || 'secret'; // Ha de coincidir amb Laravel

  if (!token) {
    console.warn('Middleware JWT: No s\'ha proporcionat cap token. Mode de desenvolupament actiu.');
    // Per evitar que petin els handlers si no hi ha token en desenvolupament:
    socket.decoded_token = { user_id: 1 }; // O un ID de test
    return next();
  }

  jwt.verify(token, secret, function (err, decoded) {
    if (err) {
      console.error('Middleware JWT: Token invàlid:', err.message);
      // En producció aquí faríem: return next(new Error('Authentication error'));
      socket.decoded_token = { user_id: 1 }; // Mode resilient en dev
      return next();
    }

    socket.decoded_token = decoded;
    console.log('Middleware JWT: Usuari ' + decoded.user_id + ' autenticat.');
    next();
  });
}

module.exports = jwtAuth;

