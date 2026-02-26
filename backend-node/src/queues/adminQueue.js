'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redis = require('redis');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var clientRedis = null;
var clauCuaAdmin = 'admin_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Obté el client de Redis connectat.
 * A. Si ja existeix, retornar-lo.
 * B. Crear client i configurar errors.
 * C. Connectar i retornar.
 */
async function obtenirClientRedis() {
  if (clientRedis) {
    return clientRedis;
  }

  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);

  clientRedis = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  clientRedis.on('error', function (err) {
    console.error('Error Redis Client (adminQueue):', err);
  });

  await clientRedis.connect();
  return clientRedis;
}

/**
 * Envia una acció d'admin a la cua de Redis.
 * Pas A: Obtenir connexió Redis.
 * Pas B: Preparar carrega amb entity, action, admin_id, data.
 * Pas C: Executar LPUSH a admin_queue.
 *
 * @param {string} accio - CREATE, UPDATE, DELETE
 * @param {number} administradorId - ID de l'administrador (MVP1: 1)
 * @param {string} tipusEntitat - plantilla, usuari, admin, habit, logro, missio
 * @param {Object} dades - Dades de l'entitat
 */
async function pushToLaravel(accio, administradorId, tipusEntitat, dades) {
  // A. Obtenir connexió Redis
  var clientRedis = await obtenirClientRedis();

  // B. Preparar carrega
  var dadesEntitat = {};
  if (dades) {
    dadesEntitat = dades;
  }

  var carregaObj = {
    entity: tipusEntitat,
    action: accio,
    admin_id: administradorId,
    data: dadesEntitat
  };

  var carregaJson = JSON.stringify(carregaObj);

  // C. Executar LPUSH a la cua
  console.log('Admin: pushing to Redis (' + accio + ' ' + tipusEntitat + ')');
  return await clientRedis.lPush(clauCuaAdmin, carregaJson);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  pushToLaravel: pushToLaravel
};
