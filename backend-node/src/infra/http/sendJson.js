'use strict';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Envia una resposta JSON amb codi HTTP.
 *
 * @param {object} res
 * @param {number} statusCode
 * @param {object} dades
 */
function enviarJson(res, statusCode, dades) {
  res.writeHead(statusCode, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify(dades));
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  enviarJson: enviarJson
};
