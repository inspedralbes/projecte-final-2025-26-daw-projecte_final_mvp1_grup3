<script setup>
/**
 * Gestió d'Hàbits (Admin).
 * Pàgina independent amb taula i accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';
import { authFetch } from '~/composables/useApi.js';
import { useAdminSwal } from '~/composables/useAdminSwal.js';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var { adminSuccess, adminError, adminWarning } = useAdminSwal();

// Hàbits via API
var { data: habitsData, refresh: refreshHabits } = useAuthFetch('/api/admin/habits/1/50', {
  key: 'admin_habits_list'
});

var habits = computed(function() {
  if (habitsData.value && habitsData.value.success) {
    return habitsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'crear', 'editar', 'eliminar'
var habitSeleccionat = ref(null);
var formulari = ref({
  titol: "",
  usuari_id: "",
  categoria_id: 1,
  dificultat: "media",
  frequencia_tipus: "diaria",
  dies_setmana: "1,2,3,4,5,6,7",
  objectiu_vegades: 1,
  moment_dia: "tot_dia",
  unitat: "vegades",
  icona: "🏃",
  color: "#65A30D"
});

// 2. METHODS (FUNCTION)
function obreCrear() {
  formulari.value = { 
    titol: "", 
    usuari_id: "", 
    categoria_id: 1, 
    dificultat: "media",
    frequencia_tipus: "diaria",
    dies_setmana: "1,2,3,4,5,6,7",
    objectiu_vegades: 1,
    moment_dia: "tot_dia",
    unitat: "vegades",
    icona: "🏃",
    color: "#65A30D"
  };
  popupObert.value = 'crear';
}

function obreEditar(h) {
  habitSeleccionat.value = h;
  formulari.value = { 
    titol: h.titol, 
    usuari_id: h.usuari_id, 
    categoria_id: h.categoria_id, 
    dificultat: h.dificultat || 'media',
    frequencia_tipus: h.frequencia_tipus || 'diaria',
    dies_setmana: h.dies_setmana || '1,2,3,4,5,6,7',
    objectiu_vegades: h.objectiu_vegades || 1,
    moment_dia: h.moment_dia || "tot_dia",
    unitat: h.unitat || "vegades",
    icona: h.icona || "🏃",
    color: h.color || "#65A30D"
  };
  popupObert.value = 'editar';
}

function obreEliminar(h) {
  habitSeleccionat.value = h;
  popupObert.value = 'eliminar';
}

function tancaPopup() {
  popupObert.value = null;
  habitSeleccionat.value = null;
}

// Lifecycle i Sockets
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'habit' && payload.success) {
        refreshHabits();
      }
    });
  }
});

async function guardarHabit() {
  var popup = popupObert.value;
  if (popup !== 'crear' && popup !== 'editar') {
    return;
  }

  var body = {
    titol: formulari.value.titol,
    categoria_id: parseInt(formulari.value.categoria_id, 10) || null,
    dificultat: formulari.value.dificultat,
    frequencia_tipus: formulari.value.frequencia_tipus,
    dies_setmana: formulari.value.dies_setmana,
    objectiu_vegades: parseInt(formulari.value.objectiu_vegades, 10) || 1,
    moment_dia: formulari.value.moment_dia,
    unitat: formulari.value.unitat,
    icona: formulari.value.icona,
    color: formulari.value.color
  };

  if (popup === 'crear') {
    var uid = parseInt(formulari.value.usuari_id, 10);
    if (!uid || uid < 1) {
      await adminWarning("Indica l'ID d'usuari propietari");
      return;
    }
    body.usuari_id = uid;
  }

  try {
    var url;
    var method;
    if (popup === 'crear') {
      url = '/api/admin/habits';
      method = 'POST';
    } else {
      url = '/api/admin/habits/' + habitSeleccionat.value.id;
      method = 'PUT';
    }

    var resposta = await authFetch(url, {
      method: method,
      body: JSON.stringify(body)
    });

    var json = await resposta.json().catch(function () { return {}; });
    if (!resposta.ok) {
      var msg = json.message || (json.errors && JSON.stringify(json.errors)) || 'Error en desar';
      throw new Error(typeof msg === 'string' ? msg : 'Error en desar');
    }

    await refreshHabits();
    tancaPopup();
    await adminSuccess(popup === 'crear' ? 'Hàbit creat' : 'Hàbit actualitzat');
  } catch (e) {
    await adminError('Error', e && e.message ? e.message : 'Error en desar');
  }
}

async function confirmarEliminacio() {
  if (!habitSeleccionat.value) {
    return;
  }

  try {
    var resposta = await authFetch('/api/admin/habits/' + habitSeleccionat.value.id, {
      method: 'DELETE'
    });
    var json = await resposta.json().catch(function () { return {}; });
    if (!resposta.ok) {
      throw new Error(json.message || 'Error en eliminar');
    }
    await refreshHabits();
    tancaPopup();
    await adminSuccess('Hàbit eliminat');
  } catch (e) {
    await adminError('Error', e && e.message ? e.message : 'Error en eliminar');
  }
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Gestió d'Hàbits</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Seguiment global d'activitats</p>
      </div>
      <button @click="obreCrear" class="bg-[#79D45D] hover:bg-[#6fbc58] text-white border-2 border-[#6fbc58] px-8 py-4 rounded-[10px] text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-200/20 flex items-center gap-3 font-bricolage">
        <span class="text-lg leading-none">+</span>
        Nou Hàbit
      </button>
    </div>

    <!-- Taula d'Hàbits en targeta Bento -->
    <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left font-comfortaa">
          <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 font-bricolage">
              <th class="pb-6">Activitat</th>
              <th class="pb-6">Propietari</th>
              <th class="pb-6">Categoria</th>
              <th class="pb-6 text-center">Dificultat</th>
              <th class="pb-6 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100/50">
            <tr v-for="habit in habits" :key="habit.id" class="group transition-all">
              <td class="py-5 font-black text-gray-800 text-base tracking-tight leading-none font-bricolage">{{ habit.titol }}</td>
              <td class="py-5 text-xs font-bold text-gray-500">{{ habit.usuari ? habit.usuari.nom : 'Usuari #' + habit.usuari_id }}</td>
              <td class="py-5">
                <span class="bg-green-50 text-green-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-green-100 italic font-bricolage">CAT #{{ habit.categoria_id }}</span>
              </td>
              <td class="py-5 text-center">
                <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-[10px] font-black text-[9px] uppercase border border-orange-100 font-bricolage">{{ habit.dificultat }}</span>
              </td>
              <td class="py-5 text-right space-x-3">
                <button @click="obreEditar(habit)" class="text-[10px] font-black text-gray-400 uppercase hover:text-green-600 transition-colors font-bricolage">Editar</button>
                <button @click="obreEliminar(habit)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors font-bricolage">Eliminar</button>
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
                {{ popupObert === 'crear' ? 'Nou Hàbit' : (popupObert === 'editar' ? 'Editar Hàbit' : 'Eliminar Hàbit') }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 font-comfortaa">Configuració d'activitat</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px] font-bricolage">Tancar</button>
          </div>

          <div class="p-8 space-y-6 font-comfortaa">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Títol de l'Hàbit</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="Beure aigua, Llegir..." />
              </div>
              <div class="grid grid-cols-3 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Icona (Emoji)</label>
                  <input v-model="formulari.icona" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="🏃" />
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Color (Hex)</label>
                  <input v-model="formulari.color" type="color" class="w-full h-[46px] bg-white/50 border border-gray-200 rounded-[10px] px-2 py-1 cursor-pointer transition-all" />
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Unitat</label>
                  <input v-model="formulari.unitat" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="vegades, ml, min..." />
                </div>
              </div>
              <div v-if="popupObert === 'crear'" class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">ID de l'Usuari</label>
                <input v-model="formulari.usuari_id" type="number" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="ID usuari" required />
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Categoria</label>
                  <select v-model="formulari.categoria_id" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all appearance-none">
                    <option :value="1">Salut / Esport</option>
                    <option :value="2">Alimentació</option>
                    <option :value="3">Estudi</option>
                    <option :value="4">Lectura</option>
                    <option :value="5">Benestar</option>
                    <option :value="6">Hàbits</option>
                    <option :value="7">Llar</option>
                    <option :value="8">Hobby</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Dificultat</label>
                  <select v-model="formulari.dificultat" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all appearance-none">
                    <option value="facil">Baixa (Fàcil)</option>
                    <option value="media">Mitjana (Media)</option>
                    <option value="dificil">Alta (Difícil)</option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Freqüència</label>
                  <select v-model="formulari.frequencia_tipus" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all appearance-none">
                    <option value="diaria">Diària</option>
                    <option value="semanal">Setmanal</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Objectiu (Vegades)</label>
                  <input v-model="formulari.objectiu_vegades" type="number" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="1" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Dies (separats per coma: 1,2,3...)</label>
                  <input v-model="formulari.dies_setmana" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all placeholder:text-gray-300" placeholder="1,2,3,4,5,6,7" />
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Momento del día</label>
                  <select v-model="formulari.moment_dia" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all appearance-none">
                    <option value="tot_dia">Todo el día</option>
                    <option value="mati">Mañana</option>
                    <option value="tarda">Tarde</option>
                    <option value="nit">Noche</option>
                  </select>
                </div>
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-6 rounded-[10px] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2 font-bricolage">Eliminar Definitivament?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Estàs a punt d'esborrar l'hàbit: <span class="text-red-700">{{ habitSeleccionat?.titol }}</span></p>
              </div>
            </template>
          </div>

          <div class="p-6 border-t border-gray-100 bg-white/50 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-6 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all font-bricolage">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-md hover:bg-red-700 transition-all font-bricolage">Esborrar</button>
            <button v-else @click="guardarHabit" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-[#79D45D] hover:bg-[#6fbc58] text-white border border-[#6fbc58] shadow-md transition-all font-bricolage">Guardar Hàbit</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
