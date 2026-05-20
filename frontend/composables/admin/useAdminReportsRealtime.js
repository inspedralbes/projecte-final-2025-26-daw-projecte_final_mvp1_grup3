/**
 * Modul JavaScript ES5: useAdminReportsRealtime.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 */

import { onMounted, onBeforeUnmount } from 'vue';
import { useAdminSocket } from '~/composables/admin/useAdminSocket.js';

function mateixReport(a, b) {
  return a && b && a.id === b.id && (a.table || '') === (b.table || '');
}

function afegirReportALlista(reportsData, fila) {
  if (!reportsData.value || !reportsData.value.success || !reportsData.value.data) {
    return false;
  }
  var llista = reportsData.value.data.data || [];
  var i;
  for (i = 0; i < llista.length; i++) {
    if (mateixReport(llista[i], fila)) {
      return false;
    }
  }
  var meta = reportsData.value.data.meta || {};
  reportsData.value = {
    success: true,
    data: {
      data: [fila].concat(llista),
      meta: Object.assign({}, meta, {
        total: (meta.total || llista.length) + 1
      })
    }
  };
  return true;
}

function treureReportDeLlista(reportsData, id, table) {
  if (!reportsData.value || !reportsData.value.success || !reportsData.value.data) {
    return false;
  }
  var llista = reportsData.value.data.data || [];
  var nova = [];
  var i;
  var eliminat = false;
  for (i = 0; i < llista.length; i++) {
    var r = llista[i];
    if (r.id === id && (!table || r.table === table)) {
      eliminat = true;
    } else {
      nova.push(r);
    }
  }
  if (!eliminat) {
    return false;
  }
  var meta = reportsData.value.data.meta || {};
  reportsData.value = {
    success: true,
    data: {
      data: nova,
      meta: Object.assign({}, meta, {
        total: Math.max(0, (meta.total || llista.length) - 1)
      })
    }
  };
  return true;
}

/**
 * Uneix el socket admin i actualitza la llista de reports en temps real.
 *
 * @param {object} opts
 * @param {string} opts.categoria - 'usuaris' | 'contingut'
 * @param {import('vue').Ref} opts.reportsData - ref de useAuthFetch
 * @param {function} opts.refreshReports
 * @param {function} [opts.onPayload] - callback extra (p.ex. moderació de contingut)
 */
export function useAdminReportsRealtime(opts) {
  var categoriaSocket = opts.categoria;
  var reportsData = opts.reportsData;
  var refreshReports = opts.refreshReports;
  var onPayload = opts.onPayload;
  var adminSocket = useAdminSocket();

  function onAdminReportUpdated(payload) {
    if (!payload || payload.success === false) {
      return;
    }
    var cat = payload.data && payload.data.categoria;
    if (cat && cat !== categoriaSocket) {
      return;
    }
    if (typeof onPayload === 'function') {
      onPayload(payload);
    }
    if (payload.action === 'CREATE' && payload.data && payload.data.id) {
      if (afegirReportALlista(reportsData, payload.data)) {
        return;
      }
    }
    if (payload.action === 'DELETE' && payload.data && payload.data.id) {
      if (treureReportDeLlista(reportsData, payload.data.id, payload.data.table)) {
        return;
      }
    }
    refreshReports();
  }

  onMounted(function () {
    adminSocket.ensureAdminJoin();
    adminSocket.onReportUpdated(onAdminReportUpdated);
  });

  onBeforeUnmount(function () {
    adminSocket.offReportUpdated(onAdminReportUpdated);
  });
}
