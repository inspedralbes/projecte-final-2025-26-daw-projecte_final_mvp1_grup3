'use strict';

/**
 * Cua de snapshots: Scheduler que publica a snapshot_queue cada dia a les 23:59.
 */
var redis = require('redis');
var cron = require('node-cron');

//================================ VARIABLES ===================================

var client = null;
var snapshotQueueKey = 'snapshot_queue';

//================================ FUNCIONS ====================================

/**
 * Obté el client de Redis connectat.
 */
async function getClient() {
    if (client) {
        return client;
    }
    var host = process.env.REDIS_HOST || '127.0.0.1';
    var port = parseInt(process.env.REDIS_PORT || '6379', 10);

    client = redis.createClient({
        socket: {
            host: host,
            port: port
        }
    });

    client.on('error', function (err) {
        console.error('Error Redis Client (snapshotQueue):', err);
    });

    await client.connect();
    return client;
}

/**
 * Inicialitza el scheduler de snapshots.
 * Publica a snapshot_queue cada dia a les 23:59.
 */
function initSnapshotScheduler() {
    cron.schedule('59 23 * * *', async function () {
        var avui = new Date();
        var any = avui.getFullYear();
        var mes = String(avui.getMonth() + 1).padStart(2, '0');
        var dia = String(avui.getDate()).padStart(2, '0');
        var dataFormatada = any + '-' + mes + '-' + dia;

        var missatge = JSON.stringify({
            event: 'snapshot:run',
            date: dataFormatada
        });

        try {
            var c = await getClient();
            await c.lPush(snapshotQueueKey, missatge);
            console.log('[SnapshotQueue] Publicat snapshot:run per data:', dataFormatada);
        } catch (err) {
            console.error('[SnapshotQueue] Error publicant a snapshot_queue:', err);
        }
    });

    console.log('[SnapshotQueue] Scheduler iniciat (23:59 diari)');
}

//================================ EXPORTS =====================================

module.exports = {
    initSnapshotScheduler: initSnapshotScheduler
};
