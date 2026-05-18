<script setup>
/**
 * Gestió de Logros (Admin).
 * Pàgina independent amb grid i accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Logros via API
var { data: logrosData, refresh: refreshLogros } = useAuthFetch('/api/admin/logros/1/50', {
  key: 'admin_logros_list'
});

var logros = computed(function() {
  if (logrosData.value && logrosData.value.success) {
    return logrosData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'crear', 'editar', 'eliminar'
var logroSeleccionat = ref(null);
var formulari = ref({
  nom: "",
  tipus: "Consistència",
  descripcio: "",
  recompensa_xp: 100
});

// 2. METHODS (FUNCTION)
function obreCrear() {
  formulari.value = { nom: "", tipus: "Consistència", descripcio: "", recompensa_xp: 100 };
  popupObert.value = 'crear';
}

function obreEditar(l) {
  logroSeleccionat.value = l;
  formulari.value = { 
    nom: l.nom, 
    tipus: l.tipus, 
    descripcio: l.descripcio, 
    recompensa_xp: l.recompensa_xp 
  };
  popupObert.value = 'editar';
}

function obreEliminar(l) {
  logroSeleccionat.value = l;
  popupObert.value = 'eliminar';
}

function tancaPopup() {
  popupObert.value = null;
  logroSeleccionat.value = null;
}

// Lifecycle i Sockets
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'logro' && payload.success) {
        refreshLogros();
      }
    });
  }
});

function guardarLogro() {
  if (!$socket) return;
  
  var payload = {
    action: popupObert.value === 'crear' ? 'CREATE' : 'UPDATE',
    entity: 'logro',
    data: {
      nom: formulari.value.nom,
      tipus: formulari.value.tipus,
      descripcio: formulari.value.descripcio,
      recompensa_xp: parseInt(formulari.value.recompensa_xp) || 100
    }
  };
  
  if (popupObert.value === 'editar') {
    payload.data.id = logroSeleccionat.value.id;
  }
  
  $socket.emit('admin_action', payload);
  tancaPopup();
}

function confirmarEliminacio() {
  if (!$socket || !logroSeleccionat.value) return;
  
  $socket.emit('admin_action', {
    action: 'DELETE',
    entity: 'logro',
    data: { id: logroSeleccionat.value.id }
  });
  
  tancaPopup();
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Medalles i Logros</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Sistema de recompenses del sistema</p>
      </div>
      <button @click="obreCrear" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white border-2 border-[#6fbc58] px-8 py-4 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-200/20 flex items-center gap-3 font-bricolage">
        <span class="text-lg leading-none">+</span>
        Crear Logro
      </button>
    </div>

    <!-- Grid de Logros Bento Glass -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div v-for="l in logros" :key="l.id" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 shadow-xl border border-white/50 flex items-center gap-8 group hover:-translate-y-1 transition-all">
        <div class="w-20 h-20 rounded-[10px] bg-orange-50 flex items-center justify-center text-[8px] font-black text-orange-600 border-4 border-orange-100 shrink-0 shadow-inner tracking-widest uppercase font-bricolage">
          MEDALLA
        </div>
        <div class="flex-1">
          <div class="flex justify-between items-start">
            <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter font-bricolage leading-none">{{ l.nom }}</h3>
            <span class="bg-orange-50 text-orange-500 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-orange-100 tracking-widest font-bricolage">{{ l.tipus }}</span>
          </div>
          <p class="text-xs text-gray-400 font-bold mt-3 leading-relaxed font-comfortaa">{{ l.descripcio }}</p>
          <div class="mt-4 flex justify-between items-center font-comfortaa">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-blue-500"></span>
              <span class="text-[10px] font-black text-blue-600 uppercase font-bricolage">{{ l.recompensa_xp }} XP</span>
            </div>
            <div class="space-x-4">
              <button @click="obreEditar(l)" class="text-[9px] font-black text-gray-400 uppercase hover:text-blue-600 font-bricolage">Editar</button>
              <button @click="obreEliminar(l)" class="text-[9px] font-black text-gray-400 uppercase hover:text-red-500 font-bricolage">Eliminar</button>
            </div>
          </div>
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
                {{ popupObert === 'crear' ? 'Nou Logro' : (popupObert === 'editar' ? 'Editar Logro' : 'Eliminar Logro') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 font-comfortaa">Configuració de medalles</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px] font-bricolage">Tancar</button>
          </div>

          <div class="p-8 space-y-6 font-comfortaa">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Nom de la Medalla</label>
                <input v-model="formulari.nom" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Ex: Super Consistent..." />
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Categoria</label>
                  <select v-model="formulari.tipus" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all appearance-none">
                    <option>Consistència</option>
                    <option>Nivell</option>
                    <option>Comunitat</option>
                    <option>Esdeveniment</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Recompensa (XP)</label>
                  <input v-model="formulari.recompensa_xp" type="number" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all" />
                </div>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Descripció del Logro</label>
                <textarea v-model="formulari.descripcio" rows="3" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Condicions per guanyar la medalla..."></textarea>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-6 rounded-[10px] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2 font-bricolage">Eliminar Logro?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Aquesta medalla s'eliminarà de l'historial de tots els usuaris que la tinguin.</p>
              </div>
            </template>
          </div>

          <div class="p-6 border-t border-gray-100 bg-white/50 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-6 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all font-bricolage">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-md hover:bg-red-700 transition-all font-bricolage">Esborrar</button>
            <button v-else @click="guardarLogro" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-[#79D45D] hover:bg-[#6fbc58] text-white border border-[#6fbc58] shadow-md transition-all font-bricolage">Guardar Medalla</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
