import { io } from 'socket.io-client';

/**
 * Plugin de Socket.io per a Nuxt 3.
 * Injecta $socket a tota l'aplicació.
 * segueix les regles ES5 per al contingut de les funcions si és possible.
 */
export default defineNuxtPlugin(function (nuxtApp) {
  // A. Llegir configuració i estat d'autenticació
  var config = useRuntimeConfig();
  var socketUrl = config.public.socketUrl || 'http://localhost:3001';
  var authStore = useAuthStore();
  if (typeof window !== 'undefined') {
    authStore.loadFromStorage();
  }

  // B. Crear socket amb token actual
  var socket = io(socketUrl, {
    auth: { token: authStore.token || '' },
    autoConnect: true,
    transports: ['websocket']
  });

  // C. Listener global per a confirmacions d'admin
  socket.on('admin_action_confirmed', function (carrega) {
    console.log('[Socket] Acció Admin Confirmada:', carrega);
    // Aquí es podria disparar un event local o actualitzar un store de Pinia
  });

  // D. Listener d'errors de connexió
  socket.on('connect_error', function (err) {
    console.error('[Socket] Error de connexió:', err.message);
  });

  /**
   * Permet reconnectar amb el token actual (després del login).
   * A. Llegir token de l'store.
   * B. Actualitzar auth del socket.
   * C. Reconnectar.
   */
  function updateSocketAuth() {
    var authStoreLocal = useAuthStore();
    socket.auth = { token: authStoreLocal.token || '' };
    socket.disconnect();
    socket.connect();
  }

  return {
    provide: {
      socket: socket,
      updateSocketAuth: updateSocketAuth
    }
  };
});
