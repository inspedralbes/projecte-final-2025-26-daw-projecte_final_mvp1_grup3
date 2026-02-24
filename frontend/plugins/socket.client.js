import { io } from 'socket.io-client';

/**
 * Plugin de Socket.io per a Nuxt 3.
 * Injecta $socket a tota l'aplicació.
 * segueix les regles ES5 per al contingut de les funcions si és possible.
 */
export default defineNuxtPlugin(function (nuxtApp) {
    var config = useRuntimeConfig();
    var socketUrl = config.public.socketUrl || 'http://localhost:3001';

    var socket = io(socketUrl, {
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

    // Injectem al context de Nuxt
    return {
        provide: {
            socket: socket
        }
    };
});
