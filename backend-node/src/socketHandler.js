'use strict';

/**
 * Gestor d'esdeveniments de Socket.io.
 */
var habitQueue = require('./queues/habitQueue');
var plantillaQueue = require('./queues/plantillaQueue');
var adminQueue = require('./queues/adminQueue');

/**
 * Map: userId -> { nom, email, connected_at, socketId }
 * Per llistar usuaris connectats a l'admin.
 */
var usuarisConnectats = {};

/**
 * Defineix la lògica de recepció de missatges dels clients.
 */
function init(io) {
    io.on('connection', function (socket) {
        console.log('Client connectat:', socket.id);

        socket.on('habit_action', async function (payload) {
            try {
                var userId = 1;
                if (socket.decoded_token && socket.decoded_token.user_id) {
                    userId = socket.decoded_token.user_id;
                }
                socket.join('user_' + userId);
                await habitQueue.pushToLaravel(payload.action, userId, payload);
            } catch (error) {
                console.error('Error gestionant habit_action:', error);
            }
        });

        socket.on('plantilla_action', async function (payload) {
            try {
                var userId = socket.decoded_token.user_id; // O socket.user.id segons el teu JWT
                socket.join('user_' + userId);
                await plantillaQueue.pushToLaravel(payload.action, userId, payload);
            } catch (error) {
                console.error('Error gestionant plantilla_action:', error);
            }
        });

        // Escolta quan el frontend comunica un hàbit completat
        socket.on('habit_completed', async function (data) {
            try {
                console.log('Hàbit rebut:', data);
                var userId = data.user_id;
                if (!userId && socket.decoded_token && socket.decoded_token.user_id) {
                    userId = socket.decoded_token.user_id;
                }
                if (!userId) {
                    userId = 1;
                }
                socket.join('user_' + userId);
                await habitQueue.pushToLaravel('TOGGLE', userId, data);
            } catch (error) {
                console.error('Error gestionant habit_completed:', error);
            }
        });

        socket.on('admin_join', function (payload) {
            var adminId = 1;
            if (payload && payload.admin_id) {
                adminId = payload.admin_id;
            }
            socket.adminId = adminId;
            socket.join('admin_' + adminId);
            console.log('Admin ' + adminId + ' units a la sala admin_' + adminId);
        });

        socket.on('admin_action', async function (payload) {
            try {
                var adminId = 1;
                if (payload && payload.admin_id) {
                    adminId = payload.admin_id;
                }
                if (socket.adminId) {
                    adminId = socket.adminId;
                }
                socket.join('admin_' + adminId);
                await adminQueue.pushToLaravel(
                    payload.action || 'CREATE',
                    adminId,
                    payload.entity || 'plantilla',
                    payload.data || {}
                );
            } catch (error) {
                console.error('Error gestionant admin_action:', error);
            }
        });

        socket.on('admin:request_connected', function () {
            var llista = [];
            for (var userId in usuarisConnectats) {
                if (usuarisConnectats.hasOwnProperty(userId)) {
                    var u = usuarisConnectats[userId];
                    llista.push({
                        user_id: userId,
                        nom: u.nom || '',
                        email: u.email || '',
                        connected_at: u.connected_at || null
                    });
                }
            }
            socket.emit('admin:connected_users', llista);
        });

        socket.on('user_register', function (data) {
            var userId = String(socket.id);
            if (data && data.user_id) {
                userId = String(data.user_id);
            }
            var nom = 'Usuari';
            if (data && data.nom) {
                nom = data.nom;
            }
            var email = '';
            if (data && data.email) {
                email = data.email;
            }
            usuarisConnectats[userId] = {
                nom: nom,
                email: email,
                connected_at: new Date().toISOString(),
                socketId: socket.id
            };
            socket.userId = userId;
        });

        socket.on('disconnect', function () {
            if (socket.userId && usuarisConnectats[socket.userId]) {
                delete usuarisConnectats[socket.userId];
            }
            console.log('Client desconnectat:', socket.id);
        });
    });
}

module.exports = {
    init: init
};
