import { io } from 'socket.io-client';
import { useFriendshipStore } from '~/stores/useFriendshipStore.js';
import { useChatStore } from '~/stores/useChatStore.js';
import { useClanStore } from '~/stores/useClanStore.js';
import { useAuthStore } from '~/stores/useAuthStore.js';
import { useShopStore } from '~/stores/useShopStore.js';
import { useGameStore } from '~/stores/gameStore.js';

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
    var typingCallbacks = [];

    // Listener global per a confirmacions d'admin
    socket.on('admin_action_confirmed', function (payload) {
        console.log('[Socket] Acció Admin Confirmada:', payload);
    });

    // Listeners per al fòrum social (temps real)
    socket.on('new_post', function (post) {
        var socialStore = useSocialStore();
        socialStore.handleNewPost(post);
    });

    socket.on('new_comment', function (comment) {
        var socialStore = useSocialStore();
        socialStore.handleNewComment(comment);
    });

    socket.on('like_update', function (data) {
        var socialStore = useSocialStore();
        socialStore.handleLikeUpdate(data);
    });

    socket.on('new_friend_request', function (data) {
        console.log('[Socket] Nova sol·licitud d\'amistat:', data);
        var friendshipStore = useFriendshipStore();
        if (friendshipStore) {
            friendshipStore.fetchPendingRequests();
        }
    });

    socket.on('friend_request_accepted', function (data) {
        console.log('[Socket] Sol·licitud d\'amistat acceptada:', data);
        var friendshipStore = useFriendshipStore();
        if (friendshipStore) {
            friendshipStore.fetchFriendsList();
        }
    });

    socket.on('clan_member_joined', function (data) {
        console.log('[Socket] Clan member joined:', data);
        var clanStore = useClanStore();
        if (clanStore && data && data.clan_id) {
            clanStore.fetchMembers(data.clan_id);
        }
    });

    socket.on('clan_request_received', function (data) {
        console.log('[Socket] Nova sol·licitud de clan rebuda:', data);
        var clanStore = useClanStore();
        if (clanStore && data && data.clan_id) {
            clanStore.fetchPendingRequests(data.clan_id);
        }
    });

    socket.on('clan_request_accepted', function (data) {
        console.log('[Socket] Clan request accepted:', data);
        var clanStore = useClanStore();
        if (clanStore && data && data.clan_id) {
            clanStore.fetchMembers(data.clan_id);
        }
    });

    socket.on('clan_member_left', function (data) {
        console.log('[Socket] Clan member left:', data);
        var clanStore = useClanStore();
        if (clanStore && data && data.clan_id) {
            clanStore.fetchMembers(data.clan_id);
        }
    });

    socket.on('shop_event', function (data) {
        console.log('[Socket] shop_event:', data);
        var shopStore = useShopStore();
        if (shopStore) {
            shopStore.aplicarEvent(data);
        }
    });

    // Mantenim sincronitzat el saldo i la ratxa quan arriba un update_xp
    // generat per la botiga (compra/consum). Altres orígens (hàbits, ruleta)
    // tenen els seus propis consumidors a home.vue, pero aquí cobrim tota la
    // sessió per a una sincronització fiable de monedes a les pàgines /shop
    // i /inventari.
    socket.on('update_xp', function (data) {
        if (!data) return;
        try {
            var gameStore = useGameStore();
            if (gameStore && typeof gameStore.actualitzarDesDeXpUpdate === 'function') {
                gameStore.actualitzarDesDeXpUpdate(data);
            }
        } catch (_) {
            // Silent fallback si el store encara no està disponible.
        }
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
        socket.emit("get_online_users", function (users) {
            var chatStore = useChatStore();
            if (chatStore) {
                // Parse them to ints just in case
                var intUsers = (users || []).map(function(u) { return parseInt(u); });
                chatStore.setOnlineUsers(intUsers);
            }
        });
    });

    socket.on('user_status', function (data) {
        var chatStore = useChatStore();
        if (chatStore && data) {
            chatStore.updateUserStatus(data.userId, data.online);
        }
    });

    socket.on('new_private_message', function (data) {
        console.log('[Socket] Nou missatge privat:', data);
        var chatStore = useChatStore();
        if (chatStore && data.sender_id) {
            chatStore.receiveMessage(data.sender_id, {
                id: data.id || Date.now(),
                sender_id: data.sender_id,
                receiver_id: data.receiver_id,
                contingut: data.message,
                created_at: data.created_at || new Date().toISOString()
            });
        }
    });

    socket.on('typing_indicator', function (data) {
        console.log('[Socket] Typing indicator:', data);
        typingCallbacks.forEach(function(cb) { cb(data); });
    });

    function onTypingIndicator(callback) {
        typingCallbacks.push(callback);
    }

    function removeTypingCallback(callback) {
        var idx = typingCallbacks.indexOf(callback);
        if (idx > -1) typingCallbacks.splice(idx, 1);
    }

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
            updateSocketAuth: updateSocketAuth,
            onTypingIndicator: onTypingIndicator,
            removeTypingCallback: removeTypingCallback
        }
    };
});
