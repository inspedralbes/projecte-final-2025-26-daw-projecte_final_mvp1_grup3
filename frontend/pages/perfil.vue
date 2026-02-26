<template>
  <div class="w-full bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto w-full">
      <h1 class="text-2xl font-bold text-gray-800 mb-8 px-2">Perfil d'Usuari</h1>

      <!-- Bento Grid Container -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 w-full pb-10">
        
        <!-- Columna Esquerra: Dades Perfil i Logros -->
        <div class="col-span-1 lg:col-span-5 space-y-6 w-full">
          
          <!-- DADES PERFIL -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-blue-500 w-full">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-6">Dades del Perfil</h2>
            
            <div v-if="loading" class="animate-pulse space-y-4">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
            
            <div v-else-if="user" class="space-y-6 w-full">
              <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                  {{ obtenirInicialUsuari() }}
                </div>
                <div class="min-w-0">
                  <h3 class="text-xl font-bold text-gray-800 truncate">{{ user.nom }}</h3>
                  <p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-xl text-center">
                  <p class="text-xs text-blue-600 font-bold uppercase">Nivell</p>
                  <p class="text-2xl font-black text-blue-800">{{ user.nivell }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-xl text-center">
                  <p class="text-xs text-purple-600 font-bold uppercase">Monedes</p>
                  <p class="text-2xl font-black text-purple-800">{{ user.monedes }}</p>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between text-xs font-bold text-gray-600">
                  <span>EXPERIÈNCIA DEL NIVELL</span>
                  <span>{{ user.xp_total % 1000 }} / 1000 XP</span>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden border border-gray-200">
                  <div class="h-full bg-blue-500 rounded-full transition-all duration-1000" :style="{ width: xpPercent + '%' }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- LOGROS -->
          <div class="bg-white rounded-2xl shadow-lg p-6 min-h-[300px] w-full">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-6">Logros i Medalles</h2>
            
            <div v-if="loading" class="grid grid-cols-4 gap-4">
              <div v-for="i in 4" :key="i" class="w-12 h-12 bg-gray-100 rounded-full animate-pulse"></div>
            </div>

            <div v-else-if="user && user.logros && user.logros.length > 0" class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-4 xl:grid-cols-5 gap-4">
              <div v-for="logro in user.logros" :key="logro.id" class="group relative flex flex-col items-center">
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-[10px] font-bold text-orange-600 shadow-sm group-hover:scale-110 transition-transform cursor-pointer border border-orange-200">
                  LOGRO
                </div>
                <!-- Tooltip simple -->
                <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded whitespace-nowrap z-20">
                  {{ logro.nom }}: {{ logro.descripcio }}
                </div>
              </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center h-48 text-gray-400">
              <div class="text-xs font-bold uppercase mb-2 border border-gray-200 px-3 py-1 rounded">Bloquejat</div>
              <p class="text-sm">Encara no has obtingut cap logro</p>
            </div>
          </div>
        </div>

        <!-- Columna Dreta: Mascota -->
        <div class="col-span-1 lg:col-span-7 w-full">
          <div class="bg-white rounded-2xl shadow-lg p-8 h-full flex flex-col w-full">
            <div class="flex justify-between items-start mb-8">
              <div>
                <h2 class="text-lg font-bold text-gray-800 uppercase tracking-tight">La teva Mascota</h2>
                <p class="text-sm text-gray-500">Estat actual: Feliç</p>
              </div>
              <div class="text-right flex-shrink-0">
                <div class="inline-flex items-center gap-2 bg-orange-100 px-4 py-2 rounded-full border border-orange-200 shadow-sm">
                  <span class="text-orange-600 font-bold text-sm tracking-wide">RATXA: {{ obtenirRatxaActual() }}</span>
                </div>
              </div>
            </div>

            <!-- Àrea Mascota -->
            <div class="flex-1 rounded-3xl relative overflow-hidden flex items-center justify-center border-4 border-gray-50 min-h-[450px] w-full" :style="estilFons">
              <div class="relative z-10 hover:scale-105 transition-transform duration-500">
                <img v-if="imatgeMascota" :src="imatgeMascota" alt="Mascota" class="w-72 h-72 lg:w-96 lg:h-96 object-contain drop-shadow-2xl" />
              </div>
              <!-- Ombra de la mascota -->
              <div class="absolute bottom-16 w-40 h-8 bg-black opacity-10 rounded-[100%] blur-md"></div>
            </div>

            <div class="mt-8 text-center">
              <p class="text-gray-600 italic text-sm">"¡Ho estàs fent genial! Continua així per cuidar el teu company."</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
// A. Imports (Seguint AgentNuxt.md: auto-imports preferibles)
import bosqueImg from "~/assets/img/Bosque.png";
import mascotaImg from "~/assets/img/Mascota.png";

// Estat reactiu amb variables VAR (ES5 segons AgentNuxt.md)
var config = useRuntimeConfig();
var user = ref(null);
var loading = ref(true);
var error = ref(null);

// Recursos estàtics
var imatgeMascota = mascotaImg;
var estilFons = {
  backgroundImage: "url(" + bosqueImg + ")",
  backgroundSize: "cover",
  backgroundPosition: "center",
};

/**
 * Propietat computada per calcular el percentatge de la barra d'experiència.
 * Suposem 1000 XP per pujar de nivell per a la visualització.
 */
var xpPercent = computed(function () {
  if (!user.value || !user.value.xp_total) {
    return 0;
  }
  // Calculem el progrés respecte a un llindar de 1000 XP
  var progre = (user.value.xp_total % 1000) / 10;
  return progre;
});

/**
 * Funció clàssica per carregar el perfil des de l'API.
 */
function carregarPerfil() {
  var baseUrl, fullUrl;
  loading.value = true;
  
  baseUrl = config.public.apiUrl;
  if (baseUrl.endsWith('/')) {
    fullUrl = baseUrl + 'api/user/profile';
  } else {
    fullUrl = baseUrl + '/api/user/profile';
  }

  $fetch(fullUrl)
    .then(function(dades) {
      user.value = dades;
      loading.value = false;
    })
    .catch(function(err) {
      console.error("Error al carregar perfil:", err);
      error.value = "Error al carregar la informació del perfil.";
      loading.value = false;
    });
}

// Inicialització quan el component estigui muntat
onMounted(function() {
  carregarPerfil();
});

/**
 * Retorna la inicial del nom d'usuari.
 */
function obtenirInicialUsuari() {
  if (user.value && user.value.nom) {
    return user.value.nom.charAt(0);
  }
  return 'U';
}

/**
 * Retorna la ratxa actual de l'usuari.
 */
function obtenirRatxaActual() {
  if (user.value && user.value.ratxa_actual) {
    return user.value.ratxa_actual;
  }
  return 0;
}
</script>

<style scoped>
/* Transicions suaus per elements del bento */
.grid > div {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.grid > div:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}
</style>
