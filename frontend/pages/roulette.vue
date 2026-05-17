<template>
  <div class="roulette-page min-h-screen overflow-x-hidden pb-24 lg:pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 sm:pt-8">
      
      <!-- Topbar amb botó d'enrere -->
      <div class="flex items-center justify-between mb-6">
        <button
          type="button"
          class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/80 hover:bg-white text-gray-700 font-bold shadow-md backdrop-blur-md border border-white/50 transition-all active:scale-95 cursor-pointer"
          @click="tornarHome"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>{{ $t('home.back') || 'Tornar' }}</span>
        </button>
      </div>

      <!-- Capçalera Bento -->
      <header class="bento-card mb-8 p-8 bg-white/95 backdrop-blur-md shadow-2xl border border-white/50 rounded-3xl text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 bg-orange-100 rounded-full -translate-y-20 translate-x-20 pointer-events-none blur-2xl"></div>
        <h1 class="text-3xl sm:text-4xl font-black text-gray-800 tracking-tight flex items-center justify-center gap-3">
          <span>🎡</span>
          <span>{{ $t('home.roulette') || 'Ruleta Diària' }}</span>
        </h1>
        <p class="text-base text-gray-500 mt-2 max-w-md mx-auto font-medium">
          {{ $t('home.roulette_daily') || 'Gira la ruleta cada dia per guanyar punts d\'experiència, monedes i recompenses especials per a la teva mascota!' }}
        </p>
      </header>

      <!-- Contingut Principal: La Ruleta Interactiva -->
      <div class="bento-card p-8 sm:p-12 bg-white/95 backdrop-blur-md shadow-2xl border border-white/50 rounded-3xl flex flex-col items-center justify-center relative">
        
        <!-- Punter Superior (Fletxa) -->
        <div class="absolute top-4 sm:top-8 left-1/2 -translate-x-1/2 z-30 flex flex-col items-center select-none filter drop-shadow-[0_4px_8px_rgba(0,0,0,0.3)]">
          <div class="w-8 h-12 bg-gradient-to-b from-red-500 to-red-700 rounded-b-full shadow-inner border-2 border-white/80"></div>
          <div class="w-3 h-3 bg-white rounded-full -mt-3 shadow-sm"></div>
        </div>

        <!-- Roda de la Ruleta -->
        <div class="relative w-72 h-72 sm:w-96 sm:h-96 my-4 flex items-center justify-center select-none">
          <!-- Ombra exterior brillant -->
          <div class="absolute inset-0 rounded-full bg-orange-400/20 blur-3xl pointer-events-none animate-pulse"></div>

          <!-- Roda giratòria -->
          <div
            class="w-full h-full rounded-full border-[10px] border-orange-400 shadow-[0_10px_35px_rgba(0,0,0,0.25)] relative overflow-hidden flex items-center justify-center"
            :style="rodaStyle"
            :class="{ 'transition-none': isContinuousSpinning }"
          >
            <!-- Fons de sectors mitjançant conic-gradient -->
            <div class="absolute inset-0" :style="conicGradientStyle"></div>

            <!-- Línies separadores i contingut dels sectors -->
            <div
              v-for="(sector, index) in rouletteSectors"
              :key="sector.id"
              class="absolute inset-0 flex items-start justify-center"
              :style="getSectorTransform(index)"
            >
              <!-- Línia separadora -->
              <div class="absolute top-0 w-[3px] h-1/2 bg-orange-400/40 origin-bottom"></div>

              <!-- Icona i text del premi -->
              <div class="pt-6 sm:pt-8 flex flex-col items-center text-center transform -rotate-0 z-10" :style="{ color: sector.color }">
                <span class="text-3xl sm:text-4xl filter drop-shadow-sm mb-1">{{ sector.icon }}</span>
                <span class="text-xs sm:text-sm font-black uppercase tracking-wider px-2 py-0.5 rounded-lg bg-white/60 backdrop-blur-sm border border-white/40 shadow-sm">
                  {{ sector.label }}
                </span>
              </div>
            </div>

            <!-- Centre decoratiu de la roda -->
            <div class="absolute w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-orange-300 to-amber-500 border-4 border-white shadow-xl flex items-center justify-center z-20">
              <span class="text-2xl sm:text-3xl animate-spin-slow">✨</span>
            </div>
          </div>
        </div>

        <!-- Estat i Botó d'Acció -->
        <div class="w-full max-w-sm mt-8 space-y-4 text-center">
          <div v-if="!canSpin" class="bg-gray-50 p-4 rounded-2xl border border-gray-200/80">
            <p class="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-wider">
              {{ $t('home.roulette_not_available') || 'Ja has tirat la ruleta avui. Torna demà!' }}
            </p>
          </div>

          <button
            v-if="canSpin"
            type="button"
            class="w-full py-5 px-8 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-lg tracking-wider uppercase shadow-[0_8px_20px_rgba(249,115,22,0.4)] active:scale-95 disabled:opacity-50 transition-all cursor-pointer"
            :disabled="isSpinning"
            @click="girarRuleta"
          >
            <span v-if="isSpinning">{{ $t('shop.loading') || 'Girant...' }}</span>
            <span v-else>{{ $t('home.roulette_spin_text') || 'Girar Ruleta!' }}</span>
          </button>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useGameStore } from '~/stores/gameStore.js';
