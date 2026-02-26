'use strict';

/**
 * Cua de plantilles: Envia dades a Redis per a que Laravel les processi.
 */
var redis = require('redis');

var client = null;
var plantillesQueueKey = 'plantilles_queue';

/**
 * Obté el client de Redis connectat.
 */
async function getClient() {
  if (client) {
    return client;
  }
  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);

  console.log('plantillaQueue: Attempting to connect to Redis at host:', host, 'port:', port); // Debug log

  client = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  client.on('error', function (err) {
    console.error('Error Redis Client (plantillaQueue):', err);
  });

  await client.connect();
  return client;
}

/**
 * Afegeix una acció de plantilla a la cua de Redis.
 * @param {string} action - L'acció a realitzar ('CREATE', 'UPDATE', 'DELETE').
 * @param {number} userId - L'ID de l'usuari (provinent del token).
 * @param {Object} data - Objecte amb plantilla_id i/o plantilla_data (titol, categoria, es_publica).
 */
async function pushToLaravel(action, userId, data) {
  var c = await getClient();

  var payload = JSON.stringify({
    type: 'PLANTILLA',
    action: action,
    user_id: userId,
    plantilla_id: data.plantilla_id || null,
    plantilla_data: data.plantilla_data || null
  });

  console.log('Pushing to Redis (' + action + ') for user ' + userId);
  return await c.lPush(plantillesQueueKey, payload);
}

module.exports = {
  pushToLaravel: pushToLaravel
};
