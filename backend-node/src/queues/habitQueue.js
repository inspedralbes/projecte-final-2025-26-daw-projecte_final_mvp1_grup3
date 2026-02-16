'use strict';

/**
 * Cola de hábitos: Node hace LPUSH a la lista Redis habits_queue.
 * Laravel consume con BRPOP en RedisWorker y publica en feedback_channel.
 */
var redis = require('redis');

var client = null;
var habitsQueueKey = 'habits_queue';

function getClient() {
  if (client) {
    return client;
  }
  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);
  client = redis.createClient({ socket: { host: host, port: port } });
  client.on('error', function (err) {
    console.error('Redis habitQueue error:', err);
  });
  client.connect().catch(function (err) {
    console.error('Redis habitQueue connect error:', err);
  });
  return client;
}

/**
 * Encola un payload para que Laravel lo procese (BRPOP).
 * @param {Object} payload - Objeto a serializar y enviar (ej. { habitId, userId, action })
 */
function push(payload) {
  var c = getClient();
  var str = typeof payload === 'string' ? payload : JSON.stringify(payload);
  return c.lPush(habitsQueueKey, str);
}

module.exports = {
  getClient: getClient,
  push: push,
  habitsQueueKey: habitsQueueKey
};