import { useAuthStore } from '~/stores/useAuthStore.js';
const gameStore = useGameStore();
const authStore = useAuthStore();
const nuxtApp = useNuxtApp();
const $swal = nuxtApp.$swal;
const $socket = nuxtApp.$socket;
const { t } = useI18n();

// Definim els sectors de la ruleta amb els seus premis, icones i colors espectaculars
const rouletteSectors = [
  { id: 1, label: "50 XP", icon: "⚡", bg: "#FEF08A", color: "#854D0E", matchKeys: ["50 xp", "50xp", "50"] },
  { id: 2, label: "5 Monedes", icon: "🪙", bg: "#FED7AA", color: "#9A3412", matchKeys: ["5 monedes", "5 moned", "5"] },
  { id: 3, label: "100 XP", icon: "⚡", bg: "#BBF7D0", color: "#166534", matchKeys: ["100 xp", "100xp", "100"] },
  { id: 4, label: "1 Moneda", icon: "🪙", bg: "#E9D5FF", color: "#6B21A8", matchKeys: ["1 moneda", "1 moned", "1"] },
  { id: 5, label: "10 Monedes", icon: "🪙", bg: "#FECACA", color: "#991B1B", matchKeys: ["10 monedes", "10 moned", "10"] },
  { id: 6, label: "200 XP", icon: "🌟", bg: "#BFDBFE", color: "#1E40AF", matchKeys: ["200 xp", "200xp", "200"] },
];

const canSpin = computed(() => gameStore.canSpinRoulette);

const currentRotation = ref(0);
const isSpinning = ref(false);
const isContinuousSpinning = ref(false);
let continuousSpinInterval = null;

const rodaStyle = computed(() => {
  return {
    transform: `rotate(${currentRotation.value}deg)`,
    transition: isContinuousSpinning.value ? 'none' : 'transform 4s cubic-bezier(0.25, 1, 0.5, 1)'
  };
});

const conicGradientStyle = computed(() => {
  const numSectors = rouletteSectors.length;
  const anglePerSector = 360 / numSectors;
  const parts = rouletteSectors.map((sector, index) => {
    const startAngle = index * anglePerSector;
    const endAngle = (index + 1) * anglePerSector;
    return `${sector.bg} ${startAngle}deg ${endAngle}deg`;
  });
  return `background: conic-gradient(${parts.join(', ')})`;
});

