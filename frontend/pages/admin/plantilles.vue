<script setup>
/**
 * Gestió de Plantilles (Admin).
 * Pàgina independent amb grid i accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Plantilles via API
var { data: plantillesData, refresh: refreshPlantilles } = useAuthFetch('/api/admin/plantilles/1/50', {
  key: 'admin_templates_list'
});

var plantilles = computed(function() {
  if (plantillesData.value && plantillesData.value.success) {
    return plantillesData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'crear', 'editar', 'eliminar'
var plantillaSeleccionada = ref(null);
var formulari = ref({
  titol: "",
  categoria: "Activitat fisica",
  es_publica: true
});

// 2. METHODS (FUNCTION)
function obreCrear() {
  formulari.value = { titol: "", categoria: "Activitat fisica", es_publica: true };
  popupObert.value = 'crear';
}

function obreEditar(p) {
  plantillaSeleccionada.value = p;
  formulari.value = { 
    titol: p.titol, 
    categoria: p.categoria, 
    es_publica: p.es_publica 
  };
  popupObert.value = 'editar';
}

function obreEliminar(p) {
  plantillaSeleccionada.value = p;
  popupObert.value = 'eliminar';
}

function tancaPopup() {
  popupObert.value = null;
  plantillaSeleccionada.value = null;
}

// Lifecycle i Sockets
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'plantilla' && payload.success) {
        refreshPlantilles();
      }
    });
  }
});

function guardarPlantilla() {
  if (!$socket) return;
  
  var payload = {
    action: popupObert.value === 'crear' ? 'CREATE' : 'UPDATE',
    entity: 'plantilla',
    data: {
      titol: formulari.value.titol,
      categoria: formulari.value.categoria,
      es_publica: formulari.value.es_publica
    }
  };
  
  if (popupObert.value === 'editar') {
    payload.data.id = plantillaSeleccionada.value.id;
  }
  
  $socket.emit('admin_action', payload);
  tancaPopup();
}

function confirmarEliminacio() {
  if (!$socket || !plantillaSeleccionada.value) return;
  
  $socket.emit('admin_action', {
    action: 'DELETE',
    entity: 'plantilla',
    data: { id: plantillaSeleccionada.value.id }
  });
  
  tancaPopup();
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Plantilles Públiques</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Configuració del catàleg oficial</p>
      </div>
      <button @click="obreCrear" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white border-2 border-[#6fbc58] px-8 py-4 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-200/20 flex items-center gap-3 font-bricolage">
        <span class="text-lg leading-none">+</span>
        Nova Plantilla
      </button>
    </div>

    <!-- Grid de Plantilles Bento Glass -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="p in plantilles" :key="p.id" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 shadow-xl border border-white/50 transition-all group relative overflow-hidden flex flex-col justify-between">
        <div>
          <div class="flex justify-between items-start mb-6">
            <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-[10px] font-black text-[10px] uppercase border border-blue-100 font-bricolage">{{ p.categoria }}</span>
            <div v-if="p.es_publica" class="flex items-center gap-2">
              <span class="text-[9px] font-black text-green-500 uppercase tracking-widest font-bricolage">Pública</span>
              <div class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-md"></div>
            </div>
            <div v-else class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
          </div>
          <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-4 font-bricolage leading-tight">{{ p.titol }}</h3>
          <p class="text-xs text-gray-400 font-bold leading-relaxed font-comfortaa">{{ p.categoria }}</p>
        </div>

        <div class="mt-8 flex justify-between items-center border-t border-gray-100/50 pt-4 font-comfortaa">
          <button @click="obreEditar(p)" class="text-[10px] font-black text-gray-400 uppercase hover:text-blue-600 transition-colors font-bricolage">Editar</button>
          <button @click="obreEliminar(p)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors font-bricolage">Eliminar</button>
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
                {{ popupObert === 'crear' ? 'Nova Plantilla' : (popupObert === 'editar' ? 'Editar Plantilla' : 'Eliminar Plantilla') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 font-comfortaa">Configuració del catàleg</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px] font-bricolage">Tancar</button>
          </div>

          <div class="p-8 space-y-6 font-comfortaa">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Nom de la Plantilla</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Gym Rutina, Lògica DAW..." />
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Categoria</label>
                  <select v-model="formulari.categoria" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all appearance-none">
                    <option>Activitat fisica</option>
                    <option>alimentación</option>
                    <option>estudio</option>
                    <option>lectura</option>
                    <option>bienestar</option>
                    <option>mejora habitos</option>
                    <option>hogar</option>
                    <option>hobby</option>
                  </select>
                </div>
                <div class="space-y-2 flex flex-col justify-center items-center">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 font-bricolage">Pública?</label>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="formulari.es_publica" class="sr-only peer">
                    <div class="w-14 h-8 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#79D45D]"></div>
                  </label>
                </div>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-6 rounded-[10px] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2 font-bricolage">Eliminar Definitivament?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Aquesta plantilla ja no estarà disponible per als usuaris.</p>
              </div>
            </template>
          </div>

          <div class="p-6 border-t border-gray-100 bg-white/50 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-6 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all font-bricolage">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-md hover:bg-red-700 transition-all font-bricolage">Esborrar</button>
            <button v-else @click="guardarPlantilla" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-[#79D45D] hover:bg-[#6fbc58] text-white border border-[#6fbc58] shadow-md transition-all font-bricolage">Guardar Plantilla</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
