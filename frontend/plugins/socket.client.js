/**
 * Modul JavaScript ES5: socket.client.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { io } from 'socket.io-client';
import { useAuthStore } from '~/stores/useAuthStore.js';
import { inicialitzarFeedbackGlobal } from '~/composables/socket/useSocketRegistry.js';

/**
 * Plugin Socket.io: connexió, auth i registre centralitzat de feedback.
 */
export default defineNuxtPlugin(function (nuxtApp) {
  var config = useRuntimeConfig();
  var socketUrl = config.public.socketUrl || 'http://localhost:3001';
  var authStore = useAuthStore();

  var socket = io(socketUrl, {
    auth: { token: authStore.token || '' },
    autoConnect: false,
    transports: ['websocket']
  });

  var authRefreshRetried = false;
  var typingCallbacks = [];
  nuxtApp.$typingCallbacks = typingCallbacks;

  inicialitzarFeedbackGlobal(socket, nuxtApp);

  function esPaginaOAuthGoogle() {
    if (typeof window === 'undefined') {
      return false;
    }
    return window.location.pathname.indexOf('/auth/google/redirect') === 0;
  }

  socket.on('connect_error', function (err) {
    console.error('[Socket] Error de connexió:', err.message);
    if (esPaginaOAuthGoogle()) {
      return;
    }
    if (err.message === 'Authentication required' && !authRefreshRetried) {
      authRefreshRetried = true;
      authStore.refrescarSessio().then(function (ok) {
        if (ok) {
          socket.auth = { token: authStore.token || '' };
          socket.connect();
        }
      });
    }
  });

  socket.on('connect', function () {
    authRefreshRetried = false;
  });

  function onTypingIndicator(callback) {
    typingCallbacks.push(callback);
  }

  function removeTypingCallback(callback) {
    var idx = typingCallbacks.indexOf(callback);
    if (idx > -1) {
      typingCallbacks.splice(idx, 1);
    }
  }

  function tryConnect() {
    if (esPaginaOAuthGoogle()) {
      return;
    }
    var auth = useAuthStore();
    if (auth.token && auth.isAuthenticated && !socket.connected) {
      socket.auth = { token: auth.token };
      socket.connect();
    }
  }

  function updateSocketAuth() {
    var auth = useAuthStore();
    authRefreshRetried = false;
    socket.auth = { token: auth.token || '' };
    socket.disconnect();
    tryConnect();
  }

  if (typeof window !== 'undefined') {
    nuxtApp.hook('app:mounted', function () {
      setTimeout(function () {
        tryConnect();
      }, 150);
    });
  }

  return {
    provide: {
      socket: socket,
      updateSocketAuth: updateSocketAuth,
      onTypingIndicator: onTypingIndicator,
      removeTypingCallback: removeTypingCallback
    }
  };
});
