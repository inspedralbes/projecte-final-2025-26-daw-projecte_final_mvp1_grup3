<template>
  <div class="relative w-full min-h-screen pb-12 overflow-y-auto">
    <!-- Navbar / Header Base -->
    <div class="w-full p-6 flex justify-between items-center z-20">
      <div class="flex items-center gap-4">
        <NuxtLink to="/home" class="bg-white/90 backdrop-blur-sm text-green-700 w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm hover:shadow-md hover:bg-white transition-all hover:-translate-x-1">
          ←
        </NuxtLink>
        <h1 class="text-3xl font-extrabold text-white drop-shadow-md">{{ $t('perfil.title') }}</h1>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 pb-20">
      
      <!-- Columna Esquerra: Dades Perfil i Logros -->
      <div class="col-span-12 lg:col-span-5 space-y-6">
        
        <!-- DADES PERFIL -->
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">👤</div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('perfil.data_title') }}</h2>
          </div>
          
          <div v-if="loading" class="animate-pulse space-y-4">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          </div>
          
          <div v-else-if="user" class="space-y-6">
            <div class="flex items-center gap-6">
              <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-3xl font-black shadow-lg">
                {{ user.nom ? user.nom.charAt(0) : 'U' }}
              </div>
              <div class="min-w-0">
                <h3 class="text-2xl font-black text-gray-800 truncate tracking-tight">{{ user.nom }}</h3>
                <p class="text-sm font-bold text-gray-400">{{ user.email }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-blue-50/50 p-4 rounded-2xl text-center border border-blue-100 shadow-sm">
                <p class="text-[10px] text-blue-500 font-black uppercase tracking-widest">{{ $t('home.level') }}</p>
                <p class="text-3xl font-black text-blue-700 mt-1">{{ user.nivell }}</p>
              </div>
              <div class="bg-purple-50/50 p-4 rounded-2xl text-center border border-purple-100 shadow-sm">
                <p class="text-[10px] text-purple-500 font-black uppercase tracking-widest">{{ $t('home.coins') }}</p>
                <p class="text-3xl font-black text-purple-700 mt-1">{{ user.monedes }}</p>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex justify-between text-[10px] font-black text-gray-400 uppercase tracking-widest">
                <span>{{ $t('perfil.experience') }}</span>
                <span class="text-blue-600">{{ user.xp_total % 1000 }} / 1000 XP</span>
              </div>
              <div class="w-full h-4 bg-gray-100 rounded-full overflow-hidden border border-gray-100 shadow-inner">
                <div class="h-full bg-gradient-to-r from-blue-400 to-blue-600 rounded-full transition-all duration-1000 shadow-sm" :style="{ width: xpPercent + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- LOGROS -->
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
          <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">🏅</div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('perfil.achievements') }}</h2>
          </div>
          
          <div v-if="user && user.logros && user.logros.length > 0" class="grid grid-cols-5 gap-3">
            <div v-for="logro in user.logros" :key="logro.id" class="group relative flex flex-col items-center">
              <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-[20px] shadow-sm group-hover:scale-110 transition-all border border-amber-100 cursor-pointer">
                🏆
              </div>
              <div class="absolute bottom-full mb-3 hidden group-hover:block bg-gray-900/95 text-white text-[10px] py-2 px-3 rounded-xl whitespace-nowrap z-20 shadow-xl border border-white/20">
                <p class="font-bold text-amber-400">{{ logro.nom }}</p>
                <p class="font-medium text-gray-300">{{ logro.descripcio }}</p>
              </div>
            </div>
          </div>

          <div v-else class="flex flex-col items-center justify-center py-12 text-gray-300">
            <div class="text-[40px] mb-2 opacity-20">🏆</div>
            <p class="text-xs font-black uppercase tracking-widest">{{ $t('perfil.no_achievements') }}</p>
          </div>
        </div>
      </div>

      <!-- Columna Dreta: Mascota y Historial -->
      <div class="col-span-12 lg:col-span-7 space-y-6">
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-white/50 h-auto flex flex-col">
          <div class="flex justify-between items-start mb-8">
            <div>
              <h2 class="text-2xl font-black text-gray-800 tracking-tight uppercase">{{ $t('perfil.pet_title') }}</h2>
              <p class="text-sm font-bold text-gray-400">{{ $t('perfil.happy') }}</p>
            </div>
            <div class="flex gap-3">
                <div class="bg-orange-50 px-4 py-2 rounded-2xl border border-orange-100 shadow-sm flex flex-col items-center">
                  <span class="text-[10px] font-black text-orange-400 uppercase tracking-widest">{{ $t('home.streak') }}</span>
                  <span class="text-xl font-black text-orange-600">{{ user ? user.ratxa_actual : 0 }}</span>
                </div>
                <div class="bg-yellow-50 px-4 py-2 rounded-2xl border border-yellow-100 shadow-sm flex flex-col items-center">
                  <span class="text-[10px] font-black text-yellow-500 uppercase tracking-widest">MAX</span>
                  <span class="text-xl font-black text-yellow-700">{{ user ? user.ratxa_maxima : 0 }}</span>
                </div>
            </div>
          </div>

          <!-- Àrea Mascota -->
          <div class="flex-1 rounded-[2.5rem] relative overflow-hidden flex items-center justify-center border-4 border-gray-50 min-h-[400px] shadow-inner" :style="estilFons">
            <div class="relative z-10 hover:scale-105 transition-all duration-700 animate-float">
              <img v-if="imatgeMascota" :src="imatgeMascota" alt="Mascota" class="w-64 h-64 lg:w-80 lg:h-80 object-contain drop-shadow-[0_20px_30px_rgba(0,0,0,0.3)]" />
            </div>
          </div>

          <div class="mt-8 text-center bg-gray-50/50 py-4 rounded-2xl border border-gray-100">
            <p class="text-gray-500 font-bold italic text-sm">{{ $t('perfil.pet_subtitle') }}</p>
          </div>
        </div>

        <!-- HISTORIAL DIARI (Compact) -->
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">{{ $t('perfil.history') }}</h2>
            <div v-if="loadingLogs" class="space-y-4">
              <div v-for="i in 3" :key="i" class="h-16 bg-gray-50 rounded-2xl animate-pulse"></div>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="(log, idx) in logs.slice(0, 4)" :key="idx" class="p-4 rounded-2xl bg-gray-50/50 border border-gray-100 flex items-center gap-4 transition-all hover:bg-white hover:border-blue-200 group">
                <div :class="log.completado ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'" class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shadow-sm">
                  {{ log.completado ? '✓' : '○' }}
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-black text-gray-800 truncate">{{ log.titol }}</p>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ log.dia }}</p>
                </div>
              </div>
            </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import bosqueImg from "~/assets/img/Bosque.png";
import mascotaImg from "~/assets/img/Mascota.png";
import { useAuthStore } from "~/stores/useAuthStore";
import { authFetch, getBaseUrl } from "~/composables/useApi.js";

var { t } = useI18n();
var authStore = useAuthStore();
var user = ref(null);
var loading = ref(true);
var logs = ref([]);
var loadingLogs = ref(true);

var imatgeMascota = mascotaImg;
var estilFons = {
  backgroundImage: "url(" + bosqueImg + ")",
  backgroundSize: "cover",
  backgroundPosition: "center",
};

var xpPercent = computed(function() {
  if (!user.value || !user.value.xp_total) return 0;
  return (user.value.xp_total % 1000) / 10;
});

onMounted(function() {
  loading.value = true;
  loadingLogs.value = true;
  var profilePromise = authFetch(getBaseUrl() + '/api/user/profile')
    .then(function(r) { return r.json(); })
    .then(function(d) { user.value = d.data || d; });
  var logsPromise = authFetch(getBaseUrl() + '/api/habits/logs')
    .then(function(r) { return r.json(); })
    .then(function(d) { logs.value = d.data || d || []; });
  Promise.all([profilePromise, logsPromise])
    .then(function() {
      loading.value = false;
      loadingLogs.value = false;
    })
    .catch(function(err) {
      console.error("Error carregant perfil:", err);
      loading.value = false;
      loadingLogs.value = false;
    });
});
</script>

<style scoped>
.animate-float {
  animation: float 6s ease-in-out infinite;
}
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
  100% { transform: translateY(0px); }
}
</style>
