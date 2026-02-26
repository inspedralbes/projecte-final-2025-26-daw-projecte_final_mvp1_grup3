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
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Plantilles Públiques</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Configuració del catàleg oficial</p>
      </div>
      <button @click="obreCrear" class="bg-blue-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center gap-3">
        <span class="text-lg leading-none">+</span>
        Nova Plantilla
      </button>
    </div>

    <!-- Grid de Plantilles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="p in plantilles" :key="p.id" class="bg-white rounded-[3rem] p-8 shadow-xl border border-gray-100 hover:shadow-2xl transition-all group relative overflow-hidden flex flex-col justify-between">
        <div>
          <div class="flex justify-between items-start mb-6">
            <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-xl font-black text-[10px] uppercase border border-blue-100">{{ p.categoria }}</span>
            <div v-if="p.es_publica" class="flex items-center gap-2">
              <span class="text-[9px] font-black text-green-500 uppercase tracking-widest">Pública</span>
              <div class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-lg shadow-green-200"></div>
            </div>
            <div v-else class="w-2.5 h-2.5 rounded-full bg-gray-200"></div>
          </div>
          <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-4">{{ p.titol }}</h3>
          <p class="text-xs text-gray-400 font-bold leading-relaxed">{{ p.categoria }}</p>
        </div>

        <div class="mt-10 flex justify-between items-center border-t border-gray-50 pt-6">
          <button @click="obreEditar(p)" class="text-[10px] font-black text-gray-400 uppercase hover:text-blue-600 transition-colors">Editar</button>
          <button @click="obreEliminar(p)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors">Eliminar</button>
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
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">
                {{ popupObert === 'crear' ? 'Nova Plantilla' : (popupObert === 'editar' ? 'Editar Plantilla' : 'Eliminar Plantilla') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Configuració del catàleg</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px]">Tancar</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Nom de la Plantilla</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-gray-300" placeholder="Gym Rutina, Lògica DAW..." />
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Categoria</label>
                  <select v-model="formulari.categoria" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all appearance-none">
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
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pública?</label>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="formulari.es_publica" class="sr-only peer">
                    <div class="w-14 h-8 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-8 rounded-[2.5rem] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2">Eliminar Definitivament?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Aquesta plantilla ja no estarà disponible per als usuaris.</p>
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-xl shadow-red-100 hover:bg-red-700 transition-all">Esborrar</button>
            <button v-else @click="guardarPlantilla" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-blue-600 text-white shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">Guardar Plantilla</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
