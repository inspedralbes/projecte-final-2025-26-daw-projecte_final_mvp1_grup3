'use strict';


/**
 * Modul JavaScript ES5: habitQueuePublisher.
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

var habitsQueueKey = 'habits_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Publica una acció d'hàbit a habits_queue (format compatible Laravel).
 *
 * @param {string} action
 * @param {number|string} userId
 * @param {object} data
 */
async function pushToLaravel(action, userId, data) {
  var dades = data || {};
  var payloadObj = {
    action: action,
    user_id: userId,
    habit_id: dades.habit_id || null,
    habit_data: dades.habit_data || null,
    plantilla_id: dades.plantilla_id || null,
    selected_habits: dades.selected_habits || null,
    valor: dades.valor,
    data: dades.data,
    focus_mode: dades.focus_mode || null,
    focus_minutes: dades.focus_minutes || 0,
    focus_event: dades.focus_event || null
  };

  console.log('Pushing to Redis (' + action + ') for user ' + userId);
  return await queuePublisher.publicarACua(habitsQueueKey, payloadObj);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  pushToLaravel: pushToLaravel
};
