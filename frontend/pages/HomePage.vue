<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { io } from "socket.io-client";
import { useGameStore } from "~/stores/gameStore.js";
import bosqueImg from "~/assets/img/Bosque.png";

// Store de Pinia con sistema de rollback
const gameStore = useGameStore();

const backgroundStyle = {
  backgroundImage: `url(${bosqueImg})`,
  backgroundSize: "cover",
  backgroundPosition: "center",
};

// Socket
let socket = null;
const isLoading = ref(false);
const isLoadingHabitos = ref(false);
const errorMessage = ref("");

// Computables desde el store
const racha = computed(() => gameStore.racha);
const xpTotal = computed(() => gameStore.xpTotal);
const habitos = computed(() => gameStore.habitos);
const userId = computed(() => gameStore.userId);

// XP por dificultad (solo visual)
const xpPerDificultat = {
  facil: 100,
  media: 250,
  dificil: 400,
};

const xpPerHabit = (habito) => {
  if (habito && habito.dificultat && xpPerDificultat[habito.dificultat]) {
    return xpPerDificultat[habito.dificultat];
  }
  if (habito && habito.xpReward !== undefined) {
    return habito.xpReward;
  }
  return 0;
};

// Inicializar socket
onMounted(async () => {
  // TODO: Obtener userId de autenticación
  gameStore.setUserId(1);

  // Cargar hábitos desde la API
  isLoadingHabitos.value = true;
  try {
    await gameStore.fetchHabitos();
    await gameStore.fetchGameState();
    console.log("✅ Hábitos cargados:", gameStore.habitos);
  } catch (error) {
    console.error("❌ Error cargando hábitos:", error);
    errorMessage.value = "Error al cargar los hábitos";
  } finally {
    isLoadingHabitos.value = false;
  }

  // Conectar al servidor de sockets
  socket = io("http://localhost:3001", {
    reconnection: true,
    reconnectionDelay: 1000,
    reconnectionDelayMax: 5000,
    reconnectionAttempts: 5,
  });

  socket.on("connect", () => {
    console.log("✅ Conectado al servidor de sockets:", socket.id);
  });

  // Escuchar feedback unificado desde el backend (Redis -> Node)
  socket.on("update_xp", (data) => {
    console.log("⭐ Feedback gamificación:", data);
    if (data && data.ratxa_actual !== undefined) {
      gameStore.updateRacha(data.ratxa_actual);
    }
    if (data && data.xp_total !== undefined) {
      gameStore.updateXP(data.xp_total);
    }
  });

  socket.on("disconnect", () => {
    console.log("❌ Desconectado del servidor de sockets");
  });

  socket.on("error", (error) => {
    console.error("⚠️ Error en socket:", error);
  });
});

// Limpiar socket cuando se desmonta el componente
onUnmounted(() => {
  if (socket) {
    socket.disconnect();
  }
});

/**
 * Completa un hábito con snapshot y rollback automático
 * - Cambio visual inmediato (0ms latencia)
 * - Emite al backend
 * - Si falla: restaura automáticamente
 */
