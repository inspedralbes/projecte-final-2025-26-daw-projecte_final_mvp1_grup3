<template>
  <div class="home-page-root relative w-full min-h-screen pb-24 lg:pb-12 overflow-y-auto">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 flex flex-col gap-8 lg:gap-6 pb-16 lg:pb-20">
      <!-- 1. Monstre: mateix patró que home (imatge sobre el fons global en mòbil; bento en desktop) -->
      <div class="space-y-4 lg:space-y-0">
        <div class="lg:hidden relative w-full flex justify-center px-2 pt-0 pb-1 overflow-visible">
          <img
            v-if="imatgeMascota"
            :src="imatgeMascota"
            alt="El teu monstre"
            class="w-[93vw] max-w-[408px] h-auto max-h-[21rem] sm:max-h-[24rem] object-contain object-bottom drop-shadow-[0_14px_28px_rgba(0,0,0,0.35)] select-none -translate-y-3 sm:-translate-y-4"
            decoding="async"
            draggable="false"
          />
        </div>

        <div class="hidden lg:flex bento-card rounded-3xl p-8 flex-col items-center relative w-full min-h-0 bg-white/95 backdrop-blur-md shadow-2xl border border-white/50 shrink-0">
          <div class="flex shrink-0 items-center justify-between w-full mb-6 relative z-10">
            <div>
              <h2 class="text-2xl font-black text-gray-800 tracking-tight">
                {{ $t('home.monster_title') }}
              </h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $t('home.level') }} {{ user ? user.nivell : '—' }}</span>
              </div>
            </div>
            <UserHomeHomeStreakSection
              :ratxa="user ? user.ratxa_actual : 0"
              :ratxa-maxima="user ? user.ratxa_maxima : 0"
              :xp-total="user ? user.xp_total : 0"
              :monedes="user ? user.monedes : 0"
            />
          </div>

          <div class="w-full flex flex-col items-center justify-start relative pt-2 shrink-0">
            <div class="flex justify-center w-full px-2 pb-2 -mt-1">
              <img
                v-if="imatgeMascota"
                :src="imatgeMascota"
                alt="El teu monstre"
                class="h-[min(32rem,78vh)] w-[min(32rem,94vw)] max-h-[min(32rem,78vh)] max-w-[min(32rem,94vw)] object-contain object-bottom drop-shadow-[0_20px_20px_rgba(0,0,0,0.28)] -translate-y-3 lg:-translate-y-5"
                decoding="async"
                draggable="false"
              />
            </div>
          </div>

          <p class="text-center text-gray-500 font-medium text-sm mt-6 max-w-sm shrink-0">
            {{ $t('home.monster_subtitle') }}
          </p>
        </div>
      </div>
      <!-- 2. Dades del perfil -->
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
            <div class="w-20 h-20 rounded-3xl overflow-hidden shadow-lg border border-gray-200 bg-white/20" :style="avatarBackgroundStyle">
              <div class="w-full h-full p-1 flex items-center justify-center">
                <img
                  v-if="imatgeMascota"
                  :src="imatgeMascota"
                  alt="Monstre del perfil"
                  class="w-full h-full object-contain"
                  decoding="async"
                  draggable="false"
                />
              </div>
            </div>
            <div class="min-w-0">
              <h3 class="text-2xl font-black text-gray-800 truncate tracking-tight">{{ user.nom }}</h3>
              <p class="text-sm font-bold text-gray-400">{{ user.email }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-blue-50/50 p-4 rounded-2xl text-center border border-blue-100 shadow-sm">
              <p class="text-[10px] text-blue-500 font-black uppercase tracking-widest">{{ $t('home.level') }}</p>
              <p class="text-3xl font-black text-blue-700 mt-1">{{ user.nivell }}</p>
            </div>
            <div class="bg-purple-50/50 p-4 rounded-2xl text-center border border-purple-100 shadow-sm">
              <p class="text-[10px] text-purple-500 font-black uppercase tracking-widest flex items-center justify-center gap-1.5">
                <img :src="coinLoopy" alt="" class="h-4 w-4 object-contain shrink-0 coin-pixel" width="16" height="16" aria-hidden="true" />
                <span>{{ $t('home.coins') }}</span>
              </p>
              <p class="text-3xl font-black text-purple-700 mt-1">{{ user.monedes }}</p>
            </div>
            <div class="col-span-2 sm:col-span-1 bg-orange-50/50 p-4 rounded-2xl text-center border border-orange-100 shadow-sm">
              <p class="text-[10px] text-orange-500 font-black uppercase tracking-widest">{{ $t('perfil.streak_days') }}</p>
              <p class="text-3xl font-black text-orange-700 mt-1">{{ user.ratxa_actual != null ? user.ratxa_actual : 0 }}</p>
              <p class="text-[10px] font-bold text-orange-600/80 mt-1.5 uppercase tracking-wide">{{ $t('home.max_streak') }}: {{ user.ratxa_maxima != null ? user.ratxa_maxima : 0 }}</p>
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

      <!-- 3. Logros i medalles (showcase fins a 3) -->
      <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
        <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
          <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-xl shadow-sm">🏅</div>
          <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('perfil.achievements') }}</h2>
          <span v-if="user && user.logros && user.logros.length > 0" class="ml-auto text-xs text-gray-400">Màx. 3 al teu perfil</span>
        </div>

        <div v-if="user && user.logros && user.logros.length > 0" class="grid grid-cols-5 gap-3">
          <div
            v-for="logro in user.logros"
            :key="logro.id"
            class="group relative flex flex-col items-center cursor-pointer"
            @click="toggleShowcaseLogro(logro.id)"
          >
            <div
              class="w-12 h-12 rounded-2xl flex items-center justify-center text-[20px] shadow-sm transition-all border-2"
              :class="showcaseLogros.includes(logro.id) ? 'bg-purple-100 border-purple-500 text-purple-600' : 'bg-amber-50 border-amber-100 hover:border-purple-300'"
            >
              {{ showcaseLogros.includes(logro.id) ? '⭐' : '🏆' }}
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

        <button
          v-if="showcaseChanged"
          type="button"
          @click="guardarShowcase"
          :disabled="guardantShowcase"
          class="mt-6 w-full py-3 px-6 rounded-2xl font-bold text-white transition-all"
          :class="guardantShowcase ? 'bg-gray-400 cursor-wait' : 'bg-purple-600 hover:bg-purple-700'"
        >
          {{ guardantShowcase ? 'Guardant…' : 'Desar selecció' }}
        </button>
      </div>

      <!-- 4. Historial diari -->
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
</template>

