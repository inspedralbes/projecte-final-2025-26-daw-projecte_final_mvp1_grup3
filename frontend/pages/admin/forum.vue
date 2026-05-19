<script setup>
/**
 * Gestió de Reports / Denúncies (Admin).
 * Reutilitza la pàgina de Fòrum per gestionar de forma centralitzada
 * els reports rebuts d'usuaris, comentaris o posts.
 */
definePageMeta({ layout: 'admin' });

import { ref, computed, onMounted } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Reports via API
var perPage = ref(50);
var { data: reportsData, refresh: refreshReports } = useAuthFetch(function() {
  return '/api/admin/reports/1/' + perPage.value;
}, {
  key: 'admin_reports_list'
});

var reports = computed(function() {
  if (reportsData.value && reportsData.value.success) {
    return reportsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'prohibir'
var usuariSeleccionat = ref(null);
var formulari = ref({
  motiuProhibicio: "",
  duradaProhibicio: "permanent"
});

// 2. METODES
function obreProhibir(reportedUserId, reportedUserNom) {
  usuariSeleccionat.value = {
    id: reportedUserId,
    nom: reportedUserNom
  };
  formulari.value.motiuProhibicio = "";
  formulari.value.duradaProhibicio = "permanent";
  popupObert.value = 'prohibir';
}

function tancaPopup() {
  popupObert.value = null;
  usuariSeleccionat.value = null;
}

// Escoltarem confirmacions de socket per refrescar
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'usuari' && payload.success) {
        refreshReports();
      }
    });
  }
});

async function resoldreReport(reportId, tableName) {
  if (!confirm("Vols marcar aquest report com a resolt i eliminar-lo de la llista?")) return;
  try {
    const resposta = await authFetch('/api/admin/reports/' + reportId + '?table=' + tableName, {
      method: 'DELETE'
    });
    if (resposta.ok) {
      refreshReports();
    } else {
      alert("Error en resoldre el report.");
    }
  } catch (e) {
    console.error("Error resolent report:", e);
    alert("Error de connexió a l'enviar la petició.");
  }
}

function confirmarProhibicio() {
  if (!$socket || !usuariSeleccionat.value) return;

  var duradesLabels = {
    "1_dia": "1 Dia",
    "3_dies": "3 Dies",
    "7_dies": "7 Dies",
    "30_dies": "30 Dies",
    "permanent": "Permanent"
  };
  var durada = duradesLabels[formulari.value.duradaProhibicio] || "Permanent";
  var motiuFinal = "[" + durada + "] " + (formulari.value.motiuProhibicio || "Violació de les normes de la comunitat");

  $socket.emit('admin_action', {
    action: 'UPDATE',
    entity: 'usuari',
    data: {
      id: usuariSeleccionat.value.id,
      prohibit: true,
      motiu_prohibicio: motiuFinal
    }
  });

  tancaPopup();
  setTimeout(() => {
    refreshReports();
  }, 500);
}

