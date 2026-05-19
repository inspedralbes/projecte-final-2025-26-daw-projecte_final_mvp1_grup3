import { registrarHabitFeedback } from './feedback/useHabitFeedback.js';
import { registrarGameFeedback } from './feedback/useGameFeedback.js';
import { registrarSocialFeedback } from './feedback/useSocialFeedback.js';
import { registrarClanFeedback } from './feedback/useClanFeedback.js';
import { registrarShopFeedback } from './feedback/useShopFeedback.js';
import { registrarPlantillaFeedback } from './feedback/usePlantillaFeedback.js';
import { registrarWebRTCFeedback } from '../domains/webrtc/useWebRTCSocket.js';

var registryInicialitzat = false;

/**
 * Registra tots els listeners de feedback una sola vegada.
 *
 * @param {object} socket
 * @param {object} nuxtApp
 */
export function inicialitzarFeedbackGlobal(socket, nuxtApp) {
  if (!socket || registryInicialitzat) {
    return;
  }
  registryInicialitzat = true;

  socket.on('admin_action_confirmed', function (payload) {
    console.log('[Socket] Acció Admin Confirmada:', payload);
  });

  registrarHabitFeedback(socket);
  registrarGameFeedback(socket);
  registrarSocialFeedback(socket, nuxtApp);
  registrarClanFeedback(socket);
  registrarShopFeedback(socket);
  registrarPlantillaFeedback(socket);
  registrarWebRTCFeedback(socket);
}

export function reiniciarRegistry() {
  registryInicialitzat = false;
}
