'use strict';


/**
 * Modul JavaScript ES5: feedbackRouter.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var userFeedbackEmitter = require('./userFeedbackEmitter');
var adminFeedbackEmitter = require('./adminFeedbackEmitter');
var socialFeedbackEmitter = require('./socialFeedbackEmitter');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Enruta el payload de feedback_channel al emitter corresponent.
 *
 * @param {object} io
 * @param {object} payload
 */
function enrutar(io, payload) {
  if (payload.social_event !== undefined) {
    socialFeedbackEmitter.emit(io, payload);
    return;
  }
  if (payload.broadcast_admin === true) {
    adminFeedbackEmitter.emitBroadcast(io, payload);
    return;
  }
  if (payload.admin_id !== undefined) {
    adminFeedbackEmitter.emit(io, payload);
    return;
  }
  if (payload.user_id !== undefined) {
    userFeedbackEmitter.emit(io, payload);
  }
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  enrutar: enrutar
};
