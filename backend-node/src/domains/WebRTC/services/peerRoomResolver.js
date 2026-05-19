'use strict';


/**
 * Modul JavaScript ES5: peerRoomResolver.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Genera la clau de sala WebRTC entre dos usuaris (mateix format que Laravel).
 *
 * @param {string|number} userIdA
 * @param {string|number} userIdB
 * @returns {string}
 */
function obtenirClauSala(userIdA, userIdB) {
  var a = parseInt(String(userIdA), 10);
  var b = parseInt(String(userIdB), 10);
  var minId = a;
  var maxId = b;
  if (a > b) {
    minId = b;
    maxId = a;
  }
  return 'webrtc_room_' + minId + '_' + maxId;
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  obtenirClauSala: obtenirClauSala
};
