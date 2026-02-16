'use strict';

var jwt = require('jsonwebtoken');

var JWT_SECRET = process.env.JWT_SECRET || 'shared-secret-change-in-production';

/**
 * Middleware que verifica el token JWT en handshake.auth.token o en query.
 * Uso: socket.use(jwtAuth(socket, next));
 */
function jwtAuth(socket, next) {
  var token = null;
  if (socket.handshake && socket.handshake.auth && socket.handshake.auth.token) {
    token = socket.handshake.auth.token;
  }
  if (!token && socket.handshake && socket.handshake.query && socket.handshake.query.token) {
    token = socket.handshake.query.token;
  }
  if (!token) {
    return next(new Error('JWT required'));
  }
  jwt.verify(token, JWT_SECRET, function (err, decoded) {
    if (err) {
      return next(new Error('Invalid token'));
    }
    socket.user = decoded;
    return next();
  });
}

module.exports = jwtAuth;
