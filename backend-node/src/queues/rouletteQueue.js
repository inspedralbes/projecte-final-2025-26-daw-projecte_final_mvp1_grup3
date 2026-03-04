'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

/**
 * Cua de ruleta: Envia dades a Redis per a que Laravel les processi.
 */
var redis = require('redis');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var client = null;
var clauCuaRuleta = 'roulette_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Obté el client de Redis connectat.
 * Pas A: Si ja existeix, retornar-lo.
 * Pas B: Crear client amb host/port.
 * Pas C: Connectar i retornar.
 */
async function obtenirClient() {
  if (client) {
    return client;
  }
  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);

  client = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  client.on('error', function (err) {
    console.error('Error Redis Client (rouletteQueue):', err);
  });

  await client.connect();
  return client;
}

/**
 * Afegeix una tirada de ruleta a la cua de Redis.
 * Pas A: Obtenir client Redis.
 * Pas B: Preparar el payload.
 * Pas C: Enviar LPUSH a la cua.
 *
 * @param {number} usuariId - L'ID de l'usuari (provinent del token).
 * @param {Object} data - Payload opcional.
 */
async function enviarALaravel(usuariId, data) {
  var c = await obtenirClient();

  var payloadObj = {
    action: 'SPIN',
    user_id: usuariId,
    data: data || null
  };
  var payload = JSON.stringify(payloadObj);

  console.log('Enviant a Redis (SPIN) per a usuari ' + usuariId);
  return await c.lPush(clauCuaRuleta, payload);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  enviarALaravel: enviarALaravel
};
