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

        // Escolta quan el frontend comunica un hàbit completat
        socket.on('habit_completed', async function (data) {
            try {
                console.log('Hàbit rebut:', data);
                // Enviem a Laravel via Redis
                await habitQueue.pushToLaravel(data);
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