const completarHabito = async (habitoId) => {
  try {
    isLoading.value = true;
    errorMessage.value = "";

    console.log("🎯 Iniciando completar hábito:", habitoId);

    // Llamar a la acción del store que maneja snapshot + rollback
    const success = await gameStore.completHabit(habitoId, socket);

    if (!success) {
      errorMessage.value =
        "No se pudo completar el hábito. Los cambios han sido revertidos.";
      console.error("❌ Fallida la operación - cambios revertidos");
    } else {
      console.log("✅ Hábito completado exitosamente");
    }
  } catch (error) {
    console.error("Error completando hábito:", error);
    errorMessage.value = "Error al completar el hábito";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <main class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Grid Principal -->
      <div class="grid grid-cols-12 gap-6">
        <!-- COSTAT ESQUERRE: Missions i Perfil -->
        <div class="col-span-3 space-y-6">
          <!-- Targeta Missions Diàries -->
          <div
            class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-400"
          >
            <div class="flex items-center gap-2 mb-4">
              <div
                class="w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center"
              >
                <span class="text-white text-sm">✓</span>
              </div>
              <h2
                class="text-sm font-bold text-gray-800 uppercase tracking-wide"
              >
                Missions Diàries
              </h2>
            </div>

            <div class="space-y-3">
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-700 font-semibold text-sm">Missió Diària</p>
                <p class="text-2xl font-bold text-orange-500">0/1</p>
              </div>
            </div>

            <!-- Divisor -->
            <div class="h-px bg-gray-200 my-4"></div>

            <!-- Perfil Usuari -->
            <div class="text-center">
              <div
                class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 mx-auto mb-3 flex items-center justify-center"
              >
                <span class="text-3xl"></span>
              </div>
              <h3 class="font-bold text-gray-800 text-sm">Nom</h3>
              <p class="text-xs text-gray-500 mb-2">Etiqueta</p>
              <div
                class="flex justify-center items-center gap-1 text-xs text-gray-600"
              >
                <span>Lv 1</span>
                <div class="w-20 h-1 bg-gray-200 rounded-full"></div>
              </div>
            </div>
          </div>

          <!-- Targeta Últims Assoliments -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3
              class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4"
            >
              Últims Assoliments
            </h3>
            <div class="flex justify-around items-center">
              <div
                class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-lg hover:scale-110 transition"
              ></div>
              <div
                class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg hover:scale-110 transition"
              ></div>
              <div
                class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-lg hover:scale-110 transition"
              ></div>
            </div>
          </div>
        </div>

        <!-- CENTRE: El teu Monstre -->
        <div class="col-span-6 space-y-6">
          <!-- Targeta El teu Monstre -->
          <div
            class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative"
          >
            <!-- Fons Decoratiu -->
            <div class="absolute inset-0 rounded-2xl opacity-40"></div>

            <!-- Contingut -->
            <div class="relative z-10">
              <div class="flex items-center justify-between w-full mb-4">
                <div>
                  <h2 class="text-lg font-bold text-gray-800">
                    EL TEU MONSTRE
                  </h2>
                  <p class="text-xs text-gray-500">Lv 1</p>
                </div>
                <div>
                  <p class="text-2xl font-bold">Racha: {{ racha }}</p>
                  <p class="text-sm text-green-600">XP Total: {{ xpTotal }}</p>
                </div>
              </div>

              <!-- Imatge Monstre -->
              <div
                class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative"
                :style="estilFons"
                style="min-width: 450px"
              >
                <div
                  class="w-40 h-40 rounded-xl flex items-center justify-center mb-6 overflow-hidden mx-auto"
                >
                  <img
                    src="assets/img/Mascota.png"
                    alt="El teu monstre"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
              <p class="text-center text-gray-600 text-sm">
                ¡Ho estàs fent genial!
              </p>
            </div>
          </div>
        </div>

        <!-- COSTAT DRET: Hàbits -->
        <div class="col-span-3 space-y-6">
          <!-- Capçalera Hàbits -->
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">HÀBITS</h2>
            <a
              href="#"
              class="text-blue-500 text-xs font-semibold hover:underline"
              >VEURE TOT</a
            >
          </div>

          <!-- Llista d'Hàbits -->
          <div class="space-y-3">
            <!-- Mensaje de error con rollback -->
            <div
              v-if="errorMessage"
              class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
            >
              <span class="block sm:inline">{{ errorMessage }}</span>
              <button
                @click="errorMessage = ''"
                class="absolute top-0 bottom-0 right-0 px-4 py-3"
              >
                ✕
              </button>
            </div>

            <!-- Loading state -->
            <div
              v-if="isLoadingHabitos"
              class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded"
            >
              <span>Cargando hábitos...</span>
            </div>

            <!-- Empty state -->
            <div
              v-else-if="habitos.length === 0"
              class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-3 rounded text-center"
            >
              <span>No hay hábitos disponibles</span>
            </div>

            <!-- Hábitos de la API -->
            <template v-else>
              <div
                v-for="habito in habitos"
                :key="habito.id"
                class="bg-white rounded-lg p-4 shadow flex items-center justify-between"
              >
                <div>
                  <p class="font-semibold text-gray-800">{{ habito.nombre }}</p>
                  <p class="text-xs text-gray-500">
                    {{ habito.descripcion }} • +{{ xpPerHabit(habito) }} XP
                  </p>
                  <p
                    v-if="habito.completado"
                    class="text-xs text-green-600 font-semibold"
                  >
                    ✓ Completado
                  </p>
                </div>
                <button
                  v-if="!habito.completado"
                  @click="completarHabito(habito.id)"
                  :disabled="isLoading"
                  class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ isLoading ? "Procesando..." : "Completar" }}
                </button>
                <div v-else class="text-green-500 font-bold">✓</div>
              </div>
            </template>
          </div>

          <!-- Targeta Diari -->
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
