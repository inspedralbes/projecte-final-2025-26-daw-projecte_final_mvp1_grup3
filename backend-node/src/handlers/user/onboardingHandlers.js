'use strict';

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
