'use strict';


/**
 * Modul JavaScript ES5: snapshotScheduler.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var cron = require('node-cron');
var queuePublisher = require('../../infra/redis/queuePublisher');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var snapshotQueueKey = 'snapshot_queue';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function formatarDataAvui() {
  var avui = new Date();
  var any = avui.getFullYear();
  var mes = String(avui.getMonth() + 1);
  if (mes.length < 2) {
    mes = '0' + mes;
  }
  var dia = String(avui.getDate());
  if (dia.length < 2) {
    dia = '0' + dia;
  }
  return any + '-' + mes + '-' + dia;
}

/**
 * Inicialitza el scheduler de snapshots (23:59 diari).
 */
function initSnapshotScheduler() {
  cron.schedule('59 23 * * *', async function () {
    var dataFormatada = formatarDataAvui();
    var missatge = {
      event: 'snapshot:run',
      date: dataFormatada
    };

    try {
      await queuePublisher.publicarACua(snapshotQueueKey, missatge);
      console.log('[SnapshotQueue] Publicat snapshot:run per data:', dataFormatada);
    } catch (err) {
      console.error('[SnapshotQueue] Error publicant a snapshot_queue:', err);
    }
  });

  console.log('[SnapshotQueue] Scheduler iniciat (23:59 diari)');
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  initSnapshotScheduler: initSnapshotScheduler
};
