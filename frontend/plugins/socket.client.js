import { io } from 'socket.io-client';

/**
 * Plugin de Socket.io per a Nuxt 3.
 * Injecta $socket a tota l'aplicació.
 * Conecta quan el token està disponible (després de loadFromStorage o login).
 * Segueix les regles ES5 per al contingut de les funcions si és possible.
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

    // Listener global per a confirmacions d'admin
    socket.on('admin_action_confirmed', function (payload) {
        console.log('[Socket] Acció Admin Confirmada:', payload);
    });

    socket.on('connect_error', function (err) {
        console.error('[Socket] Error de connexió:', err.message);
        // Si falla per auth, intentar refrescar token i reconnectar una vegada
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

    // Connecta quan el token està disponible
    function tryConnect() {
        var auth = useAuthStore();
        if (auth.token && auth.isAuthenticated && !socket.connected) {
            socket.auth = { token: auth.token };
            socket.connect();
        }
    }

    // Permet reconnectar amb el token actual (després del login)
    function updateSocketAuth() {
        var auth = useAuthStore();
        authRefreshRetried = false;
        socket.auth = { token: auth.token || '' };
        socket.disconnect();
        tryConnect();
    }

    if (typeof window !== 'undefined') {
        // Endarrerir la connexió fins que l'auth estigui hidratat (0.auth-init carrega localStorage)
        nuxtApp.hook('app:mounted', function () {
            setTimeout(function () {
                tryConnect();
            }, 150);
        });
    }

    return {
        provide: {
            socket: socket,
            updateSocketAuth: updateSocketAuth
        }
    };
});
