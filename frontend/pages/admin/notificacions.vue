<script setup>
/**
 * Gestió de Notificacions (Admin).
 * Permet enviar avisos globals i veure l'historial enviat.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Historial via API
var { data: notificacionsData, refresh: refreshNotificacions } = useAuthFetch('/api/admin/notificacions/1/20/-', {
  key: 'admin_notifications_list'
});

var notificacions = computed(function() {
  if (notificacionsData.value && notificacionsData.value.success) {
    return notificacionsData.value.data.data;
  }
  return [];
});

var popupObert = ref(null); // 'enviar', 'detall'
var notiSeleccionada = ref(null);
var formulari = ref({
  titol: "",
  missatge: "",
  destinatari: "Tots"
});

// 2. METHODS (FUNCTION)
function obreEnviar() {
  formulari.value = { titol: "", missatge: "", destinatari: "Tots" };
  popupObert.value = 'enviar';
}

function obreDetall(n) {
  notiSeleccionada.value = n;
  popupObert.value = 'detall';
}

function tancaPopup() {
  popupObert.value = null;
  notiSeleccionada.value = null;
}

// Escoltarem confirmacions
onMounted(function() {
  if ($socket) {
    $socket.on('admin_action_confirmed', function(payload) {
      if (payload.entity === 'notificacio' && payload.success) {
        refreshNotificacions();
      }
    });
  }
});

function enviarNotificacio() {
  if (!$socket) return;
  
  $socket.emit('admin_action', {
    action: 'CREATE',
    entity: 'notificacio',
    data: {
      titol: formulari.value.titol,
      missatge: formulari.value.missatge,
      destinatari: formulari.value.destinatari
    }
  });
  
  tancaPopup();
}
</script>

<template>
  <div class="space-y-8 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Notificacions Push</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Comunicació directa amb els usuaris</p>
      </div>
      <button @click="obreEnviar" class="bg-blue-600 text-white px-8 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 flex items-center gap-3">
        ENVIAR AVÍS
      </button>
    </div>

    <!-- Historial de Notificacions -->
    <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100">
      <div class="space-y-4">
        <h3 class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-6">Historial d'enviaments</h3>
        
        <div v-for="n in notificacions" :key="n.id" @click="obreDetall(n)" class="group flex items-center justify-between p-6 rounded-[2rem] border border-gray-50 hover:border-blue-100 hover:bg-blue-50/10 cursor-pointer transition-all">
          <div class="flex items-center gap-6">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xs font-black group-hover:bg-blue-100 transition-colors uppercase">MSG</div>
            <div>
              <p class="font-black text-gray-800 text-sm uppercase tracking-tight">{{ n.titol }}</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ n.data }} • Destí: {{ n.destinatari }}</p>
            </div>
          </div>
          <span class="bg-green-100 text-green-600 px-3 py-1 rounded-lg font-black text-[9px] uppercase tracking-widest">{{ n.estat }}</span>
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
                {{ popupObert === 'enviar' ? 'Enviar Nou Avís' : 'Detall del Missatge' }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Sistemes de comunicació</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">X</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'enviar'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Títol de la Notificació</label>
                <input v-model="formulari.titol" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-gray-300" placeholder="Escriu un títol impactant..." />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Grup Destinatari</label>
                <select v-model="formulari.destinatari" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all appearance-none">
                  <option>Tots</option>
                  <option>Usuaris Actius</option>
                  <option>Usuaris Nous (7 dies)</option>
                  <option>Administradors</option>
                </select>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Missatge Complet</label>
                <textarea v-model="formulari.missatge" rows="4" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-gray-300" placeholder="Escriu el contingut de la notificació..."></textarea>
              </div>
            </template>

            <template v-if="popupObert === 'detall'">
              <div class="space-y-6">
                <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100">
                  <p class="text-[10px] font-black text-gray-300 uppercase mb-2">Missatge enviat</p>
                  <p class="text-sm font-bold text-gray-700 leading-relaxed">{{ notiSeleccionada?.missatge }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100 text-center">
                    <p class="text-[8px] font-black text-blue-400 uppercase">Impacte</p>
                    <p class="text-lg font-black text-blue-600 tracking-tighter">1.2k clics</p>
                  </div>
                  <div class="p-4 bg-green-50/50 rounded-2xl border border-green-100 text-center">
                    <p class="text-[8px] font-black text-green-400 uppercase">Entregades</p>
                    <p class="text-lg font-black text-green-600 tracking-tighter">100%</p>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Tancar</button>
            <button v-if="popupObert === 'enviar'" @click="enviarNotificacio" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-blue-600 text-white shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all flex items-center gap-2">
              ENVIAR ARA
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
