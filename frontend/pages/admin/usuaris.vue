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

// Usuaris via API
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
  motiuProhibicio: "",
  duradaProhibicio: "permanent"
});

// 2. METHODS (FUNCTION)
function obreCrear() {
  formulari.value = { nom: "", email: "", password: "", motiuProhibicio: "", duradaProhibicio: "permanent" };
  popupObert.value = 'crear';
}

function obreEditar(user) {
  usuariSeleccionat.value = user;
  formulari.value = { nom: user.nom, email: user.email, password: "", motiuProhibicio: "", duradaProhibicio: "permanent" };
  popupObert.value = 'editar';
}

function obreProhibir(user) {
  usuariSeleccionat.value = user;
  formulari.value.motiuProhibicio = "";
  formulari.value.duradaProhibicio = "permanent";
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
  
  var duradesLabels = {
    "1_dia": "1 Dia",
    "3_dies": "3 Dies",
    "7_dies": "7 Dies",
    "30_dies": "30 Dies",
    "permanent": "Permanent"
  };
  var durada = duradesLabels[formulari.value.duradaProhibicio] || "Permanent";
  var motiuFinal = "[" + durada + "] " + (formulari.value.motiuProhibicio || "Sense motiu especificat");

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
}

function desprohibirUsuari(user) {
  if (!$socket) return;
  
  $socket.emit('admin_action', {
    action: 'UPDATE',
    entity: 'usuari',
    data: {
      id: user.id,
      prohibit: false,
      motiu_prohibicio: null
    }
  });
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Gestió d'Usuaris</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Administració completa de la comunitat</p>
      </div>
      <button @click="obreCrear" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white border-2 border-[#6fbc58] px-8 py-4 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-200/20 flex items-center gap-3 font-bricolage">
        <span class="text-lg leading-none">+</span>
        Crear Usuari
      </button>
    </div>

    <!-- Taula d'Usuaris en targeta Bento -->
    <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left font-comfortaa">
          <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 font-bricolage">
              <th class="pb-6">Identitat</th>
              <th class="pb-6">Contacte</th>
              <th class="pb-6 text-center">Nivell</th>
              <th class="pb-6 text-center">Estat</th>
              <th class="pb-6 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100/50">
            <tr v-for="user in usuaris" :key="user.id" class="group transition-all">
              <td class="py-5">
                <div class="flex items-center gap-5">
                  <div class="w-12 h-12 rounded-[10px] bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-md font-bricolage">{{ user.nom.charAt(0) }}</div>
                  <div>
                    <p class="font-black text-gray-800 text-base tracking-tight leading-none font-bricolage">{{ user.nom }}</p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Dona d'alta: {{ user.data }}</p>
                  </div>
                </div>
              </td>
              <td class="py-5">
                <p class="text-xs font-bold text-gray-500 tracking-tight">{{ user.email }}</p>
              </td>
              <td class="py-5 text-center">
                <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-[10px] font-black text-[10px] uppercase border border-blue-100 font-bricolage">Lvl {{ user.nivell }}</span>
              </td>
              <td class="py-5 text-center">
                <div v-if="user.prohibit" class="flex flex-col items-center">
                  <span class="bg-red-500 text-white px-3 py-1 rounded-[10px] font-black text-[9px] uppercase shadow-sm font-bricolage">Prohibit</span>
                  <p class="text-[8px] text-red-400 font-bold uppercase mt-1 max-w-[120px] truncate" :title="user.motiu">{{ user.motiu }}</p>
                </div>
                <span v-else class="bg-green-100 text-green-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-green-200 font-bricolage">Actiu</span>
              </td>
              <td class="py-5 text-right space-x-3">
                <button @click="obreEditar(user)" class="text-[10px] font-black text-gray-400 uppercase hover:text-blue-600 transition-colors font-bricolage">Editar</button>
                <button v-if="user.prohibit" @click="desprohibirUsuari(user)" class="text-[10px] font-black text-green-600 uppercase hover:text-green-800 transition-colors font-bricolage">🔓 Desbanear</button>
                <button v-else @click="obreProhibir(user)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors font-bricolage">🚫 Prohibir</button>
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
        <div class="bg-white/95 backdrop-blur-md w-full max-w-xl rounded-[10px] shadow-2xl relative overflow-hidden flex flex-col border border-white/50">
          
          <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-white/50">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter font-bricolage">
                {{ popupObert === 'crear' ? 'Nou Usuari' : (popupObert === 'editar' ? 'Editar Usuari' : 'Prohibir Usuari') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 font-comfortaa">Gestió de comptes</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px] font-bricolage">Tancar</button>
          </div>

          <div class="p-8 space-y-6 font-comfortaa">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Nom Complet</label>
                <input v-model="formulari.nom" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Pau Garcia..." />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Correu Electrònic</label>
                <input v-model="formulari.email" type="email" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="exemple@loopy.com" />
              </div>
              <div v-if="popupObert === 'crear'" class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Contrasenya Temporal</label>
                <input v-model="formulari.password" type="password" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="********" />
              </div>
            </template>

            <template v-if="popupObert === 'prohibir'">
              <div class="bg-red-50 p-6 rounded-[10px] border border-red-100 mb-6">
                <p class="text-xs font-bold text-red-600 leading-relaxed uppercase font-bricolage">Compte: <span class="text-red-800">{{ usuariSeleccionat?.nom }}</span></p>
                <p class="text-[10px] text-red-400 font-bold mt-1 uppercase">Aquesta acció impedirà que l'usuari entri al sistema temporalment o permanent.</p>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-red-400 uppercase tracking-widest ml-1 font-bricolage">Durada del Ban</label>
                  <select v-model="formulari.duradaProhibicio" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all appearance-none font-comfortaa">
                    <option value="1_dia">1 Dia</option>
                    <option value="3_dies">3 Dies</option>
                    <option value="7_dies">7 Dies (1 Setmana)</option>
                    <option value="30_dies">30 Dies (1 Mes)</option>
                    <option value="permanent">Permanent</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-red-400 uppercase tracking-widest ml-1 font-bricolage">Motiu del Ban</label>
                  <input v-model="formulari.motiuProhibicio" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all placeholder:text-gray-300 font-comfortaa" placeholder="Spam, comportament inadequat..." />
                </div>
              </div>
            </template>
          </div>

          <div class="p-6 border-t border-gray-100 bg-white/50 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-6 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all font-bricolage">Cancel·lar</button>
            
            <button v-if="popupObert === 'prohibir'" @click="confirmarProhibicio" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-md hover:bg-red-700 transition-all font-bricolage">Confirmar Ban</button>
            <button v-else @click="guardarUsuari" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-[#79D45D] hover:bg-[#6fbc58] text-white border border-[#6fbc58] shadow-md transition-all font-bricolage">Guardar Canvis</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
