'use strict';

/**
 * Middleware temporal per deixar passar el user_id.
 */
function jwtAuth(socket, next) {
  // De moment no validem, només verifiquem si arriba alguna dada d'usuari
  // o simplement deixem passar la connexió.
  console.log('Middleware JWT (Temporal): Connexió autoritzada');
  return next();
}

module.exports = jwtAuth;
