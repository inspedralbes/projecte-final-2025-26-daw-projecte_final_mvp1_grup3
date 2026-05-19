<!--
  Component o pagina Nuxt: index.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<script setup>
/**
 * Loopy Admin Dashboard.
 * Ara utilitza el layout 'admin' per a la navegació lateral.
 */
definePageMeta({ layout: 'admin' });

import { ref, computed } from 'vue';
import { authFetch, getBaseUrl } from '~/composables/useApi.js';

// 1. DADES REACTIVES (VAR)
var { $socket } = useNuxtApp();
var config = useRuntimeConfig();

// Estadístiques reals via API
var { data: statsData, refresh: refreshStats } = useAuthFetch('/api/admin/dashboard', {
  key: 'admin_stats',
  server: false
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
    authFetch(getBaseUrl() + '/api/admin/usuaris/tots/1/50/0/-').then(function(resposta) {
      return resposta.json();
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
  <div class="space-y-8 pb-12">
    <!-- Capçalera Dashboard (Alta visibilitat sobre fons verd) -->
    <div class="admin-dashboard-header">
      <h2 class="text-3xl font-black text-[#faf9f9] uppercase tracking-tighter font-bricolage drop-shadow-sm">Benvingut al Dashboard</h2>
      <p class="text-sm font-black text-white/80 uppercase tracking-widest mt-1 font-bricolage">Resum global de l'activitat</p>
    </div>

    <!-- Bento Grid Dashboard (Cohesive rounded-[10px] Glassmorphism) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Usuaris Connectats -->
      <div @click="obrePopup('connectats')" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 shadow-xl border border-white/50 cursor-pointer flex flex-col justify-between min-h-[190px]">
        <div>
          <div class="flex justify-between items-center mb-6">
            <div class="w-12 h-12 bg-blue-50 border border-blue-100 rounded-[10px] flex items-center justify-center text-xs font-black text-blue-600 uppercase font-bricolage">USR</div>
            <span class="text-[10px] font-black text-green-600 bg-green-50 px-2 py-0.5 border border-green-100 rounded-[10px] uppercase tracking-widest font-bricolage">En línia</span>
          </div>
          <h3 class="text-4xl font-black text-[#2b2d42] tracking-tighter mb-1 font-bricolage">{{ stats.connectats }}</h3>
        </div>
        <p class="text-[10px] text-gray-400 font-extrabold uppercase font-bricolage">Veure Llista →</p>
      </div>

      <!-- Usuaris Totals -->
      <div @click="obrePopup('usuaris_totals')" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 shadow-xl border border-white/50 cursor-pointer flex flex-col justify-between min-h-[190px]">
        <div>
          <div class="flex justify-between items-center mb-6">
            <div class="w-12 h-12 bg-indigo-50 border border-indigo-100 rounded-[10px] flex items-center justify-center text-xs font-black text-indigo-600 uppercase font-bricolage">ALL</div>
            <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 border border-indigo-100 rounded-[10px] uppercase tracking-widest font-bricolage">Comunitat</span>
          </div>
          <h3 class="text-4xl font-black text-[#2b2d42] tracking-tighter mb-1 font-bricolage">{{ stats.totalUsuaris }}</h3>
        </div>
        <p class="text-[10px] text-gray-400 font-extrabold uppercase font-bricolage">Usuaris Registrats →</p>
      </div>

      <!-- Logs del Sistema -->
      <div @click="obrePopup('logs')" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 shadow-xl border border-white/50 cursor-pointer flex flex-col justify-between min-h-[190px]">
        <div>
          <div class="flex justify-between items-center mb-6">
            <div class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-[10px] flex items-center justify-center text-xs font-black text-gray-600 uppercase font-bricolage">LOG</div>
            <span class="text-[10px] font-black text-gray-500 bg-gray-50 px-2 py-0.5 border border-gray-100 rounded-[10px] uppercase tracking-widest font-bricolage">Temps Real</span>
          </div>
          <div class="space-y-2 opacity-80">
            <div class="h-1.5 w-full bg-gray-200 rounded-full"></div>
            <div class="h-1.5 w-2/3 bg-gray-200 rounded-full"></div>
            <div class="h-1.5 w-4/5 bg-gray-200 rounded-full"></div>
          </div>
        </div>
        <p class="text-[10px] text-gray-400 font-extrabold uppercase font-bricolage">Auditoria Completa →</p>
      </div>

      <!-- Rankings Globals -->
      <div @click="obrePopup('rankings')" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 shadow-xl border border-white/50 cursor-pointer flex flex-col justify-between min-h-[190px]">
        <div>
          <div class="flex justify-between items-center mb-4">
            <div class="w-12 h-12 bg-orange-50 border border-orange-100 rounded-[10px] flex items-center justify-center text-xs font-black text-orange-600 uppercase font-bricolage">TOP</div>
            <span class="text-[10px] font-black text-orange-600 bg-orange-50 px-2 py-0.5 border border-orange-100 rounded-[10px] uppercase tracking-widest font-bricolage">Popularitat</span>
          </div>
          <div class="space-y-1">
            <div v-for="(r, i) in rankings.slice(0,2)" :key="i" class="flex items-center justify-between font-comfortaa">
              <span class="text-[11px] font-bold text-gray-700 truncate max-w-[110px]">{{ i+1 }}. {{ r.nom }}</span>
              <span class="text-[10px] font-extrabold text-orange-600">{{ r.valor }}</span>
            </div>
          </div>
        </div>
        <p class="text-[10px] text-gray-400 font-extrabold uppercase font-bricolage">Veure detalls →</p>
      </div>
    </div>

    <!-- Main Content Area: Recent Users -->
    <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 sm:p-8 shadow-xl border border-white/50">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter border-l-4 border-[#3b82f6] pl-4 font-bricolage">Usuaris Recents</h2>
        <NuxtLink to="/admin/usuaris">
          <button class="bg-[#79D45D] hover:bg-[#6fbc58] text-white border-2 border-[#6fbc58] px-4 py-2 rounded-[10px] text-[10px] font-black uppercase tracking-widest transition-all shadow-md font-bricolage">Gestionar Tots</button>
        </NuxtLink>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left font-comfortaa">
          <thead>
            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 font-bricolage">
              <th class="pb-4">Identitat</th>
              <th class="pb-4">Correu</th>
              <th class="pb-4 text-center">Nivell</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in usuaris.slice(0, 4)" :key="user.id" class="group transition-all">
              <td class="py-4">
                <div class="flex items-center gap-4">
                  <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 flex items-center justify-center font-black text-sm font-bricolage">{{ user.nom.charAt(0) }}</div>
                  <span class="font-extrabold text-[#2b2d42] text-sm font-bricolage">{{ user.nom }}</span>
                </div>
              </td>
              <td class="py-4 text-gray-500 font-bold text-xs tracking-tight">{{ user.email }}</td>
              <td class="py-4 text-center">
                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-[10px] border border-blue-100 font-black text-[9px] uppercase font-bricolage">Lvl {{ user.nivell }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Rankings Section -->
    <div @click="obrePopup('rankings')" class="bg-white/95 backdrop-blur-md rounded-[10px] p-6 sm:p-8 shadow-xl border border-white/50 cursor-pointer">
      <h2 class="text-[10px] font-black text-orange-600 bg-orange-50/50 border border-orange-100/50 rounded-[10px] px-3 py-1.5 inline-block uppercase tracking-wider mb-6 font-bricolage">🔝 Rankings Globals</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="(r, i) in rankings" :key="i" class="flex items-center justify-between p-4 rounded-[10px] bg-gray-50/50 border border-gray-100/60 shadow-sm font-comfortaa">
          <span class="text-xs font-black text-gray-700 font-bricolage">{{ i+1 }}. {{ r.nom }}</span>
          <span class="text-[10px] font-black text-orange-600 font-bricolage">{{ r.valor }}</span>
        </div>
      </div>
      <p class="text-[10px] text-gray-400 font-extrabold uppercase mt-6 text-center font-bricolage">Veure detalls complets →</p>
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
        <div class="bg-white/95 backdrop-blur-md w-full max-w-2xl max-h-[80vh] rounded-[10px] shadow-2xl relative overflow-hidden flex flex-col border border-white/50">
          
          <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
              <h3 class="text-xl font-black text-[#2b2d42] uppercase tracking-tighter font-bricolage">{{ titolPopup }}</h3>
              <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-bricolage">Detalls del Dashboard</p>
            </div>
            <button @click="tancaPopup" class="w-8 h-8 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-500 hover:bg-gray-50 transition-all font-bricolage">X</button>
          </div>

          <div class="p-8 overflow-y-auto flex-1 space-y-4">
            <!-- Popup Connectats (Real Time) -->
            <div v-if="popupActiu === 'connectats'" class="space-y-3">
              <div v-for="c in usuarisRealTime" :key="c.user_id" class="flex justify-between items-center p-4 rounded-[10px] bg-gray-50 border border-gray-100 font-comfortaa">
                <div class="flex items-center gap-3">
                  <div class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></div>
                  <span class="font-extrabold text-gray-800 text-sm font-bricolage">{{ c.nom }}</span>
                  <span class="text-[10px] text-gray-400 font-bold uppercase">{{ c.email }}</span>
                </div>
                <span class="text-[10px] font-black text-gray-400 uppercase italic font-bricolage">Connectat {{ c.connected_at }}</span>
              </div>
              <div v-if="usuarisRealTime.length === 0" class="py-10 text-center opacity-35 text-xs font-black uppercase tracking-widest font-bricolage">No hi ha usuaris en línia ara mateix</div>
            </div>

            <!-- Popup Totals (Database) -->
            <div v-if="popupActiu === 'usuaris_totals'" class="space-y-3">
              <div v-if="carregantLlista" class="text-center py-10 animate-pulse text-xs font-black uppercase text-gray-400 font-bricolage">Carregant llista completa...</div>
              <div v-for="u in usuarisLlista" :key="u.id" class="flex justify-between items-center p-4 rounded-[10px] bg-gray-50 border border-gray-100 group transition-all font-comfortaa">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-[10px] bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs font-bricolage">{{ u.nom.charAt(0) }}</div>
                  <div>
                    <div class="font-extrabold text-gray-800 text-sm font-bricolage">{{ u.nom }}</div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase">{{ u.email }}</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-[10px] border border-indigo-100 uppercase font-bricolage">Lvl {{ u.nivell }}</span>
                  <span v-if="u.prohibit" class="text-[9px] font-black text-red-600 bg-red-50 px-2 py-1 rounded-[10px] border border-red-100 uppercase font-bricolage">Banejat</span>
                </div>
              </div>
              <div class="pt-6 text-center">
                <NuxtLink to="/admin/usuaris" @click="tancaPopup" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline font-bricolage">Gestionar tots els usuaris →</NuxtLink>
              </div>
            </div>

            <!-- Popup Logs -->
            <div v-if="popupActiu === 'logs'" class="space-y-3">
              <div v-for="l in dadesMock.logs" :key="l.id" class="p-5 rounded-[10px] bg-gray-900 text-white border border-gray-800 font-comfortaa">
                <div class="flex justify-between mb-3 text-[9px] font-black uppercase text-gray-500 tracking-widest font-bricolage">
                  <span>{{ l.data }}</span>
                  <span class="text-blue-400">{{ l.admin }}</span>
                </div>
                <p class="text-sm font-extrabold mb-2 font-bricolage">{{ l.accio }}</p>
              </div>
            </div>

            <!-- Popup Rankings -->
            <div v-if="popupActiu === 'rankings'" class="grid grid-cols-1 sm:grid-cols-2 gap-8 pt-2">
              <div class="space-y-3">
                <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-widest border-b pb-2 font-bricolage">Plantilles Top</h4>
                <div v-for="(p, i) in dadesMock.plantillesRanking" :key="i" class="flex justify-between font-bold text-sm font-comfortaa">
                  <span class="text-gray-500">{{ i+1 }}. {{ p.nom }}</span>
                  <span class="text-blue-600 font-extrabold">{{ p.us }}</span>
                </div>
              </div>
              <div class="space-y-3">
                <h4 class="text-[10px] font-black text-orange-500 uppercase tracking-widest border-b pb-2 font-bricolage">Hàbits Top</h4>
                <div v-for="(h, i) in dadesMock.habitsRanking" :key="i" class="flex justify-between font-bold text-sm font-comfortaa">
                  <span class="text-gray-500">{{ i+1 }}. {{ h.nom }}</span>
                  <span class="text-orange-600 font-extrabold">{{ h.us }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 text-right">
            <button @click="tancaPopup" class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-[10px] text-[10px] font-black uppercase tracking-widest transition-all font-bricolage">Tancar Detalls</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.font-bricolage {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
}
.font-comfortaa {
  font-family: "Comfortaa", system-ui, sans-serif;
}
</style>
