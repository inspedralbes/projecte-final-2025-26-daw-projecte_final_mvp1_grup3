'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redis = require('redis');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var publisherClient = null;

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Retorna el client Redis compartit per LPUSH (singleton publisher).
 * Pas A: Reutilitzar client si ja està connectat.
 * Pas B: Crear client amb host/port/password de l'entorn.
 * Pas C: Connectar i retornar.
 */
async function obtenirClientPublisher() {
  if (publisherClient) {
    return publisherClient;
  }

  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);

  var redisOpts = {
    socket: {
      host: host,
      port: port
    }
  };
  if (process.env.REDIS_PASSWORD) {
    redisOpts.password = process.env.REDIS_PASSWORD;
  }

  publisherClient = redis.createClient(redisOpts);

  publisherClient.on('error', function (err) {
    console.error('Error Redis Client (publisher):', err);
  });

  await publisherClient.connect();

  return publisherClient;
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  obtenirClientPublisher: obtenirClientPublisher
};