function desprohibirUsuari(reportedUserId) {
  if (!$socket) return;
  if (!confirm("Vols tornar a permetre l'accés a aquest usuari?")) return;

  $socket.emit('admin_action', {
    action: 'UPDATE',
    entity: 'usuari',
    data: {
      id: reportedUserId,
      prohibit: false,
      motiu_prohibicio: null
    }
  });

  setTimeout(() => {
    refreshReports();
  }, 500);
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Fòrum & Reports</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Moderació de denúncies i contingut reportat</p>
      </div>
      <button @click="refreshReports" class="bg-white/10 hover:bg-white/20 text-white border-2 border-white/20 px-6 py-3 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2 font-bricolage">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18" />
        </svg>
        Actualitzar
      </button>
    </div>

    <!-- Taula de Reports en targeta Bento -->
    <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 overflow-hidden">
      <div v-if="reports.length === 0" class="text-center py-12 text-gray-500 font-comfortaa">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="font-bold text-lg text-gray-700">No hi ha reports pendents</p>
        <p class="text-xs text-gray-400 mt-1">La comunitat de Loopy està neta i lliure d'infraccions.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left font-comfortaa">
          <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 font-bricolage">
              <th class="pb-6">Tipus</th>
              <th class="pb-6">Objectiu (Reportat)</th>
              <th class="pb-6">Motiu / Detalls</th>
              <th class="pb-6">Denunciant</th>
              <th class="pb-6 text-center">Data</th>
              <th class="pb-6 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100/50">
            <tr v-for="rep in reports" :key="rep.id" class="group transition-all">
              <!-- Tipus -->
              <td class="py-5">
                <span v-if="rep.tipus === 'user'" class="bg-yellow-50 text-yellow-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-yellow-100 font-bricolage">Usuari</span>
                <span v-else-if="rep.tipus === 'social_post'" class="bg-blue-50 text-blue-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-blue-100 font-bricolage">Post</span>
                <span v-else class="bg-purple-50 text-purple-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-purple-100 font-bricolage">Comentari</span>
              </td>

              <!-- Objectiu -->
              <td class="py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-[8px] bg-gray-100 text-gray-600 flex items-center justify-center font-black text-xs font-bricolage">
                    ID
                  </div>
                  <div>
                    <p class="font-black text-gray-800 text-sm tracking-tight font-bricolage">
                      {{ rep.tipus === 'user' ? (rep.reported_user_nom || 'Usuari ID: ' + rep.post_id) : 'ID Contingut: ' + rep.post_id }}
                    </p>
                    <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">ID Target: {{ rep.post_id }}</p>
                  </div>
                </div>
              </td>

              <!-- Motiu / Detalls -->
              <td class="py-5 max-w-xs">
                <p class="text-xs font-bold text-gray-600 leading-relaxed break-words">{{ rep.contingut || 'Sense detalls addicionals' }}</p>
              </td>

              <!-- Denunciant -->
              <td class="py-5">
                <p class="text-xs font-black text-gray-800 font-bricolage leading-none">{{ rep.usuari }}</p>
              </td>

              <!-- Data -->
              <td class="py-5 text-center">
                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ rep.data || 'Fa poc' }}</span>
              </td>

              <!-- Accions -->
              <td class="py-5 text-right space-x-3 whitespace-nowrap">
                <!-- Accions sobre l'usuari reportat (si és tipus user) -->
                <template v-if="rep.tipus === 'user'">
                  <button @click="desprohibirUsuari(rep.post_id)" class="text-[10px] font-black text-green-600 uppercase hover:text-green-800 transition-colors font-bricolage">🔓 Desbanear</button>
                  <button @click="obreProhibir(rep.post_id, rep.reported_user_nom)" class="text-[10px] font-black text-red-500 uppercase hover:text-red-700 transition-colors font-bricolage">🚫 Prohibir</button>
                </template>
                <button @click="resoldreReport(rep.id, rep.table)" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white px-3 py-1.5 rounded-[8px] text-[9px] font-black uppercase tracking-widest transition-colors font-bricolage shadow-sm">Resoldre</button>
              </td>
            </tr>
          </tbody>
        </table>
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
      <div v-if="popupObert === 'prohibir'" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-[20px] max-w-md w-full p-8 shadow-2xl border border-white/80 transform transition-all font-comfortaa">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight font-bricolage">🚫 Prohibir Accés</h3>
            <button @click="tancaPopup" class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
          </div>
          
          <div class="mb-4">
            <p class="text-xs text-gray-500 font-bold uppercase mb-2">Usuari Reportat</p>
            <div class="bg-red-50 text-red-700 font-black text-sm p-3 rounded-[10px] border border-red-100 font-bricolage">
              {{ usuariSeleccionat?.nom || 'Usuari #' + usuariSeleccionat?.id }}
            </div>
          </div>

          <div class="space-y-5">
            <div>
              <label for="duradaBan" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 font-bricolage">Durada del Ban</label>
              <div class="relative">
                <select id="duradaBan" v-model="formulari.duradaProhibicio" class="w-full bg-gray-50 border border-gray-200/80 rounded-[10px] px-4 py-3 text-xs font-bold text-gray-700 outline-none appearance-none focus:border-red-400 transition-colors">
                  <option value="1_dia">1 Dia</option>
                  <option value="3_dies">3 Dies</option>
                  <option value="7_dies">7 Dies (1 Setmana)</option>
                  <option value="30_dies">30 Dies (1 Mes)</option>
                  <option value="permanent">Permanent</option>
                </select>
                <span class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">▼</span>
              </div>
            </div>

            <div>
              <label for="motiuBan" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 font-bricolage">Motiu de la Prohibició</label>
              <textarea id="motiuBan" v-model="formulari.motiuProhibicio" rows="3" placeholder="Introdueix el motiu detallat del banejament..." class="w-full bg-gray-50 border border-gray-200/80 rounded-[10px] p-4 text-xs font-bold text-gray-700 outline-none focus:border-red-400 transition-colors resize-none"></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-8">
            <button @click="tancaPopup" class="px-5 py-3 rounded-[10px] text-xs font-bold text-gray-500 hover:bg-gray-50 transition-colors">Cancelar</button>
            <button @click="confirmarProhibicio" class="bg-red-500 hover:bg-red-600 text-white border-2 border-red-600 px-6 py-3 rounded-[10px] text-xs font-black uppercase tracking-widest transition-colors shadow-lg shadow-red-200/30 font-bricolage">Confirmar Ban</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
