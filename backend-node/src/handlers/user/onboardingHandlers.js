'use strict';


/**
 * Modul JavaScript ES5: onboardingHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Pont de compatibilitat: re-exporta servei d'onboarding (ES5).
 */
var geminiOnboardingService = require('../../domains/Onboarding/services/geminiOnboardingService');

module.exports = {
  getOnboardingGenerateHandler: geminiOnboardingService.getOnboardingGenerateHandler,
  FALLBACK_HABITS: geminiOnboardingService.FALLBACK_HABITS,
  generarHabitsFallbackPerPerfil: geminiOnboardingService.generarHabitsFallbackPerPerfil,
  validarHabitsGenerats: geminiOnboardingService.validarHabitsGenerats
};
