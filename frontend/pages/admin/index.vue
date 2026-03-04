<script setup>
/**
 * Loopy Admin Dashboard.
 * Ara utilitza el layout 'admin' per a la navegació lateral.
 */
definePageMeta({ layout: 'admin' });

import { ref, computed } from 'vue';

// 1. DADES REACTIVES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Estadístiques reals via API
var { data: statsData, refresh: refreshStats } = useAuthFetch('/api/admin/dashboard', {
  key: 'admin_stats'
});

var stats = computed(function() {
  if (statsData.value && statsData.value.success) {
    return statsData.value.data;
  }
  return { totalUsuaris: 0, totalHabits: 0, connectats: 0, prohibits: 0, logrosActius: 0 };
});

// Rankings reals via API
var { data: rankingsData } = useAuthFetch('/api/admin/rankings/mensual');

var rankings = computed(function() {
  if (rankingsData.value && rankingsData.value.success) {
    return rankingsData.value.data;
  }
  return [];
});

var popupActiu = ref(null);
var usuarisRealTime = ref([]);
var usuarisLlista = ref([]);
var carregantLlista = ref(false);

// Usuaris recents via API
var { data: usuarisData } = useAuthFetch('/api/admin/usuaris/tots/1/4/false/none');

var usuaris = computed(function() {
  if (usuarisData.value && usuarisData.value.success) {
    return usuarisData.value.data.data; // Paginació de Laravel
  }
  return [];
});

var dadesMock = {
  connectats: [
    { id: 1, nom: "Pepito", email: "pep@dev.com" },
    { id: 2, nom: "Jordi", email: "jor@test.ca" },
    { id: 3, nom: "Marta", email: "mar@web.io" }
  ],
  logs: [
    { id: 1, data: "2026-02-24 09:45", admin: "SuperAdmin", accio: "Editar Plantilla #3", abans: '{"titol":"Vell"}', despres: '{"titol":"Nou"}' },
    { id: 2, data: "2026-02-24 09:30", admin: "Admin01", accio: "Crear Logro 'Mestre'", abans: "null", despres: '{"nom":"Mestre"}' }
  ],
  plantillesRanking: [
    { nom: "Hivern Saludable", us: "1,2k" },
    { nom: "Productivitat DAW", us: "980" }
  ],
  habitsRanking: [
    { nom: "Beure 2L Aigua", us: "3,4k" },
    { nom: "Arribar d'hora", us: "2,1k" }
  ]
};

// 2. COMPUTADES (FUNCTION)
var titolPopup = computed(function() {
  if (popupActiu.value === 'connectats') return "Usuaris en Línia";
  if (popupActiu.value === 'logs') return "Auditoria del Sistema";
  if (popupActiu.value === 'rankings') return "Estadístiques de Popularitat";
  if (popupActiu.value === 'usuaris_totals') return "Comunitat d'Usuaris";
  return "Secció Administrativa";
});

// 3. LIFECYCLE
onMounted(function() {
  if ($socket) {
    $socket.emit('admin_join', {});
    
    $socket.on('admin:connected_users', function(llista) {
      usuarisRealTime.value = llista;
    });
  }
});

// 4. METHODS (FUNCTION)
function obrePopup(nom) {
  popupActiu.value = nom;
  if (nom === 'connectats' && $socket) {
    $socket.emit('admin:request_connected');
  }
  if (nom === 'usuaris_totals') {
    carregantLlista.value = true;
    $fetch('/api/admin/usuaris/tots/1/50/0/-', {
      baseURL: config.public.apiUrl,
      headers: useAuthStore().getAuthHeaders()
    }).then(function(res) {
      if (res.success) usuarisLlista.value = res.data.data;
      carregantLlista.value = false;
    }).catch(function() {
      carregantLlista.value = false;
    });
  }
}

function tancaPopup() {
  popupActiu.value = null;
}
</script>

