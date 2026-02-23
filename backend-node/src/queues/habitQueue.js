'use strict';

/**
 * Cua d'hàbits: Envia dades a Redis per a que Laravel les processi.
 */
var redis = require('redis');

var client = null;
var habitsQueueKey = 'habits_queue';

/**
 * Obté el client de Redis connectat.
 */
async function getClient() {
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
    console.error('Error Redis Client (habitQueue):', err);
  });

  await client.connect();
  return client;
}

/**
 * Afegeix un hàbit completat a la cua de Redis.
 * @param {string} action - L'acció a realitzar ('CREATE', 'UPDATE', 'DELETE', 'TOGGLE').
 * @param {number} userId - L'ID de l'usuari (provinent del token).
 * @param {Object} data - Objecte amb habit_id i/o habit_data (titol, dificultat, etc.).
 */
async function pushToLaravel(action, userId, data) {
  var c = await getClient();

  // Creem el JSON que Laravel "entendrà"
  var payloadObj = {
    action: action,
    user_id: userId,
    habit_id: data.habit_id || null,
    habit_data: data.habit_data || null
  };
  if (action === 'TOGGLE' && data.data) {
    payloadObj.data = data.data;
  }
  var payload = JSON.stringify(payloadObj);

  console.log('Pushing to Redis (' + action + ') for user ' + userId);
  return await c.lPush(habitsQueueKey, payload);
}

module.exports = {
  pushToLaravel: pushToLaravel
};
