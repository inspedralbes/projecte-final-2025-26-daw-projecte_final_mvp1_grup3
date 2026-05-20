<!--
  Component o pagina Nuxt: contingut.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<script setup>
/**
 * Reports de posts i comentaris: editar o eliminar contingut.
 */
definePageMeta({ layout: 'admin' });

import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { authFetch } from '~/composables/useApi.js';
import { useAdminSwal } from '~/composables/useAdminSwal.js';
import { useAdminReportsRealtime } from '~/composables/admin/useAdminReportsRealtime.js';

var { $socket } = useNuxtApp();
var { adminSuccess, adminError, adminConfirm } = useAdminSwal();
var perPage = ref(50);
var categoriaSocket = 'contingut';

var { data: reportsData, refresh: refreshReports } = useAuthFetch(function () {
  return '/api/admin/reports/contingut/1/' + perPage.value;
}, {
  key: 'admin_reports_contingut'
});

var reports = computed(function () {
  if (reportsData.value && reportsData.value.success) {
    return reportsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null);
var reportActiu = ref(null);
var textEdicio = ref('');
var carregantContingut = ref(false);

function tipusLabel(tipus) {
  return tipus === 'social_post' ? 'Post' : 'Comentari';
}

function onModeracioContingut(payload) {
  if (payload.entity !== 'content_moderation' || !reportActiu.value || !payload.data) {
    return;
  }
  var rep = reportActiu.value;
  var mateixContingut = rep.tipus === payload.data.tipus && rep.post_id === payload.data.post_id;
  if (!mateixContingut) {
    return;
  }
  if (payload.action === 'DELETED') {
    rep.eliminat = true;
  } else if (payload.action === 'UPDATED' && payload.data.target_contingut) {
    rep.target_contingut = payload.data.target_contingut;
    textEdicio.value = payload.data.target_contingut;
  }
}

useAdminReportsRealtime({
  categoria: categoriaSocket,
  reportsData: reportsData,
  refreshReports: refreshReports,
  onPayload: onModeracioContingut
});

function onPostUpdatedSocket(post) {
  if (!post || !post.id) {
    return;
  }
  if (reportActiu.value && reportActiu.value.tipus === 'social_post' && reportActiu.value.post_id === post.id) {
    reportActiu.value.target_contingut = post.content;
    textEdicio.value = post.content;
  }
  refreshReports();
}

function onPostDeletedSocket(data) {
  var postId = data && data.post_id;
  if (reportActiu.value && reportActiu.value.tipus === 'social_post' && reportActiu.value.post_id === postId) {
    reportActiu.value.eliminat = true;
  }
  refreshReports();
}

function onCommentUpdatedSocket(comment) {
  if (!comment || !comment.id) {
    return;
  }
  if (reportActiu.value && reportActiu.value.tipus === 'social_comment' && reportActiu.value.post_id === comment.id) {
    reportActiu.value.target_contingut = comment.content;
    textEdicio.value = comment.content;
  }
  refreshReports();
}

function onCommentDeletedSocket(data) {
  if (reportActiu.value && reportActiu.value.tipus === 'social_comment' && reportActiu.value.post_id === data.comment_id) {
    reportActiu.value.eliminat = true;
  }
  refreshReports();
}

onMounted(function () {
  if ($socket) {
    $socket.on('post_updated', onPostUpdatedSocket);
    $socket.on('post_deleted', onPostDeletedSocket);
    $socket.on('comment_updated', onCommentUpdatedSocket);
    $socket.on('comment_deleted', onCommentDeletedSocket);
  }
});

onBeforeUnmount(function () {
  if ($socket) {
    $socket.off('post_updated', onPostUpdatedSocket);
    $socket.off('post_deleted', onPostDeletedSocket);
    $socket.off('comment_updated', onCommentUpdatedSocket);
    $socket.off('comment_deleted', onCommentDeletedSocket);
  }
});

async function carregarContingut(rep) {
  carregantContingut.value = true;
  reportActiu.value = rep;
  textEdicio.value = rep.target_contingut || '';

  if (!rep.target_contingut) {
    try {
      var url = rep.tipus === 'social_post'
        ? '/api/admin/social/posts/' + rep.post_id
        : '/api/admin/social/comments/' + rep.post_id;
      var resposta = await authFetch(url);
      var json = await resposta.json();
      if (json.success && json.data) {
        textEdicio.value = json.data.content || '';
      }
    } catch (e) {
      console.error('Error carregant contingut:', e);
    }
  }
  carregantContingut.value = false;
}

function obreVeure(rep) {
  carregarContingut(rep);
  popupObert.value = 'veure';
}

function obreEditar(rep) {
  carregarContingut(rep);
  popupObert.value = 'editar';
}

function tancaPopup() {
  popupObert.value = null;
  reportActiu.value = null;
  textEdicio.value = '';
}

async function resoldreReport(reportId, senseConfirm) {
  if (!senseConfirm) {
    var confirmat = await adminConfirm({
      title: 'Vols marcar aquest report com a resolt?',
      confirmText: 'Resoldre',
      icon: 'question'
    });
    if (!confirmat.isConfirmed) {
      return;
    }
  }
  try {
    var resposta = await authFetch('/api/admin/reports/' + reportId + '?table=reports', {
      method: 'DELETE'
    });
    if (resposta.ok) {
      refreshReports();
      if (!senseConfirm) {
        await adminSuccess('Report resolt');
      }
    } else {
      await adminError('Error en resoldre el report');
    }
  } catch (e) {
    await adminError('Error de connexió', "No s'ha pogut completar l'acció.");
  }
}

async function guardarEdicio() {
  if (!reportActiu.value) {
    return;
  }
  var rep = reportActiu.value;
  var url = rep.tipus === 'social_post'
    ? '/api/admin/social/posts/' + rep.post_id
    : '/api/admin/social/comments/' + rep.post_id;

  try {
    var resposta = await authFetch(url, {
      method: 'PUT',
      body: JSON.stringify({ content: textEdicio.value })
    });
    var json = await resposta.json().catch(function () { return {}; });
    if (!resposta.ok) {
      throw new Error(json.message || 'Error en guardar');
    }
    tancaPopup();
    await refreshReports();
    await adminSuccess('Contingut actualitzat');
  } catch (e) {
    await adminError('Error', e && e.message ? e.message : 'Error en guardar');
  }
}

async function eliminarContingut(rep) {
  var tipusText = rep.tipus === 'social_post' ? 'post' : 'comentari';
  var confirmat = await adminConfirm({
    title: 'Vols eliminar aquest ' + tipusText + '?',
    text: 'Aquesta acció no es pot desfer.',
    confirmText: 'Sí, eliminar',
    icon: 'warning'
  });
  if (!confirmat.isConfirmed) {
    return;
  }

  var url = rep.tipus === 'social_post'
    ? '/api/admin/social/posts/' + rep.post_id
    : '/api/admin/social/comments/' + rep.post_id;

  try {
    var resposta = await authFetch(url, { method: 'DELETE' });
    if (!resposta.ok) {
      throw new Error('Error en eliminar');
    }
    await resoldreReport(rep.id, true);
    await adminSuccess(tipusText.charAt(0).toUpperCase() + tipusText.slice(1) + ' eliminat');
  } catch (e) {
    await adminError('Error', e && e.message ? e.message : 'Error en eliminar');
  }
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end flex-wrap gap-4">
      <div>
        <NuxtLink to="/admin/forum" class="text-[10px] font-black text-white/70 uppercase tracking-widest hover:text-white font-bricolage">← Fòrum</NuxtLink>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage mt-2">Reports de contingut</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Posts i comentaris reportats al fòrum social</p>
      </div>
      <button @click="refreshReports" class="bg-white/10 hover:bg-white/20 text-white border-2 border-white/20 px-6 py-3 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all font-bricolage">
        Actualitzar
      </button>
    </div>

    <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 overflow-hidden">
      <div v-if="reports.length === 0" class="text-center py-12 text-gray-500 font-comfortaa">
        <p class="font-bold text-lg text-gray-700">No hi ha reports de contingut pendents</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left font-comfortaa">
          <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 font-bricolage">
              <th class="pb-6">Tipus</th>
              <th class="pb-6">Contingut reportat</th>
              <th class="pb-6">Motiu denúncia</th>
              <th class="pb-6">Denunciant</th>
              <th class="pb-6 text-center">Data</th>
              <th class="pb-6 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100/50">
            <tr v-for="rep in reports" :key="'cr-' + rep.id">
              <td class="py-5">
                <span v-if="rep.tipus === 'social_post'" class="bg-blue-50 text-blue-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-blue-100 font-bricolage">Post</span>
                <span v-else class="bg-purple-50 text-purple-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-purple-100 font-bricolage">Comentari</span>
                <span v-if="rep.eliminat" class="block mt-1 text-[8px] font-black text-red-500 uppercase">Eliminat</span>
              </td>
              <td class="py-5 max-w-sm">
                <p class="text-xs font-bold text-gray-600 line-clamp-3">{{ rep.target_contingut || '(contingut no disponible)' }}</p>
                <p v-if="rep.target_autor" class="text-[9px] text-gray-400 font-bold uppercase mt-1">Autor: {{ rep.target_autor }} · ID {{ rep.post_id }}</p>
              </td>
              <td class="py-5 max-w-xs">
                <p class="text-xs font-bold text-gray-600 break-words">{{ rep.contingut || 'Sense motiu' }}</p>
              </td>
              <td class="py-5">
                <p class="text-xs font-black text-gray-800 font-bricolage">{{ rep.usuari }}</p>
              </td>
              <td class="py-5 text-center">
                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ rep.data || 'Fa poc' }}</span>
              </td>
              <td class="py-5 text-right space-x-2 whitespace-nowrap">
                <button @click="obreVeure(rep)" class="text-[10px] font-black text-blue-600 uppercase hover:text-blue-800 font-bricolage">Veure</button>
                <button @click="obreEditar(rep)" :disabled="rep.eliminat" class="text-[10px] font-black text-amber-600 uppercase hover:text-amber-800 font-bricolage disabled:opacity-40">Editar</button>
                <button @click="eliminarContingut(rep)" :disabled="rep.eliminat" class="text-[10px] font-black text-red-500 uppercase hover:text-red-700 font-bricolage disabled:opacity-40">Eliminar</button>
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
      <div v-if="popupObert && reportActiu" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-[20px] max-w-lg w-full p-8 shadow-2xl border border-white/80 font-comfortaa max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-black text-gray-800 uppercase font-bricolage">
              {{ popupObert === 'editar' ? 'Editar' : 'Veure' }} {{ tipusLabel(reportActiu.tipus) }}
            </h3>
            <button @click="tancaPopup" class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
          </div>

          <p v-if="reportActiu.target_autor" class="text-[10px] font-bold text-gray-400 uppercase mb-3">Autor: {{ reportActiu.target_autor }}</p>
          <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Motiu de la denúncia</p>
          <p class="text-xs text-gray-600 mb-4 bg-gray-50 p-3 rounded-[10px]">{{ reportActiu.contingut || '—' }}</p>

          <div v-if="carregantContingut" class="text-center py-6 text-gray-400 text-xs">Carregant...</div>
          <template v-else>
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Contingut</p>
            <textarea
              v-if="popupObert === 'editar'"
              v-model="textEdicio"
              rows="6"
              class="w-full bg-gray-50 border border-gray-200 rounded-[10px] p-4 text-xs font-bold text-gray-700 outline-none resize-none focus:border-blue-400"
            ></textarea>
            <p v-else class="text-sm text-gray-800 whitespace-pre-wrap bg-gray-50 p-4 rounded-[10px] border border-gray-100">{{ textEdicio || '(buit)' }}</p>
          </template>

          <div class="flex justify-end gap-3 mt-6">
            <button @click="tancaPopup" class="px-5 py-3 rounded-[10px] text-xs font-bold text-gray-500">Tancar</button>
            <button v-if="popupObert === 'editar'" @click="guardarEdicio" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-[10px] text-xs font-black uppercase font-bricolage">Guardar</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
