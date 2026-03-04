'use strict';

var jwt = require('jsonwebtoken');

/**
 * Middleware d'autenticació JWT per a Socket.io.
 * Rebutja la connexió si no hi ha token o és invàlid.
 */
function jwtAuth(socket, next) {
  var token = socket.handshake.auth.token || socket.handshake.query.token;
  var secret = process.env.JWT_SECRET || 'secret';

  if (!token) {
    console.warn('Middleware JWT: No s\'ha proporcionat cap token.');
    return next(new Error('Authentication required'));
  }

  jwt.verify(token, secret, function (err, decoded) {
    if (err) {
      console.error('Middleware JWT: Token invàlid:', err.message);
      return next(new Error('Authentication required'));
    }

    socket.decoded_token = decoded;
    if (decoded.user_id) {
      console.log('Middleware JWT: Usuari ' + decoded.user_id + ' autenticat.');
    }
    if (decoded.admin_id) {
      console.log('Middleware JWT: Admin ' + decoded.admin_id + ' autenticat.');
    }
    next();
  });
}

module.exports = jwtAuth;
