<script setup>
/**
 * Gestió d'Usuaris (Admin).
 * Pàgina independent amb taula i accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Usuaris via API (Paginació desactivada per ara o simple)
var perPage = ref(50);
var { data: usuarisData, refresh: refreshUsuaris } = useAuthFetch(function() {
  return '/api/admin/usuaris/tots/1/' + perPage.value + '/false/none';
}, {
  key: 'admin_users_list'
});

var usuaris = computed(function() {
  if (usuarisData.value && usuarisData.value.success) {
    return usuarisData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'crear', 'editar', 'prohibir'
var usuariSeleccionat = ref(null);
var formulari = ref({
  nom: "",
  email: "",
  password: "", // Nova contrasenya per a crear
  motiuProhibicio: ""
});

// 2. METHODS (FUNCTION)
function obreCrear() {
  formulari.value = { nom: "", email: "", nivell: 1, motiuProhibicio: "" };
  popupObert.value = 'crear';
}

function obreEditar(user) {
  usuariSeleccionat.value = user;
  formulari.value = { nom: user.nom, email: user.email, nivell: user.nivell, motiuProhibicio: "" };
  popupObert.value = 'editar';
}

function obreProhibir(user) {
  usuariSeleccionat.value = user;
  formulari.value.motiuProhibicio = "";
  popupObert.value = 'prohibir';
}

function tancaPopup() {
  popupObert.value = null;
  usuariSeleccionat.value = null;
}

// Escoltarem confirmacions per refrescar la llista
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'usuari' && payload.success) {
        refreshUsuaris();
      }
    });
  }
});

function guardarUsuari() {
  if (!$socket) return;
  
  var payload = {
    action: popupObert.value === 'crear' ? 'CREATE' : 'UPDATE',
    entity: 'usuari',
    data: {
      nom: formulari.value.nom,
      email: formulari.value.email
    }
  };
  
  if (popupObert.value === 'crear') {
    payload.data.contrasenya = formulari.value.password;
  } else {
    payload.data.id = usuariSeleccionat.value.id;
  }
  
  $socket.emit('admin_action', payload);
  tancaPopup();
}

function confirmarProhibicio() {
  if (!$socket || !usuariSeleccionat.value) return;
  
  $socket.emit('admin_action', {
    action: 'UPDATE',
    entity: 'usuari',
    data: {
      id: usuariSeleccionat.value.id,
      prohibit: true,
      motiu_prohibicio: formulari.value.motiuProhibicio
    }
  });
  
  tancaPopup();
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Gestió d'Usuaris</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Administració completa de la comunitat</p>
      </div>
      <button @click="obreCrear" class="bg-blue-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center gap-3">
        <span class="text-lg leading-none">+</span>
        Crear Usuari
      </button>
    </div>

    <!-- Taula d'Usuaris -->
    <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-[10px] font-black text-gray-300 uppercase tracking-widest border-b border-gray-50">
              <th class="pb-8">Identitat</th>
              <th class="pb-8">Contacte</th>
              <th class="pb-8 text-center">Nivell</th>
              <th class="pb-8 text-center">Estat</th>
              <th class="pb-8 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="user in usuaris" :key="user.id" class="group hover:bg-gray-50/50 transition-all">
              <td class="py-6">
                <div class="flex items-center gap-5">
                  <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center font-black text-lg shadow-lg shadow-blue-100">{{ user.nom.charAt(0) }}</div>
                  <div>
                    <p class="font-black text-gray-800 text-base tracking-tight leading-none">{{ user.nom }}</p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Dona d'alta: {{ user.data }}</p>
                  </div>
                </div>
              </td>
              <td class="py-6">
                <p class="text-xs font-bold text-gray-500 tracking-tight">{{ user.email }}</p>
              </td>
              <td class="py-6 text-center">
                <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-xl font-black text-[10px] uppercase border border-blue-100">Lvl {{ user.nivell }}</span>
              </td>
              <td class="py-6 text-center">
                <div v-if="user.prohibit" class="flex flex-col items-center">
                  <span class="bg-red-500 text-white px-3 py-1 rounded-lg font-black text-[9px] uppercase shadow-sm">Prohibit</span>
                  <p class="text-[8px] text-red-400 font-bold uppercase mt-1 max-w-[80px] truncate" :title="user.motiu">{{ user.motiu }}</p>
                </div>
                <span v-else class="bg-green-100 text-green-600 px-3 py-1 rounded-lg font-black text-[9px] uppercase border border-green-200">Actiu</span>
              </td>
              <td class="py-6 text-right space-x-3">
                <button @click="obreEditar(user)" class="text-[10px] font-black text-gray-400 uppercase hover:text-blue-600 transition-colors">Editar</button>
                <button v-if="!user.prohibit" @click="obreProhibir(user)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors">🚫 Prohibir</button>
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
      <div v-if="popupObert" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-gray-900/60 backdrop-blur-md" @click.self="tancaPopup">
        <div class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col border border-white/20">
          
          <div class="p-10 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">
                {{ popupObert === 'crear' ? 'Nou Usuari' : (popupObert === 'editar' ? 'Editar Usuari' : 'Prohibir Usuari') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Gestió de comptes</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px]">Tancar</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Nom Complet</label>
                <input v-model="formulari.nom" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-gray-300" placeholder="Pau Garcia..." />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Correu Electrònic</label>
                <input v-model="formulari.email" type="email" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-gray-300" placeholder="exemple@loopy.com" />
              </div>
              <div v-if="popupObert === 'crear'" class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Contrasenya Temporal</label>
                <input v-model="formulari.password" type="password" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-gray-300" placeholder="********" />
              </div>
            </template>

            <template v-if="popupObert === 'prohibir'">
              <div class="bg-red-50 p-6 rounded-3xl border border-red-100 mb-6">
                <p class="text-xs font-bold text-red-600 leading-relaxed uppercase">Compte: <span class="text-red-800">{{ usuariSeleccionat?.nom }}</span></p>
                <p class="text-[10px] text-red-400 font-bold mt-1 uppercase">Aquesta acció impedirà que l'usuari entri al sistema.</p>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-red-400 uppercase tracking-widest ml-4">Motiu de la Prohibició</label>
                <textarea v-model="formulari.motiuProhibicio" rows="3" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all placeholder:text-gray-300" placeholder="Incompliment de les normes..."></textarea>
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Cancel·lar</button>
            
            <button v-if="popupObert === 'prohibir'" @click="confirmarProhibicio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-xl shadow-red-100 hover:bg-red-700 transition-all">Confirmar Ban</button>
            <button v-else @click="guardarUsuari" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-blue-600 text-white shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">Guardar Canvis</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
