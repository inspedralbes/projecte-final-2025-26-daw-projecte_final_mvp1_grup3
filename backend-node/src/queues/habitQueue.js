'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redis = require('redis');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var clientRedis = null;
var clauCuaHabits = 'habits_queue';

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
    console.error('Error Redis Client (habitQueue):', err);
  });

  await clientRedis.connect();
  return clientRedis;
}

/**
 * Afegeix una acció d'hàbit a la cua de Redis.
 * Pas A: Obtenir connexió Redis.
 * Pas B: Preparar carrega per Laravel.
 * Pas C: Executar LPUSH a habits_queue.
 *
 * @param {string} accio - L'acció a realitzar ('CREATE', 'UPDATE', 'DELETE', 'TOGGLE').
 * @param {number} usuariId - L'ID de l'usuari (provinent del token).
 * @param {Object} dades - Objecte amb habit_id i/o habit_data (titol, dificultat, etc.).
 */
async function pushToLaravel(accio, usuariId, dades) {
  // A. Obtenir connexió Redis
  var clientRedis = await obtenirClientRedis();

  // B. Preparar carrega per Laravel
  var habitId = null;
  var habitData = null;
  if (dades && dades.habit_id) {
    habitId = dades.habit_id;
  }
  if (dades && dades.habit_data) {
    habitData = dades.habit_data;
  }

  var carregaObj = {
    action: accio,
    user_id: usuariId,
    habit_id: habitId,
    habit_data: habitData
  };

  if (accio === 'TOGGLE' && dades && dades.data) {
    carregaObj.data = dades.data;
  }

  var carregaJson = JSON.stringify(carregaObj);

  // C. Executar LPUSH a la cua
  console.log('Pushing to Redis (' + accio + ') for user ' + usuariId);
  return await clientRedis.lPush(clauCuaHabits, carregaJson);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  pushToLaravel: pushToLaravel
};