function getSectorTransform(index) {
  const numSectors = rouletteSectors.length;
  const anglePerSector = 360 / numSectors;
  // Centrem el sector a la part superior (angle 0)
  const rotation = index * anglePerSector;
  return `transform: rotate(${rotation}deg)`;
}

function tornarHome() {
  if (isSpinning.value) return;
  navigateTo('/home');
}

function iniciarGirContinu() {
  aturarGirContinu();
  isContinuousSpinning.value = true;
  continuousSpinInterval = setInterval(() => {
    currentRotation.value = (currentRotation.value + 15) % 360;
  }, 30);
}

function aturarGirContinu() {
  if (continuousSpinInterval) {
    clearInterval(continuousSpinInterval);
    continuousSpinInterval = null;
  }
  isContinuousSpinning.value = false;
}

function girarRuleta() {
  if (!canSpin.value || isSpinning.value) return;

  if (!$socket || !$socket.connected) {
    $swal.fire({
      icon: 'warning',
      title: 'Sense connexió',
      text: 'No s\'ha pogut connectar amb el servidor per girar la ruleta.'
    });
    return;
  }

  isSpinning.value = true;
  iniciarGirContinu();

  // Enviem la petició de gir al backend via socket
  $socket.emit("roulette_spin", {});
}

function gestionarResultatServidor(data) {
  aturarGirContinu();

  if (!data) {
    isSpinning.value = false;
    return;
  }

  if (data.error) {
    isSpinning.value = false;
    $swal.fire({
      icon: 'error',
      title: 'Error',
      text: data.error
    });
    return;
  }

  // Actualitzem l'estat local del store
  gameStore.canSpinRoulette = false;
  if (data.ruleta_ultima_tirada !== undefined) {
    gameStore.ruletaUltimaTirada = data.ruleta_ultima_tirada;
  }
  gameStore.obtenirEstatJoc();

  const premiLabel = data.label || data.premi_text || data.premi_valor || "Premi";
  const premiLower = String(premiLabel).toLowerCase();

  // Busquem el sector que coincideixi millor amb el premi guanyador
  let targetSectorIndex = 0;
  for (let i = 0; i < rouletteSectors.length; i++) {
    const keys = rouletteSectors[i].matchKeys;
    if (keys.some(k => premiLower.includes(k))) {
      targetSectorIndex = i;
      break;
    }
  }

  // Calculem l'angle de destinació perquè el sector guanyador quedi sota el punter (a dalt de tot, 0 graus)
  const numSectors = rouletteSectors.length;
  const anglePerSector = 360 / numSectors;
  // L'angle del sector guanyador
  const sectorAngle = targetSectorIndex * anglePerSector + (anglePerSector / 2);
  
  // Perquè aquest angle quedi a dalt (0 graus), la roda ha de girar (360 - sectorAngle)
  const baseTargetAngle = 360 - sectorAngle;
  // Afegim 4 voltes completes per fer l'animació espectacular
  const finalTargetAngle = currentRotation.value + (360 * 4) + ((baseTargetAngle - (currentRotation.value % 360)) % 360);

  currentRotation.value = finalTargetAngle;

  // Esperem que acabi l'animació CSS (4 segons) per mostrar el premi
  setTimeout(() => {
    isSpinning.value = false;
    $swal.fire({
      icon: 'success',
      title: t('home.roulette_won_title') || 'Enhorabona!',
      text: t('home.roulette_won_text', { premi: premiLabel }) || `Has guanyat ${premiLabel}! 🎉`,
      confirmButtonColor: '#f97316'
    });
  }, 4000);
}

onMounted(() => {
  if ($socket) {
    $socket.on("roulette_result", gestionarResultatServidor);
  }
  gameStore.obtenirEstatJoc();
});

onBeforeUnmount(() => {
  aturarGirContinu();
  if ($socket) {
    $socket.off("roulette_result", gestionarResultatServidor);
  }
});
</script>

<style scoped>
.roulette-page {
  background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
}

@keyframes spinSlow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spinSlow 12s linear infinite;
}
</style>
