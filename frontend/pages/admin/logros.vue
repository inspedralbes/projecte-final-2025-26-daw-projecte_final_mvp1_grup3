<script setup>
/**
 * Gestió de Logros (Admin).
 * Pàgina independent amb grid i accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var nuxtApp = useNuxtApp();
var socketGlobal = nuxtApp.$socket;

// Logros via API
var respostaLogros = useAuthFetch('/api/admin/logros/1/50', {
  key: 'admin_logros_list'
});

var logros = computed(function () {
  if (respostaLogros.data.value && respostaLogros.data.value.success) {
    return respostaLogros.data.value.data.data;
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
  formulari.value = { nom: "", tipus: "Consistència", desc: "", xp: 100 };
  popupObert.value = 'crear';
}

function obreEditar(l) {
  logroSeleccionat.value = l;
  formulari.value = { nom: l.nom, tipus: l.tipus, desc: l.desc, xp: l.xp };
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
onMounted(function () {
  if (socketGlobal) {
    socketGlobal.on('admin_action_confirmed', function (carrega) {
      if (carrega.entity === 'logro' && carrega.success) {
        respostaLogros.refresh();
      }
    });
  }
});

function guardarLogro() {
  if (!socketGlobal) {
    return;
  }

  var accio = 'UPDATE';
  if (popupObert.value === 'crear') {
    accio = 'CREATE';
  }

  var carrega = {
    action: accio,
    entity: 'logro',
    data: {
      nom: formulari.value.nom,
      tipus: formulari.value.tipus,
      descripcio: formulari.value.descripcio,
      recompensa_xp: parseInt(formulari.value.recompensa_xp) || 100
    }
  };
  
  if (popupObert.value === 'editar') {
    carrega.data.id = logroSeleccionat.value.id;
  }
  
  socketGlobal.emit('admin_action', carrega);
  tancaPopup();
}

function confirmarEliminacio() {
  if (!socketGlobal || !logroSeleccionat.value) {
    return;
  }

  socketGlobal.emit('admin_action', {
    action: 'DELETE',
    entity: 'logro',
    data: { id: logroSeleccionat.value.id }
  });
  
  tancaPopup();
}

function obtenirTitolPopup() {
  if (popupObert.value === 'crear') {
    return 'Nou Logro';
  }
  if (popupObert.value === 'editar') {
    return 'Editar Logro';
  }
  return 'Eliminar Logro';
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Medalles i Logros</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Sistema de recompenses del sistema</p>
      </div>
      <button @click="obreCrear" class="bg-orange-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-orange-700 transition-all shadow-xl shadow-orange-100 flex items-center gap-3">
        <span class="text-lg leading-none">+</span>
        Crear Logro
      </button>
    </div>

    <!-- Grid de Logros -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div v-for="l in logros" :key="l.id" class="bg-white rounded-[3rem] p-8 shadow-xl border border-gray-50 flex items-center gap-8 group hover:-translate-y-1 transition-all">
        <div class="w-24 h-24 rounded-full bg-orange-100 flex items-center justify-center text-[8px] font-black text-orange-600 border-8 border-orange-50 shrink-0 shadow-inner tracking-widest uppercase">
          MEDALLA
        </div>
        <div class="flex-1">
          <div class="flex justify-between items-start">
            <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter">{{ l.nom }}</h3>
            <span class="bg-orange-50 text-orange-500 px-3 py-1 rounded-xl font-black text-[9px] uppercase border border-orange-100 tracking-widest">{{ l.tipus }}</span>
          </div>
          <p class="text-xs text-gray-400 font-bold mt-2 leading-relaxed">{{ l.descripcio }}</p>
          <div class="mt-6 flex justify-between items-center">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-blue-500"></span>
              <span class="text-[10px] font-black text-blue-600 uppercase">{{ l.recompensa_xp }} XP</span>
            </div>
            <div class="space-x-4">
              <button @click="obreEditar(l)" class="text-[9px] font-black text-gray-400 uppercase hover:text-blue-600">Editar</button>
              <button @click="obreEliminar(l)" class="text-[9px] font-black text-gray-400 uppercase hover:text-red-500">Eliminar</button>
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
        <div class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col border border-white/20">
          
          <div class="p-10 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">
                {{ obtenirTitolPopup() }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Configuració de medalles</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">X</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Nom de la Medalla</label>
                <input v-model="formulari.nom" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all placeholder:text-gray-300" placeholder="Ex: Super Consistent..." />
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Categoria</label>
                  <select v-model="formulari.tipus" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all appearance-none">
                    <option>Consistència</option>
                    <option>Nivell</option>
                    <option>Comunitat</option>
                    <option>Esdeveniment</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Recompensa (XP)</label>
                  <input v-model="formulari.xp" type="number" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all" />
                </div>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Descripció del Logro</label>
                <textarea v-model="formulari.desc" rows="3" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all placeholder:text-gray-300" placeholder="Condicions per guanyar la medalla..."></textarea>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-8 rounded-[2.5rem] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2">Eliminar Logro?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Aquesta medalla s'eliminarà de l'historial de tots els usuaris que la tinguin.</p>
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-xl shadow-red-100 hover:bg-red-700 transition-all">Esborrar</button>
            <button v-else @click="guardarLogro" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-orange-600 text-white shadow-xl shadow-orange-100 hover:bg-orange-700 transition-all">Guardar Medalla</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
