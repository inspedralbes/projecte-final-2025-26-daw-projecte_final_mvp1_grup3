<script setup>
/**
 * Gestió de Missions (Admin).
 * Pàgina independent amb accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Missions via API
var { data: missionsData, refresh: refreshMissions } = useAuthFetch('/api/admin/missions/1/50', {
  key: 'admin_missions_list'
});

var missions = computed(function() {
  if (missionsData.value && missionsData.value.success) {
    return missionsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'crear', 'editar', 'eliminar'
var missioSeleccionada = ref(null);
var formulari = ref({
  titol: "",
  objectiu: "",
  recompensa: "",
  actiu: true
});

// 2. METHODS (FUNCTION)
function obreCrear() {
  formulari.value = { titol: "", objectiu: "", recompensa: "", actiu: true };
  popupObert.value = 'crear';
}

function obreEditar(m) {
  missioSeleccionada.value = m;
  formulari.value = { titol: m.titol, objectiu: m.objectiu, recompensa: m.recompensa, actiu: m.actiu };
  popupObert.value = 'editar';
}

function obreEliminar(m) {
  missioSeleccionada.value = m;
  popupObert.value = 'eliminar';
}

function tancaPopup() {
  popupObert.value = null;
  missioSeleccionada.value = null;
}

// Lifecycle i Sockets
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'missio' && payload.success) {
        refreshMissions();
      }
    });
  }
});

function guardarMissio() {
  if (!$socket) return;
  
  var payload = {
    action: popupObert.value === 'crear' ? 'CREATE' : 'UPDATE',
    entity: 'missio',
    data: {
      titol: formulari.value.titol,
      objectiu: formulari.value.objectiu,
      recompensa: formulari.value.recompensa,
      actiu: formulari.value.actiu
    }
  };
  
  if (popupObert.value === 'editar') {
    payload.data.id = missioSeleccionada.value.id;
  }
  
  $socket.emit('admin_action', payload);
  tancaPopup();
}

function confirmarEliminacio() {
  if (!$socket || !missioSeleccionada.value) return;
  
  $socket.emit('admin_action', {
    action: 'DELETE',
    entity: 'missio',
    data: { id: missioSeleccionada.value.id }
  });
  
  tancaPopup();
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Missions del Sistema</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Reptes temporals per a la comunitat</p>
      </div>
      <button @click="obreCrear" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white border-2 border-[#6fbc58] px-8 py-4 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-200/20 flex items-center gap-3 font-bricolage">
        <span class="text-lg leading-none">+</span>
        Nova Missió
      </button>
    </div>

    <!-- Grid de Missions Bento Glass -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div v-for="m in missions" :key="m.id" class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 flex flex-col justify-between hover:shadow-2xl transition-all group overflow-hidden relative">
        <div v-if="m.actiu" class="absolute top-0 right-0 bg-green-500 text-white px-6 py-2 rounded-bl-[10px] text-[9px] font-black uppercase tracking-widest font-bricolage">Activa</div>
        
        <div>
          <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-4 font-bricolage pr-16 leading-tight">{{ m.titol }}</h3>
          <div class="space-y-3 font-comfortaa">
            <div class="flex items-start gap-3">
              <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-2"></span>
              <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Objectiu: <span class="text-gray-600 normal-case">{{ m.objectiu }}</span></p>
            </div>
            <div class="flex items-start gap-3">
              <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mt-2"></span>
              <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Recompensa: <span class="text-orange-600 normal-case">{{ m.recompensa }}</span></p>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-end gap-6 border-t border-gray-100/50 pt-4 font-comfortaa">
          <button @click="obreEditar(m)" class="text-[10px] font-black text-gray-400 uppercase hover:text-indigo-600 transition-colors font-bricolage">Editar</button>
          <button @click="obreEliminar(m)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors font-bricolage">Eliminar</button>
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
        <div class="bg-white/95 backdrop-blur-md w-full max-w-xl rounded-[10px] shadow-2xl relative overflow-hidden flex flex-col border border-white/50">
          
          <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-white/50">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter font-bricolage">
                {{ popupObert === 'crear' ? 'Nova Missió' : (popupObert === 'editar' ? 'Editar Missió' : 'Eliminar Missió') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 font-comfortaa">Configuració de repte</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px] font-bricolage">Tancar</button>
          </div>

          <div class="p-8 space-y-6 font-comfortaa">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Títol del Repte</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Ex: Marató de Salut..." />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Objectiu Tècnic</label>
                <textarea v-model="formulari.objectiu" rows="2" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Completa X vegades..."></textarea>
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Recompensa</label>
                  <input v-model="formulari.recompensa" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all" placeholder="XP o Medalla" />
                </div>
                <div class="space-y-2 flex flex-col justify-center items-center">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 font-bricolage">Activa?</label>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="formulari.actiu" class="sr-only peer">
                    <div class="w-14 h-8 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#79D45D]"></div>
                  </label>
                </div>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-6 rounded-[10px] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2 font-bricolage">Eliminar Missió?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Aquesta acció farà desaparèixer el repte per a tots els usuaris.</p>
              </div>
            </template>
          </div>

          <div class="p-6 border-t border-gray-100 bg-white/50 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-6 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all font-bricolage">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-md hover:bg-red-700 transition-all font-bricolage">Esborrar repte</button>
            <button v-else @click="guardarMissio" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-[#79D45D] hover:bg-[#6fbc58] text-white border border-[#6fbc58] shadow-md transition-all font-bricolage">Guardar Missió</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
