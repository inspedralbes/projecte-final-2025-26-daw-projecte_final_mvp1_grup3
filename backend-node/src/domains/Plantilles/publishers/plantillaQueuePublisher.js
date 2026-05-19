'use strict';


/**
 * Modul JavaScript ES5: plantillaQueuePublisher.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var queuePublisher = require('../../../infra/redis/queuePublisher');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var plantillesQueueKey = 'plantilles_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Publica acció de plantilla a plantilles_queue.
 */
async function pushToLaravel(action, userId, data) {
  var dades = data || {};
  var payloadObj = {
    type: 'PLANTILLA',
    action: action,
    user_id: userId,
    plantilla_id: dades.plantilla_id || null,
    plantilla_data: dades.plantilla_data || null
  };

  console.log('Pushing to Redis (' + action + ') for user ' + userId);
  return await queuePublisher.publicarACua(plantillesQueueKey, payloadObj);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  pushToLaravel: pushToLaravel
};
