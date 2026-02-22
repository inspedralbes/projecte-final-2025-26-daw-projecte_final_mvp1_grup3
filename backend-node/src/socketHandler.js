'use strict';

/**
 * Gestor d'esdeveniments de Socket.io.
 */
var habitQueue = require('./queues/habitQueue');

/**
 * Defineix la lògica de recepció de missatges dels clients.
 */
function init(io) {
    io.on('connection', function (socket) {
        console.log('Client connectat:', socket.id);

        socket.on('habit_action', async function (payload) {
            try {
                var userId = socket.decoded_token.user_id; // O socket.user.id segons el teu JWT
                socket.join('user_' + userId);
                await habitQueue.pushToLaravel(payload.action, userId, payload);
            } catch (error) {
                console.error('Error gestionant habit_action:', error);
            }
        });

        // Escolta quan el frontend comunica un hàbit completat
        socket.on('habit_completed', async function (data) {
            try {
                console.log('Hàbit rebut:', data);
                // NOTA: Ajustem a la nova firma de pushToLaravel
                var userId = data.user_id;
                if (!userId && socket.decoded_token && socket.decoded_token.user_id) {
                    userId = socket.decoded_token.user_id;
                }
                socket.join('user_' + userId);
                await habitQueue.pushToLaravel('TOGGLE', userId, data);
            } catch (error) {
                console.error('Error gestionant habit_completed:', error);
            }
        });

        socket.on('disconnect', function () {
            console.log('Client desconnectat:', socket.id);
        });
    });
}

module.exports = {
    init: init
};
