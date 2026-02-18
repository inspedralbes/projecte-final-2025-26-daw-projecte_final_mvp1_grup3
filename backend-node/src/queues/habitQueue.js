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
 * @param {Object} data - Ha de contenir user_id i habit_id.
 */
async function pushToLaravel(data) {
  var c = await getClient();
  var payload = JSON.stringify({
    user_id: data.user_id,
    habit_id: data.habit_id
  });

  return await c.lPush(habitsQueueKey, payload);
}

module.exports = {
  pushToLaravel: pushToLaravel
};
