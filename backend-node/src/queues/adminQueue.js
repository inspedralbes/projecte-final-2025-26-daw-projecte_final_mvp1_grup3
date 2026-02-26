'use strict';

/**
 * Cua admin: Envia accions CUD d'admin a Redis per que Laravel les processi.
 */
var redis = require('redis');

var client = null;
var adminQueueKey = 'admin_queue';

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
    console.error('Error Redis Client (adminQueue):', err);
  });

  await client.connect();
  return client;
}

/**
 * Envia una acció d'admin a la cua de Redis.
 * Pas A: Obtenir connexió Redis.
 * Pas B: Preparar payload amb entity, action, admin_id, data.
 * Pas C: Executar LPUSH a admin_queue.
 *
 * @param {string} action - CREATE, UPDATE, DELETE
 * @param {number} adminId - ID de l'administrador (MVP1: 1)
 * @param {string} entityType - plantilla, usuari, admin, habit, logro, missio
 * @param {Object} data - Dades de l'entitat
 */
async function pushToLaravel(action, adminId, entityType, data) {
  var c = await getClient();

  var payload = JSON.stringify({
    entity: entityType,
    action: action,
    admin_id: adminId,
    data: data || {}
  });

  console.log('Admin: pushing to Redis (' + action + ' ' + entityType + ')');
  return await c.lPush(adminQueueKey, payload);
}

module.exports = {
  pushToLaravel: pushToLaravel
};
