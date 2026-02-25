import { io } from 'socket.io-client';

/**
 * Plugin de Socket.io per a Nuxt 3.
 * Injecta $socket a tota l'aplicació.
 * segueix les regles ES5 per al contingut de les funcions si és possible.
 */
export default defineNuxtPlugin(function (nuxtApp) {
    var config = useRuntimeConfig();
    var socketUrl = config.public.socketUrl || 'http://localhost:3001';
    var authStore = useAuthStore();
    if (typeof window !== 'undefined') authStore.loadFromStorage();

    var socket = io(socketUrl, {
        auth: { token: authStore.token || '' },
        autoConnect: true,
        transports: ['websocket']
    });

    // Listener global per a confirmacions d'admin
    socket.on('admin_action_confirmed', function (payload) {
        console.log('[Socket] Acció Admin Confirmada:', payload);
        // Aquí es podria disparar un event local o actualitzar un store de Pinia
    });

    socket.on('connect_error', function (err) {
        console.error('[Socket] Error de connexió:', err.message);
    });

    // Permet reconnectar amb el token actual (després del login)
    function updateSocketAuth() {
        var authStore = useAuthStore();
        socket.auth = { token: authStore.token || '' };
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
