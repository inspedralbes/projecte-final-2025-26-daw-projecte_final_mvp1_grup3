'use strict';


/**
 * Modul JavaScript ES5: rouletteQueuePublisher.
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

var clauCuaRuleta = 'roulette_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

async function enviarALaravel(usuariId, data) {
  var payloadObj = {
    action: 'SPIN',
    user_id: usuariId,
    data: data || null
  };

  console.log('Enviant a Redis (SPIN) per a usuari ' + usuariId);
  return await queuePublisher.publicarACua(clauCuaRuleta, payloadObj);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  enviarALaravel: enviarALaravel
};
