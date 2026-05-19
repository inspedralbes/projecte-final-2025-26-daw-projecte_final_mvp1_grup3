<!--
  Component o pagina Nuxt: usuaris.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<script setup>
/**
 * Reports d'usuaris: prohibir / desprohibir comptes.
 */
definePageMeta({ layout: 'admin' });

import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { authFetch } from '~/composables/useApi.js';
import { useAdminSwal } from '~/composables/useAdminSwal.js';

var { $socket } = useNuxtApp();
var { adminSuccess, adminError, adminConfirm } = useAdminSwal();
var perPage = ref(50);
var categoriaSocket = 'usuaris';

var { data: reportsData, refresh: refreshReports } = useAuthFetch(function () {
  return '/api/admin/reports/usuaris/1/' + perPage.value;
}, {
  key: 'admin_reports_usuaris'
});

var reports = computed(function () {
  if (reportsData.value && reportsData.value.success) {
    return reportsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null);
var usuariSeleccionat = ref(null);
var formulari = ref({
  motiuProhibicio: '',
  duradaProhibicio: 'permanent'
});

function obreProhibir(reportedUserId, reportedUserNom) {
  usuariSeleccionat.value = { id: reportedUserId, nom: reportedUserNom };
  formulari.value.motiuProhibicio = '';
  formulari.value.duradaProhibicio = 'permanent';
  popupObert.value = 'prohibir';
}

function tancaPopup() {
  popupObert.value = null;
  usuariSeleccionat.value = null;
}

function onAdminReportUpdated(payload) {
  if (!payload || !payload.success) {
    return;
  }
  var cat = payload.data && payload.data.categoria;
  if (cat && cat !== categoriaSocket) {
    return;
  }
  refreshReports();
}

onMounted(function () {
  if ($socket) {
    $socket.emit('admin_join', {});
    $socket.on('admin_report_updated', onAdminReportUpdated);
  }
});

onBeforeUnmount(function () {
  if ($socket) {
    $socket.off('admin_report_updated', onAdminReportUpdated);
  }
});

async function resoldreReport(reportId) {
  var confirmat = await adminConfirm({
    title: 'Vols marcar aquest report com a resolt?',
    text: "S'eliminarà de la llista de reports pendents.",
    confirmText: 'Resoldre',
    icon: 'question'
  });
  if (!confirmat.isConfirmed) {
    return;
  }
  try {
    var resposta = await authFetch('/api/admin/reports/' + reportId + '?table=reports_usuari', {
      method: 'DELETE'
    });
    if (resposta.ok) {
      refreshReports();
      await adminSuccess('Report resolt');
    } else {
      await adminError('Error en resoldre el report');
    }
  } catch (e) {
    console.error('Error resolent report:', e);
    await adminError('Error de connexió', "No s'ha pogut enviar la petició.");
  }
}

async function confirmarProhibicio() {
  if (!usuariSeleccionat.value) {
    return;
  }
  try {
    var resposta = await authFetch('/api/admin/usuaris/' + usuariSeleccionat.value.id + '/prohibir', {
      method: 'PATCH',
      body: JSON.stringify({
        prohibit: true,
        durada_prohibicio: formulari.value.duradaProhibicio,
        motiu_prohibicio: formulari.value.motiuProhibicio || 'Violació de les normes de la comunitat'
      })
    });
    var json = await resposta.json().catch(function () { return {}; });
    if (!resposta.ok) {
      throw new Error(json.message || json.error || 'Error en prohibir');
    }
    tancaPopup();
    await refreshReports();
    await adminSuccess('Usuari prohibit');
  } catch (e) {
    await adminError('Error', e && e.message ? e.message : 'Error en prohibir');
  }
}

async function desprohibirUsuari(reportedUserId) {
  var confirmat = await adminConfirm({
    title: "Vols tornar a permetre l'accés a aquest usuari?",
    confirmText: 'Sí, desprohibir',
    icon: 'question'
  });
  if (!confirmat.isConfirmed) {
    return;
  }
  try {
    var resposta = await authFetch('/api/admin/usuaris/' + reportedUserId + '/prohibir', {
      method: 'PATCH',
      body: JSON.stringify({ prohibit: false })
    });
    if (!resposta.ok) {
      throw new Error('Error en desprohibir');
    }
    await refreshReports();
    await adminSuccess('Usuari desprohibit');
  } catch (e) {
    await adminError('Error', e && e.message ? e.message : 'Error en desprohibir');
  }
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end flex-wrap gap-4">
      <div>
        <NuxtLink to="/admin/forum" class="text-[10px] font-black text-white/70 uppercase tracking-widest hover:text-white font-bricolage">← Fòrum</NuxtLink>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage mt-2">Reports d'usuaris</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Prohibir o desprohibir usuaris denunciats</p>
      </div>
      <button @click="refreshReports" class="bg-white/10 hover:bg-white/20 text-white border-2 border-white/20 px-6 py-3 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all font-bricolage">
        Actualitzar
      </button>
    </div>

    <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 overflow-hidden">
      <div v-if="reports.length === 0" class="text-center py-12 text-gray-500 font-comfortaa">
        <p class="font-bold text-lg text-gray-700">No hi ha reports d'usuaris pendents</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left font-comfortaa">
          <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 font-bricolage">
              <th class="pb-6">Usuari reportat</th>
              <th class="pb-6">Motiu / Detalls</th>
              <th class="pb-6">Denunciant</th>
              <th class="pb-6 text-center">Data</th>
              <th class="pb-6 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100/50">
            <tr v-for="rep in reports" :key="'ur-' + rep.id">
              <td class="py-5">
                <p class="font-black text-gray-800 text-sm font-bricolage">{{ rep.reported_user_nom || 'ID ' + rep.post_id }}</p>
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">ID: {{ rep.post_id }}</p>
                <span v-if="rep.reported_user_prohibit" class="inline-block mt-1 bg-red-50 text-red-600 px-2 py-0.5 rounded text-[8px] font-black uppercase">Prohibit</span>
              </td>
              <td class="py-5 max-w-xs">
                <p class="text-xs font-bold text-gray-600 break-words">{{ rep.contingut || 'Sense detalls' }}</p>
              </td>
              <td class="py-5">
                <p class="text-xs font-black text-gray-800 font-bricolage">{{ rep.usuari }}</p>
              </td>
              <td class="py-5 text-center">
                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ rep.data || 'Fa poc' }}</span>
              </td>
              <td class="py-5 text-right space-x-3 whitespace-nowrap">
                <button @click="desprohibirUsuari(rep.post_id)" class="text-[10px] font-black text-green-600 uppercase hover:text-green-800 font-bricolage">Desbanear</button>
                <button @click="obreProhibir(rep.post_id, rep.reported_user_nom)" class="text-[10px] font-black text-red-500 uppercase hover:text-red-700 font-bricolage">Prohibir</button>
                <button @click="resoldreReport(rep.id)" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white px-3 py-1.5 rounded-[8px] text-[9px] font-black uppercase font-bricolage">Resoldre</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="popupObert === 'prohibir'" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-[20px] max-w-md w-full p-8 shadow-2xl border border-white/80 font-comfortaa">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-800 uppercase font-bricolage">Prohibir accés</h3>
            <button @click="tancaPopup" class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
          </div>
          <div class="mb-4">
            <p class="text-xs text-gray-500 font-bold uppercase mb-2">Usuari reportat</p>
            <div class="bg-red-50 text-red-700 font-black text-sm p-3 rounded-[10px] border border-red-100 font-bricolage">
              {{ usuariSeleccionat?.nom || 'Usuari #' + usuariSeleccionat?.id }}
            </div>
          </div>
          <div class="space-y-5">
            <div>
              <label for="duradaBan" class="block text-[10px] font-black text-gray-400 uppercase mb-2 font-bricolage">Durada del ban</label>
              <select id="duradaBan" v-model="formulari.duradaProhibicio" class="w-full bg-gray-50 border border-gray-200/80 rounded-[10px] px-4 py-3 text-xs font-bold text-gray-700 outline-none">
                <option value="1_dia">1 Dia</option>
                <option value="3_dies">3 Dies</option>
                <option value="7_dies">7 Dies</option>
                <option value="30_dies">30 Dies</option>
                <option value="permanent">Permanent</option>
              </select>
            </div>
            <div>
              <label for="motiuBan" class="block text-[10px] font-black text-gray-400 uppercase mb-2 font-bricolage">Motiu</label>
              <textarea id="motiuBan" v-model="formulari.motiuProhibicio" rows="3" placeholder="Motiu del banejament..." class="w-full bg-gray-50 border border-gray-200/80 rounded-[10px] p-4 text-xs font-bold text-gray-700 outline-none resize-none"></textarea>
            </div>
          </div>
          <div class="flex justify-end gap-3 mt-8">
            <button @click="tancaPopup" class="px-5 py-3 rounded-[10px] text-xs font-bold text-gray-500">Cancelar</button>
            <button @click="confirmarProhibicio" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-[10px] text-xs font-black uppercase font-bricolage">Confirmar ban</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
