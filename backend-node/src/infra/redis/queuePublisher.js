'use strict';


/**
 * Modul JavaScript ES5: queuePublisher.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redisClient = require('./redisClient');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Publica un objecte JSON a una cua Redis mitjançant LPUSH.
 * Pas A: Obtenir client publisher.
 * Pas B: Serialitzar objecte.
 * Pas C: LPUSH a la cua indicada.
 *
 * @param {string} nomCua
 * @param {object} objecte
 */
async function publicarACua(nomCua, objecte) {
  var client = await redisClient.obtenirClientPublisher();
  var raw = JSON.stringify(objecte);
  return await client.lPush(nomCua, raw);
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  publicarACua: publicarACua
};
