<script setup>
/**
 * Moderació del Fòrum (Admin).
 * Gestió de fils, comentaris i bloquejos.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Reports via API
var { data: reportsData, refresh: refreshReports } = useFetch('/api/admin/reports/1/20', {
  baseURL: config.public.apiUrl,
  key: 'admin_reports_list'
});

var reports = computed(function() {
  if (reportsData.value && reportsData.value.success) {
    return reportsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'detall'
var reportSeleccionat = ref(null);

// 2. METHODS (FUNCTION)
function obreDetall(r) {
  reportSeleccionat.value = r;
  popupObert.value = 'detall';
}

function tancaPopup() {
  popupObert.value = null;
}

// Lifecycle i Sockets
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'report' && payload.success) {
        refreshReports();
      }
    });
  }
});

function ignorarReport() {
  if (!$socket || !reportSeleccionat.value) return;
  
  $socket.emit('admin_action', {
    action: 'UPDATE',
    entity: 'report',
    data: {
      id: reportSeleccionat.value.id,
      estat: 'ignorat'
    }
  });
  
  tancaPopup();
}

function eliminarContingut() {
  if (!$socket || !reportSeleccionat.value) return;
  
  $socket.emit('admin_action', {
    action: 'DELETE',
    entity: 'report_content',
    data: {
      report_id: reportSeleccionat.value.id,
      post_id: reportSeleccionat.value.post_id // Suposant que ens arriba l'ID del post
    }
  });
  
  tancaPopup();
}
</script>

<template>
  <div class="space-y-12 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Moderació del Fòrum</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Control de la comunitat i contingut</p>
      </div>
      <div class="flex gap-4">
        <span class="bg-red-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-100">{{ reports.length }} Reports Pendents</span>
      </div>
    </div>

    <!-- Llista de Reports -->
    <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100">
      <h3 class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-10">Alertes recents</h3>
      
      <div class="space-y-6">
        <div v-for="r in reports" :key="r.id" class="p-8 rounded-[2rem] border border-gray-50 flex items-center justify-between hover:bg-red-50/10 hover:border-red-100 transition-all cursor-pointer" @click="obreDetall(r)">
          <div class="flex items-center gap-6">
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-[10px] font-black text-red-600 uppercase">ALERTA</div>
            <div>
              <p class="font-black text-gray-800 text-sm uppercase">Reportat per {{ r.usuari }}</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Tipus: {{ r.tipus }} • Fa {{ r.data }}</p>
            </div>
          </div>
          <p class="text-xs font-bold text-gray-500 italic max-w-xs truncate">"{{ r.contingut }}"</p>
        </div>

        <div v-if="reports.length === 0" class="py-20 text-center opacity-30">
          <p class="text-xs font-bold uppercase tracking-widest mt-4">Comunitat neta de reports</p>
        </div>
      </div>
    </div>

    <!-- MODAL (Popups) -->
    <Transition 
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="popupObert" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-gray-900/60 backdrop-blur-md" @click.self="tancaPopup">
        <div class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col border border-white/20">
          
          <div class="p-10 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">Detall de la Denúncia</h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Seguretat de la comunitat</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">X</button>
          </div>

          <div class="p-12 space-y-6">
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-300 uppercase tracking-widest ml-4">Usuari Denunciat</label>
              <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100">
                <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ reportSeleccionat?.usuari }}</p>
              </div>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black text-gray-300 uppercase tracking-widest ml-4">Contingut Conflictiv</label>
              <div class="p-8 bg-red-50/30 rounded-3xl border border-red-100">
                <p class="text-sm font-bold text-red-800 leading-relaxed italic">"{{ reportSeleccionat?.contingut }}"</p>
              </div>
            </div>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-between gap-4">
            <button @click="ignorarReport" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Ignorar Report</button>
            <div class="flex gap-4">
              <button @click="eliminarContingut" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-xl shadow-red-100 hover:bg-red-700 transition-all">Eliminar Post</button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
