<script setup>
/**
 * Gestió d'Hàbits (Admin).
 * Pàgina independent amb taula i accions CRUD en popups.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var nuxtApp = useNuxtApp();
var socketGlobal = nuxtApp.$socket;

// Hàbits via API
var respostaHabits = useAuthFetch('/api/admin/habits/1/50', {
  key: 'admin_habits_list'
});

var habits = computed(function () {
  if (respostaHabits.data.value && respostaHabits.data.value.success) {
    return respostaHabits.data.value.data.data;
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
  objectiu_vegades: 1
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
    objectiu_vegades: 1
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
    objectiu_vegades: h.objectiu_vegades || 1
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
onMounted(function () {
  if (socketGlobal) {
    socketGlobal.on('admin_action_confirmed', function (carrega) {
      if (carrega.entity === 'habit' && carrega.success) {
        respostaHabits.refresh();
      }
    });
  }
});

function guardarHabit() {
  if (!socketGlobal) {
    return;
  }

  var accio = 'UPDATE';
  if (popupObert.value === 'crear') {
    accio = 'CREATE';
  }

  var carrega = {
    action: accio,
    entity: 'habit',
    data: {
      titol: formulari.value.titol,
      categoria_id: parseInt(formulari.value.categoria_id) || 1,
      dificultat: formulari.value.dificultat,
      frequencia_tipus: formulari.value.frequencia_tipus,
      dies_setmana: formulari.value.dies_setmana,
      objectiu_vegades: parseInt(formulari.value.objectiu_vegades) || 1
    }
  };
  
  if (popupObert.value === 'crear') {
    carrega.data.usuari_id = parseInt(formulari.value.usuari_id) || null;
  } else {
    carrega.data.id = habitSeleccionat.value.id;
  }
  
  socketGlobal.emit('admin_action', carrega);
  tancaPopup();
}

function confirmarEliminacio() {
  if (!socketGlobal || !habitSeleccionat.value) {
    return;
  }

  socketGlobal.emit('admin_action', {
    action: 'DELETE',
    entity: 'habit',
    data: { id: habitSeleccionat.value.id }
  });
  
  tancaPopup();
}

function obtenirTitolPopup() {
  if (popupObert.value === 'crear') {
    return 'Nou Hàbit';
  }
  if (popupObert.value === 'editar') {
    return 'Editar Hàbit';
  }
  return 'Eliminar Hàbit';
}

function obtenirNomHabitSeleccionat() {
  if (habitSeleccionat.value && habitSeleccionat.value.titol) {
    return habitSeleccionat.value.titol;
  }
  return '';
}

function obtenirNomUsuari(habit) {
  if (habit && habit.usuari && habit.usuari.nom) {
    return habit.usuari.nom;
  }
  if (habit && habit.usuari_id) {
    return 'Usuari #' + habit.usuari_id;
  }
  return 'Usuari';
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Gestió d'Hàbits</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Seguiment global d'activitats</p>
      </div>
      <button @click="obreCrear" class="bg-green-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-green-700 transition-all shadow-xl shadow-green-100 flex items-center gap-3">
        <span class="text-lg leading-none">+</span>
        Nou Hàbit
      </button>
    </div>

    <!-- Taula d'Hàbits -->
    <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-[10px] font-black text-gray-300 uppercase tracking-widest border-b border-gray-50">
              <th class="pb-8">Activitat</th>
              <th class="pb-8">Propietari</th>
              <th class="pb-8">Categoria</th>
              <th class="pb-8 text-center">Dificultat</th>
              <th class="pb-8 text-right">Accions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="habit in habits" :key="habit.id" class="group hover:bg-gray-50/50 transition-all">
              <td class="py-6 font-black text-gray-800 text-base tracking-tight leading-none">{{ habit.titol }}</td>
              <td class="py-6 text-xs font-bold text-gray-500">{{ obtenirNomUsuari(habit) }}</td>
              <td class="py-6">
                <span class="bg-green-50 text-green-600 px-3 py-1 rounded-lg font-black text-[9px] uppercase border border-green-100 italic">CAT #{{ habit.categoria_id }}</span>
              </td>
              <td class="py-6 text-center">
                <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-lg font-black text-[9px] uppercase border border-orange-100">{{ habit.dificultat }}</span>
              </td>
              <td class="py-6 text-right space-x-3">
                <button @click="obreEditar(habit)" class="text-[10px] font-black text-gray-400 uppercase hover:text-green-600 transition-colors">Editar</button>
                <button @click="obreEliminar(habit)" class="text-[10px] font-black text-gray-400 uppercase hover:text-red-500 transition-colors">Eliminar</button>
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
                {{ obtenirTitolPopup() }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Configuració d'activitat</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px]">Tancar</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'crear' || popupObert === 'editar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Títol de l'Hàbit</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all placeholder:text-gray-300" placeholder="Beure aigua, Llegir..." />
              </div>
              <div v-if="popupObert === 'crear'" class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">ID de l'Usuari</label>
                <input v-model="formulari.usuari_id" type="number" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all placeholder:text-gray-300" placeholder="ID usuari" required />
              </div>
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Categoria</label>
                  <select v-model="formulari.categoria_id" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all appearance-none">
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
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Dificultat</label>
                  <select v-model="formulari.dificultat" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all appearance-none">
                    <option value="facil">Baixa (Fàcil)</option>
                    <option value="media">Mitjana (Media)</option>
                    <option value="dificil">Alta (Difícil)</option>
                  </select>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Freqüència</label>
                  <select v-model="formulari.frequencia_tipus" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all appearance-none">
                    <option value="diaria">Diària</option>
                    <option value="semanal">Setmanal</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Objectiu (Vegades)</label>
                  <input v-model="formulari.objectiu_vegades" type="number" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all placeholder:text-gray-300" placeholder="1" />
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Dies (separats per coma: 1,2,3...)</label>
                <input v-model="formulari.dies_setmana" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-green-100 transition-all placeholder:text-gray-300" placeholder="1,2,3,4,5,6,7" />
              </div>
            </template>

            <template v-if="popupObert === 'eliminar'">
              <div class="bg-red-50 p-8 rounded-[2.5rem] border border-red-100 text-center">
                <p class="text-base font-black text-red-600 uppercase tracking-tighter mb-2">Eliminar Definitivament?</p>
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest">Estàs a punt d'esborrar l'hàbit: <span class="text-red-700">{{ obtenirNomHabitSeleccionat() }}</span></p>
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Cancel·lar</button>
            <button v-if="popupObert === 'eliminar'" @click="confirmarEliminacio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-red-600 text-white shadow-xl shadow-red-100 hover:bg-red-700 transition-all">Esborrar</button>
            <button v-else @click="guardarHabit" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-green-600 text-white shadow-xl shadow-green-100 hover:bg-green-700 transition-all">Guardar Hàbit</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