<template>
  <div class="space-y-10">
    <!-- Capçalera Dashboard -->
    <div>
      <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">Benvingut al Dashboard</h2>
      <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1">Resum global de l'activitat</p>
    </div>

    <!-- Bento Grid Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
      <!-- Usuaris Connectats -->
      <div @click="obrePopup('connectats')" class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 hover:-translate-y-2 transition-all cursor-pointer group">
        <div class="flex justify-between items-start mb-10">
          <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-xs font-black text-blue-600 uppercase">USR</div>
          <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">En línia</span>
        </div>
        <h3 class="text-5xl font-black text-gray-900 tracking-tighter mb-2">{{ stats.connectats }}</h3>
        <p class="text-[10px] text-gray-400 font-bold uppercase mt-4 group-hover:text-green-600 transition-colors">Veure Llista</p>
      </div>

      <!-- Usuaris Totals -->
      <div @click="obrePopup('usuaris_totals')" class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 hover:-translate-y-2 transition-all cursor-pointer group">
        <div class="flex justify-between items-start mb-10">
          <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-xs font-black text-indigo-600 uppercase">ALL</div>
          <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Comunitat</span>
        </div>
        <h3 class="text-5xl font-black text-gray-900 tracking-tighter mb-2">{{ stats.totalUsuaris }}</h3>
        <p class="text-[10px] text-gray-400 font-bold uppercase mt-4 group-hover:text-indigo-600 transition-colors">Usuaris Registrats</p>
      </div>

      <!-- Logs del Sistema -->
      <div @click="obrePopup('logs')" class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 hover:-translate-y-2 transition-all cursor-pointer group">
        <div class="flex justify-between items-start mb-10">
          <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center text-xs font-black text-gray-600 uppercase">LOG</div>
          <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">Temps Real</span>
        </div>
        <div class="space-y-2 opacity-60 group-hover:opacity-100 transition-opacity">
          <div class="h-1 w-full bg-gray-200 rounded-full"></div>
          <div class="h-1 w-2/3 bg-gray-200 rounded-full"></div>
          <div class="h-1 w-4/5 bg-gray-200 rounded-full"></div>
        </div>
        <p class="text-[10px] text-gray-400 font-bold uppercase mt-4 group-hover:text-gray-600 transition-colors">Auditoria Completa</p>
      </div>

      <!-- Rankings Globals -->
      <div @click="obrePopup('rankings')" class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 hover:-translate-y-2 transition-all cursor-pointer group">
        <div class="flex justify-between items-start mb-10">
          <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-xs font-black text-orange-600 uppercase">TOP</div>
          <span class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Popularitat</span>
        </div>
        <div class="space-y-4">
          <div v-for="(r, i) in rankings.slice(0,2)" :key="i" class="flex items-center justify-between">
            <span class="text-xs font-black text-gray-700">{{ i+1 }}. {{ r.nom }}</span>
            <span class="text-[10px] font-black text-orange-500">{{ r.valor }}</span>
          </div>
        </div>
        <p class="text-[10px] text-gray-400 font-bold uppercase mt-4 group-hover:text-orange-600 transition-colors">Veure detalls complets</p>
      </div>

      <!-- Main Content Area -->
      <div class="lg:col-span-4 bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100">
        <div class="flex justify-between items-center mb-10">
          <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tighter border-l-8 border-blue-600 pl-6">Usuaris Recents</h2>
          <NuxtLink to="/admin/usuaris">
            <button class="bg-blue-600 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">Gestionar Tots</button>
          </NuxtLink>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="text-[10px] font-black text-gray-300 uppercase tracking-widest border-b border-gray-50">
                <th class="pb-6">Identitat</th>
                <th class="pb-6">Correu</th>
                <th class="pb-6 text-center">Nivell</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in usuaris.slice(0, 4)" :key="user.id" class="group hover:bg-gray-50 transition-all">
                <td class="py-5">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 flex items-center justify-center font-black text-xs">{{ user.nom.charAt(0) }}</div>
                    <span class="font-black text-gray-800 text-sm">{{ user.nom }}</span>
                  </div>
                </td>
                <td class="py-5 text-gray-400 font-bold text-xs tracking-tight">{{ user.email }}</td>
                <td class="py-5 text-center">
                  <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg font-black text-[9px] uppercase border border-blue-100">Lvl {{ user.nivell }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rankings Column -->
      <div @click="obrePopup('rankings')" class="lg:col-span-4 bg-gradient-to-br from-white to-gray-50 rounded-[2.5rem] p-8 shadow-xl border border-gray-100 cursor-pointer hover:shadow-2xl transition-all h-full">
        <h2 class="text-[10px] font-black text-orange-500 uppercase tracking-[0.2em] mb-6">🔝 Rankings Globals</h2>
        <div class="space-y-4">
          <div v-for="(r, i) in rankings" :key="i" class="flex items-center justify-between p-4 rounded-2xl bg-white shadow-sm border border-gray-50">
            <span class="text-xs font-black text-gray-700">{{ i+1 }}. {{ r.nom }}</span>
            <span class="text-[10px] font-black text-orange-500">{{ r.valor }}</span>
          </div>
        </div>
        <p class="text-[10px] text-gray-400 font-bold uppercase mt-10 text-center">Veure detalls complets</p>
      </div>

    </div>

    <!-- SISTEMA DE POPUPS (Dashboard ONLY) -->
    <Transition 
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="popupActiu" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-gray-900/60 backdrop-blur-md" @click.self="tancaPopup">
        <!-- ... contingut popup ... -->
        <div class="bg-white w-full max-w-4xl max-h-[85vh] rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col border border-white/20">
          
          <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">{{ titolPopup }}</h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Detalls del Dashboard</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-gray-50 transition-all">X</button>
          </div>

          <div class="p-10 overflow-y-auto flex-1">
            <!-- Popup Connectats (Real Time) -->
            <div v-if="popupActiu === 'connectats'" class="space-y-4">
              <div v-for="c in usuarisRealTime" :key="c.user_id" class="flex justify-between items-center p-4 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="flex items-center gap-4">
                  <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                  <span class="font-black text-gray-800 text-sm">{{ c.nom }}</span>
                  <span class="text-[10px] text-gray-400 font-bold uppercase">{{ c.email }}</span>
                </div>
                <span class="text-[10px] font-black text-gray-300 uppercase italic">Connectat {{ c.connected_at }}</span>
              </div>
              <div v-if="usuarisRealTime.length === 0" class="py-10 text-center opacity-30 text-xs font-black uppercase tracking-widest">No hi ha usuaris en línia ara mateix</div>
            </div>

            <!-- Popup Totals (Database) -->
            <div v-if="popupActiu === 'usuaris_totals'" class="space-y-4">
              <div v-if="carregantLlista" class="text-center py-10 animate-pulse text-xs font-black uppercase text-gray-400">Carregant llista completa...</div>
              <div v-for="u in usuarisLlista" :key="u.id" class="flex justify-between items-center p-4 rounded-2xl bg-gray-50 border border-gray-100 group hover:bg-white hover:shadow-md transition-all">
                <div class="flex items-center gap-4">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-[10px]">{{ u.nom.charAt(0) }}</div>
                  <div>
                    <div class="font-black text-gray-800 text-sm">{{ u.nom }}</div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase">{{ u.email }}</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-[9px] font-black text-indigo-500 bg-indigo-50 px-2 py-1 rounded-md uppercase">Lvl {{ u.nivell }}</span>
                  <span v-if="u.prohibit" class="text-[9px] font-black text-red-500 bg-red-50 px-2 py-1 rounded-md uppercase">Banejat</span>
                </div>
              </div>
              <div class="pt-6 text-center">
                <NuxtLink to="/admin/usuaris" @click="tancaPopup" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Gestionar tots els usuaris →</NuxtLink>
              </div>
            </div>

            <!-- Popup Logs -->
            <div v-if="popupActiu === 'logs'" class="space-y-3">
              <div v-for="l in dadesMock.logs" :key="l.id" class="p-5 rounded-3xl bg-gray-900 text-white border border-gray-800">
                <div class="flex justify-between mb-3 text-[9px] font-black uppercase text-gray-500 tracking-widest">
                  <span>{{ l.data }}</span>
                  <span class="text-blue-400">{{ l.admin }}</span>
                </div>
                <p class="text-sm font-bold mb-2">{{ l.accio }}</p>
              </div>
            </div>

            <!-- Popup Rankings -->
            <div v-if="popupActiu === 'rankings'" class="grid grid-cols-2 gap-8">
              <div class="space-y-4">
                <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-widest border-b pb-2">Plantilles Top</h4>
                <div v-for="(p, i) in dadesMock.plantillesRanking" :key="i" class="flex justify-between font-bold text-sm">
                  <span class="text-gray-400">{{ i+1 }}. {{ p.nom }}</span>
                  <span class="text-blue-600">{{ p.us }}</span>
                </div>
              </div>
              <div class="space-y-4">
                <h4 class="text-[10px] font-black text-orange-500 uppercase tracking-widest border-b pb-2">Hàbits Top</h4>
                <div v-for="(h, i) in dadesMock.habitsRanking" :key="i" class="flex justify-between font-bold text-sm">
                  <span class="text-gray-400">{{ i+1 }}. {{ h.nom }}</span>
                  <span class="text-orange-600">{{ h.us }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-8 border-t border-gray-100 bg-gray-50/50 text-right">
            <button @click="tancaPopup" class="bg-gray-900 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all">Tancar Detalls</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