<script setup>
import mascotaImg from "~/assets/img/Mascota.png";
import coinLoopy from "~/assets/img/coin-loopy.png";
import bosqueImg from "~/assets/img/Bosque.png";
import { authFetch, getBaseUrl } from "~/composables/useApi.js";

var user = ref(null);
var loading = ref(true);
var logs = ref([]);
var loadingLogs = ref(true);
var showcaseLogros = ref([]);
var guardantShowcase = ref(false);
var showcaseChanged = ref(false);
var originalShowcase = ref([]);

var imatgeMascota = mascotaImg;
var avatarBackgroundStyle = computed(function() {
  return {
    backgroundImage: "url(" + bosqueImg + ")",
    backgroundSize: "cover",
    backgroundPosition: "center",
  };
});

var xpPercent = computed(function() {
  if (!user.value || !user.value.xp_total) return 0;
  return (user.value.xp_total % 1000) / 10;
});

onMounted(function() {
  loading.value = true;
  loadingLogs.value = true;
  var profilePromise = authFetch(getBaseUrl() + '/api/user/profile')
    .then(function(r) { return r.json(); })
    .then(function(d) { 
      user.value = d.data || d; 
      if (user.value.logros_showcase) {
        originalShowcase.value = user.value.logros_showcase.map(function(l) { return l.id; });
        showcaseLogros.value = [].concat(originalShowcase.value);
      }
    });
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

function toggleShowcaseLogro(logroId) {
  var idx = showcaseLogros.value.indexOf(logroId);
  if (idx > -1) {
    showcaseLogros.value.splice(idx, 1);
  } else if (showcaseLogros.value.length < 3) {
    showcaseLogros.value.push(logroId);
  }
  showcaseChanged.value = true;
}

function guardarShowcase() {
  guardantShowcase.value = true;
  authFetch(getBaseUrl() + '/api/users/self/showcase', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ logros: showcaseLogros.value }),
  })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      guardantShowcase.value = false;
      if (d.success) {
        alert('¡Logros guardados!');
      } else {
        alert(d.error || 'Error al guardar');
      }
    })
    .catch(function(err) {
      guardantShowcase.value = false;
      console.error("Error guardant showcase:", err);
      alert('Error al guardar');
    });
}
</script>

<style scoped>
.coin-pixel {
  image-rendering: pixelated;
}
</style>
