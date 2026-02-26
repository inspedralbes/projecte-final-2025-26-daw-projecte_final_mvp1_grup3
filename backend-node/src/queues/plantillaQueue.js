'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redis = require('redis');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var clientRedis = null;
var clauCuaPlantilles = 'plantilles_queue';

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

  console.log('plantillaQueue: connectant a Redis a host', host, 'port', port);

  clientRedis = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  clientRedis.on('error', function (err) {
    console.error('Error Redis Client (plantillaQueue):', err);
  });

  await clientRedis.connect();
  return clientRedis;
}

/**
 * Afegeix una acció de plantilla a la cua de Redis.
 * Pas A: Obtenir connexió Redis.
 * Pas B: Preparar carrega per Laravel.
 * Pas C: Executar LPUSH a plantilles_queue.
 *
 * @param {string} accio - L'acció a realitzar ('CREATE', 'UPDATE', 'DELETE').
 * @param {number} usuariId - L'ID de l'usuari (provinent del token).
 * @param {Object} dades - Objecte amb plantilla_id i/o plantilla_data (titol, categoria, es_publica).
 */
async function pushToLaravel(accio, usuariId, dades) {
  // A. Obtenir connexió Redis
  var clientRedis = await obtenirClientRedis();

  // B. Preparar carrega per Laravel
  var plantillaId = null;
  var plantillaData = null;
  if (dades && dades.plantilla_id) {
    plantillaId = dades.plantilla_id;
  }
  if (dades && dades.plantilla_data) {
    plantillaData = dades.plantilla_data;
  }

  var carregaObj = {
    type: 'PLANTILLA',
    action: accio,
    user_id: usuariId,
    plantilla_id: plantillaId,
    plantilla_data: plantillaData
  };

  var carregaJson = JSON.stringify(carregaObj);

  // C. Executar LPUSH a la cua
  console.log('Pushing to Redis (' + accio + ') for user ' + usuariId);
  return await clientRedis.lPush(clauCuaPlantilles, carregaJson);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  pushToLaravel: pushToLaravel
};
