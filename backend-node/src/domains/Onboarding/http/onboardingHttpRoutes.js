'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var readJsonBody = require('../../../infra/http/readJsonBody');
var geminiOnboardingService = require('../services/geminiOnboardingService');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Gestiona POST /api/onboarding/generate.
 *
 * @param {object} req
 * @param {object} res
 * @param {object|null} genAI
 * @returns {boolean}
 */
function gestionarPeticio(req, res, genAI) {
  var url = req.url || '';
  var method = req.method || 'GET';

  if (method !== 'POST' || url !== '/api/onboarding/generate') {
    return false;
  }

  var handler = geminiOnboardingService.getOnboardingGenerateHandler(genAI);
  readJsonBody.llegirCosJson(req, res, function (err, body) {
    if (err) {
      return;
    }
    req.body = body;
    handler(req, res);
  });
  return true;
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  gestionarPeticio: gestionarPeticio
};
