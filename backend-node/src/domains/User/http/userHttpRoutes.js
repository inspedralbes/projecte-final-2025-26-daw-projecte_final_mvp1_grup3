'use strict';


/**
 * Modul JavaScript ES5: userHttpRoutes.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var readJsonBody = require('../../../infra/http/readJsonBody');
var sendJson = require('../../../infra/http/sendJson');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function esTipusMonstreValid(tipus) {
  var valids = ['VV', 'VR', 'VL', 'VA'];
  var i;
  for (i = 0; i < valids.length; i++) {
    if (valids[i] === tipus) {
      return true;
    }
  }
  return false;
}

function gestionarMonsterChoice(req, res, body) {
  var monstreTipus = body.monstre_tipus;
  var userId = body.user_id;
  if (!monstreTipus || !esTipusMonstreValid(monstreTipus)) {
    sendJson.enviarJson(res, 400, { success: false, error: 'Tipus de monstre no vàlid' });
    return;
  }
  var userLabel = userId || 'anonymous';
  console.log('Monstre triat:', monstreTipus, 'per user:', userLabel);
  sendJson.enviarJson(res, 200, { success: true, message: 'Monstre desat correctament' });
}

function gestionarHabitsAssign(req, res, body) {
  var habits = body.habits || [];
  console.log('Habits assign rebuts:', habits.length);
  sendJson.enviarJson(res, 200, { success: true, habits: habits });
}

/**
 * Gestiona peticions HTTP d'usuari (monster-choice, habits/assign).
 *
 * @param {object} req
 * @param {object} res
 * @returns {boolean} true si la ruta s'ha gestionat
 */
function gestionarPeticio(req, res) {
  var url = req.url || '';
  var method = req.method || 'GET';

  if (method === 'POST' && url === '/api/user/monster-choice') {
    readJsonBody.llegirCosJson(req, res, function (err, body) {
      if (err) {
        sendJson.enviarJson(res, 500, { success: false, error: 'Server error' });
        return;
      }
      gestionarMonsterChoice(req, res, body);
    });
    return true;
  }

  if (method === 'POST' && url === '/api/habits/assign') {
    readJsonBody.llegirCosJson(req, res, function (err, body) {
      if (err) {
        sendJson.enviarJson(res, 500, { success: false, error: 'Server error' });
        return;
      }
      gestionarHabitsAssign(req, res, body);
    });
    return true;
  }

  return false;
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  gestionarPeticio: gestionarPeticio
};
