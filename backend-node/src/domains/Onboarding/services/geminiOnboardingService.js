'use strict';


/**
 * Modul JavaScript ES5: geminiOnboardingService.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var sendJson = require('../../../infra/http/sendJson');
var fallback = require('./geminiOnboardingFallback');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var GEMINI_MODEL = process.env.GEMINI_MODEL || 'gemini-1.5-flash';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function construirPrompt(categoria, senyal, dificultat, temps) {
  return 'Eres un assistent IA d\'una app d\'hàbits anomenada Loopy.\n' +
    'Genera 3 hàbits ALTAMENT personalitzats segons la combinació exacta de respostes.\n\n' +
    'Perfil de l\'usuari:\n' +
    '- Vol millorar en: ' + categoria + '\n' +
    '- Té energia al: ' + senyal + '\n' +
    '- El seu obstacle principal és: ' + dificultat + '\n' +
    '- Té temps disponible: ' + temps + '\n\n' +
    'Regles obligatòries:\n' +
    '1) Els hàbits han de ser relacionals amb aquest perfil concret (no genèrics).\n' +
    '2) Han de ser aplicables a nivell "facil" o "media" (mai "dificil").\n' +
    '3) Cada hàbit ha de ser diferent i accionable.\n' +
    '4) Inclou trigger temporal coherent amb el senyal.\n\n' +
    'Retorna ÚNICAMENT un array JSON vàlid amb EXACTAMENT 3 objectes.';
}

function parsejarHabitsDeResposta(text, categoria, senyal, dificultat, temps) {
  var habits;
  try {
    var jsonMatch = text.match(/\[[\s\S]*\]/);
    if (jsonMatch) {
      habits = JSON.parse(jsonMatch[0]);
    } else {
      throw new Error('No JSON found in response');
    }
  } catch (parseError) {
    console.error('Error parsing Gemini response:', parseError.message);
    habits = fallback.generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps);
  }
  return habits;
}

/**
 * Factory del handler HTTP POST /api/onboarding/generate.
 *
 * @param {object|null} genAI
 * @returns {function}
 */
function getOnboardingGenerateHandler(genAI) {
  return async function (req, res) {
    try {
      var body = req.body || {};
      var categoria = body.categoria;
      var senyal = body.senyal;
      var dificultat = body.dificultat;
      var temps = body.temps;

      if (!categoria || !senyal || !dificultat || !temps) {
        sendJson.enviarJson(res, 400, {
          success: false,
          message: 'Falten camps obligatoris: categoria, senyal, dificultat, temps'
        });
        return;
      }

      if (!genAI) {
        console.warn('Onboarding generate: GEMINI_API_KEY no configurada; s\'usen hàbits per defecte.');
        var habitsFallbackPerfil = fallback.generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps);
        sendJson.enviarJson(res, 200, {
          success: true,
          habits: habitsFallbackPerfil,
          fallback: true
        });
        return;
      }

      var prompt = construirPrompt(categoria, senyal, dificultat, temps);
      var model = genAI.getGenerativeModel({
        model: GEMINI_MODEL,
        generationConfig: {
          temperature: 1.0,
          topP: 0.95,
          topK: 40
        }
      });
      var result = await model.generateContent(prompt);
      var responseText = result.response.text();
      var habits = parsejarHabitsDeResposta(responseText, categoria, senyal, dificultat, temps);
      var validatedHabits = fallback.validarHabitsGenerats(habits, categoria, senyal, dificultat, temps);

      sendJson.enviarJson(res, 200, {
        success: true,
        habits: validatedHabits
      });
    } catch (error) {
      console.error('Error in onboarding generate:', error.message);
      var bodyErr = req.body || {};
      var habitsPerfil = fallback.generarHabitsFallbackPerPerfil(
        bodyErr.categoria,
        bodyErr.senyal,
        bodyErr.dificultat,
        bodyErr.temps
      );
      sendJson.enviarJson(res, 200, {
        success: true,
        habits: habitsPerfil,
        fallback: true,
        message: 'Error generant hàbits; s\'han retornat suggeriments per defecte.'
      });
    }
  };
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  getOnboardingGenerateHandler: getOnboardingGenerateHandler,
  FALLBACK_HABITS: fallback.FALLBACK_HABITS,
  generarHabitsFallbackPerPerfil: fallback.generarHabitsFallbackPerPerfil,
  validarHabitsGenerats: fallback.validarHabitsGenerats
};
