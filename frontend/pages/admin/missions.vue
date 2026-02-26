<script setup>
/**
 * Gestió de Missions (Admin).
 * Pàgina independent amb accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var nuxtApp = useNuxtApp();
var socketGlobal = nuxtApp.$socket;

// Missions via API
var respostaMissions = useAuthFetch('/api/admin/missions/1/50', {
  key: 'admin_missions_list'
});

var missions = computed(function () {
  if (respostaMissions.data.value && respostaMissions.data.value.success) {
    return respostaMissions.data.value.data.data;
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
onMounted(function () {
  if (socketGlobal) {
    socketGlobal.on('admin_action_confirmed', function (carrega) {
      if (carrega.entity === 'missio' && carrega.success) {
        respostaMissions.refresh();
      }
    });
  }
});

function guardarMissio() {
  if (!socketGlobal) {
    return;
  }

  var accio = 'UPDATE';
  if (popupObert.value === 'crear') {
    accio = 'CREATE';
  }

  var carrega = {
    action: accio,
    entity: 'missio',
    data: {
      titol: formulari.value.titol,
      objectiu: formulari.value.objectiu,
      recompensa: formulari.value.recompensa,
      actiu: formulari.value.actiu
    }
  };
  
  if (popupObert.value === 'editar') {
    carrega.data.id = missioSeleccionada.value.id;
  }
  
  socketGlobal.emit('admin_action', carrega);
  tancaPopup();
}

function confirmarEliminacio() {
  if (!socketGlobal || !missioSeleccionada.value) {
    return;
  }

  socketGlobal.emit('admin_action', {
    action: 'DELETE',
    entity: 'missio',
    data: { id: missioSeleccionada.value.id }
  });
  
  tancaPopup();
}

function obtenirTitolPopup() {
  if (popupObert.value === 'crear') {
    return 'Nova Missió';
  }
  if (popupObert.value === 'editar') {
    return 'Editar Missió';
  }
  return 'Eliminar Missió';
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Missions del Sistema</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Reptes temporals per a la comunitat</p>
      </div>
      <button @click="obreCrear" class="bg-indigo-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-3">
        <span class="text-lg leading-none">+</span>
        Nova Missió
      </button>
    </div>

    <!-- Grid de Missions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div v-for="m in missions" :key="m.id" class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 flex flex-col justify-between hover:shadow-indigo-50 transition-all group overflow-hidden relative">
        <div v-if="m.actiu" class="absolute top-0 right-0 bg-green-500 text-white px-6 py-2 rounded-bl-3xl text-[9px] font-black uppercase tracking-widest">Activa</div>
        
        <div>
          <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-4">{{ m.titol }}</h3>
          <div class="space-y-4">
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

        <div class="mt-10 flex justify-end gap-6 border-t border-gray-50 pt-8">
          <button @click="obreEditar(m)" class="text-[10px] font-black text-gray-400 uppercase hover:text-indigo-600 transition-colors">Editar</button>
          <button @click="obreEliminar(m)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors">Eliminar</button>
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
                {{ obtenirTitolPopup() }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Configuració de repte</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px]">Tancar</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Títol del Repte</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-gray-300" placeholder="Ex: Marató de Salut..." />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Objectiu Tècnic</label>
                <textarea v-model="formulari.objectiu" rows="2" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-gray-300" placeholder="Completa X vegades..."></textarea>
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Recompensa</label>
                  <input v-model="formulari.recompensa" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all" placeholder="XP o Medalla" />
                </div>
                <div class="space-y-2 flex flex-col justify-center items-center">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Activa?</label>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="formulari.actiu" class="sr-only peer">
                    <div class="w-14 h-8 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                  </label>
                </div>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-8 rounded-[2.5rem] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2">Eliminar Missió?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Aquesta acció farà desaparèixer el repte per a tots els usuaris.</p>
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-xl shadow-red-100 hover:bg-red-700 transition-all">Esborrar repte</button>
            <button v-else @click="guardarMissio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-indigo-600 text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Guardar Missió</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
