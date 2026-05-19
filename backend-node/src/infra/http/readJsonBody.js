'use strict';


/**
 * Modul JavaScript ES5: readJsonBody.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Llegeix el cos JSON d'una petició HTTP POST.
 *
 * @param {object} req
 * @param {object} res
 * @param {function} callback - callback(err, bodyObject)
 */
function llegirCosJson(req, res, callback) {
  var body = '';
  req.on('data', function (chunk) {
    body = body + chunk.toString();
  });
  req.on('end', function () {
    var parsed = {};
    try {
      if (body !== '') {
        parsed = JSON.parse(body);
      }
    } catch (e) {
      console.error('Error parsejant JSON del body:', e.message);
    }
    callback(null, parsed);
  });
  req.on('error', function (err) {
    callback(err, null);
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  llegirCosJson: llegirCosJson
};
