'use strict';


/**
 * Modul JavaScript ES5: adminQueuePublisher.
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

var adminQueueKey = 'admin_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

async function pushToLaravel(action, adminId, entityType, data) {
  var payloadObj = {
    entity: entityType,
    action: action,
    admin_id: adminId,
    data: data || {}
  };

  console.log('Admin: pushing to Redis (' + action + ' ' + entityType + ')');
  return await queuePublisher.publicarACua(adminQueueKey, payloadObj);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  pushToLaravel: pushToLaravel
};
