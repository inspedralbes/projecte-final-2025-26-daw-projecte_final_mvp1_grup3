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
            <div class="flex justify-around items-center relative group">
              <div
                class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-lg hover:scale-110 transition"
              ></div>
              <div
                class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg hover:scale-110 transition"
              ></div>
              <div
                class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-lg hover:scale-110 transition"
              ></div>
              
              <!-- Botó per veure tots els logros -->
              <button 
                @click="obrirModalLogros"
                class="absolute -right-2 -bottom-2 w-8 h-8 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 hover:scale-110 transition flex items-center justify-center font-bold text-xl"
                title="Veure tots els logros"
              >
                +
              </button>
            </div>
          </div>
        </div>

        <!-- CENTRE: El teu Monstre -->
        <div class="col-span-6 space-y-6">
          <!-- Targeta El teu Monstre -->
          <div
            class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative"
          >
            <!-- Contingut -->
            <div class="relative z-10 w-full">
              <div class="flex items-center justify-between w-full mb-4">
                <div>
                  <h2 class="text-lg font-bold text-gray-800">
                    EL TEU MONSTRE
                  </h2>
                  <p class="text-xs text-gray-500">Lv 1</p>
                </div>
                <div class="text-right">
                  <p class="text-2xl font-bold">Ratxa: {{ ratxa }}</p>
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
                    v-if="imatgeMascota"
                    :src="imatgeMascota"
                    alt="El teu monstre"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
              <p class="text-center text-gray-600 text-sm mt-4">
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
            <NuxtLink
              to="/habits"
              class="text-blue-500 text-xs font-semibold hover:underline"
              >VEURE TOT</NuxtLink
            >
          </div>

          <!-- Llista d'Hàbits -->
          <div class="space-y-3">
            <!-- Missatge d'error -->
            <div
              v-if="errorMissatge"
              class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
            >
              <span class="block sm:inline">{{ errorMissatge }}</span>
              <button
                @click="errorMissatge = ''"
                class="absolute top-0 bottom-0 right-0 px-4 py-3"
              >
                ✕
              </button>
            </div>

            <!-- Estat de càrrega -->
            <div
              v-if="estaCarregantHabits"
              class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-center"
            >
              <span>Carregant hàbits...</span>
            </div>

            <!-- Estat buit -->
            <div
              v-else-if="habits.length === 0"
              class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-3 rounded text-center"
            >
              <span>No hi ha hàbits disponibles</span>
            </div>

            <!-- Llista d'hàbits -->
            <template v-else>
              <div
                v-for="hàbit in habits"
                :key="hàbit.id"
                class="bg-white rounded-lg p-4 shadow flex items-center justify-between transition-all hover:shadow-md"
              >
                <div class="flex-1 mr-3">
                  <p class="font-semibold text-gray-800">{{ hàbit.nom }}</p>
                  <p class="text-xs text-gray-500 truncate">
                    {{ hàbit.descripcio }} • +{{ hàbit.recompensaXP }} XP
                  </p>
                  <p
                    v-if="hàbit.completat"
                    class="text-xs text-green-600 font-semibold"
                  >
                    ✓ Completat
                  </p>
                </div>
                <button
                  v-if="!hàbit.completat"
                  @click="completarHabit(hàbit.id)"
                  :disabled="comvprovarSiSestaProcessant(hàbit.id)"
                  class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed min-w-[90px]"
                >
                  <span v-if="comvprovarSiSestaProcessant(hàbit.id)">...</span>
                  <span v-else>Completar</span>
                </button>
                <div v-else class="text-green-500 font-bold ml-2">✓</div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL DE LOGROS (Achivements) -->
    <div v-if="esObertModalLogros" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancarModalLogros"></div>
      
      <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[85vh] overflow-hidden shadow-2xl relative animate-in fade-in zoom-in duration-200 flex flex-col">
        <!-- Capçalera Modal -->
        <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-xl">
              🏆
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">Tots els Logros</h2>
              <p class="text-xs text-gray-500">Descobreix nous reptes i medalles</p>
            </div>
          </div>
          <button @click="tancarModalLogros" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600">
            <span class="text-2xl">×</span>
          </button>
        </div>

        <!-- Contingut Modal (Bento Grid) -->
        <div class="p-8 overflow-y-auto bg-gray-50/50 flex-1">
          <div v-if="logroStore.loading" class="flex flex-col items-center justify-center py-20 gap-4">
            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="text-gray-500 font-medium">Carregant la teva vitrina...</p>
          </div>

          <div v-else-if="logrosFiltrats.length === 0" class="text-center py-20 text-gray-400">
            <p class="text-4xl mb-4">🏜️</p>
            <p class="font-medium text-lg">Encara no hi ha logros disponibles.</p>
            <p class="text-sm">Torna més tard per veure noves missions!</p>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="logro in logrosFiltrats" 
              :key="logro.id"
              class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all group relative overflow-hidden"
              :class="{'opacity-60': !logro.obtingut}"
            >
              <!-- Icona/Medalla -->
              <div class="flex items-start justify-between mb-4">
                <div 
                  class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shadow-inner transition-transform group-hover:scale-110"
                  :class="logro.obtingut ? 'bg-gradient-to-br from-yellow-400 to-orange-500 text-white' : 'bg-gray-100 text-gray-400'"
                >
                  {{ logro.obtingut ? '🏅' : '🔒' }}
                </div>
                <div v-if="logro.obtingut" class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">
                  Desbloquejat
                </div>
              </div>

              <!-- Info -->
              <h4 class="font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors">{{ logro.nom }}</h4>
              <p class="text-xs text-gray-500 leading-relaxed mb-4">{{ logro.descripcio }}</p>

              <!-- Progress (Simulat si ho requereix el tipus de logro) -->
              <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ logro.tipus || 'Especial' }}</span>
                <span v-if="logro.data_obtencio" class="text-[10px] text-gray-400">{{ logro.data_obtencio }}</span>
              </div>

              <!-- Efecte de fons per als desbloquejats -->
              <div v-if="logro.obtingut" class="absolute -right-4 -bottom-4 w-24 h-24 bg-yellow-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity -z-10"></div>
            </div>
          </div>
        </div>

        <!-- Peu Modal -->
        <div class="p-4 border-t border-gray-100 bg-white text-center">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-[0.2em]">Loopy Achivements System</p>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { io } from "socket.io-client";
import { useGameStore } from "~/stores/gameStore.js";
import { useLogroStore } from "~/stores/useLogroStore.js";
import bosqueImg from "~/assets/img/Bosque.png";
import mascotaImg from "~/assets/img/Mascota.png";

/**
 * Pàgina principal del joc (Home).
 * Gestiona el visualitzador del monstre i la llista d'hàbits diaris.
 */
export default {
  /**
   * Configuració de l'estat local.
   */
  data: function () {
    return {
      socket: null,
      procesantHabits: [],
      estaCarregantHabits: false,
      errorMissatge: "",
      imatgeMascota: mascotaImg,
      esObertModalLogros: false,
      estilFons: {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      }
    };
  },

  /**
   * Propietats computades.
   */
  computed: {
    gameStore: function() {
        return useGameStore();
    },
    ratxa: function () {
      return this.gameStore.ratxa;
    },
    xpTotal: function () {
      return this.gameStore.xpTotal;
    },
    habits: function () {
      return this.gameStore.habits;
    },
    logroStore: function () {
        return useLogroStore();
    },
    logrosFiltrats: function () {
        // En un futur es podria filtrar per tipus o estat de bloqueig
        return this.logroStore.logros;
    }
  },

  /**
   * Inicialització del component.
   */
  mounted: function () {
    var self = this;
    
    // A. Assignar usuari (exemple amb ID 1)
    self.gameStore.assignarUsuariId(1);

    // B. Carregar dades inicials de l'hàbit i estat
    self.estaCarregantHabits = true;
    Promise.all([
      self.gameStore.obtenirHabitos(), 
      self.gameStore.obtenirEstatJoc()
    ])
    .then(function() {
        console.log("✅ Dades carregades correctament");
    })
    .catch(function(error) {
        console.error("❌ Error carregant dades:", error);
        self.errorMissatge = "Error al carregar la informació del servidor.";
    })
    .finally(function() {
        self.estaCarregantHabits = false;
    });

    // C. Conectar Sockets
    self.inicialitzarSocket();
  },

  /**
   * Neteja abans de destruir el component.
   */
  beforeUnmount: function () {
    if (this.socket) {
      this.socket.disconnect();
    }
  },

  methods: {
    /**
     * Inicialitza la conexió de sockets.
     */
    inicialitzarSocket: function () {
      var self = this;
      var socketConfig = useSocketConfig();
      var socketUrl = socketConfig.socketUrl;

      self.socket = io(socketUrl, {
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: 5,
      });

      self.socket.on("connect", function () {
        console.log("✅ Conectat al servidor de sockets:", self.socket.id);
      });

      self.socket.on("update_xp", async function (data) {
        console.log("⭐ Recept feedback gamificació:", data);
        try {
          await self.gameStore.obtenirEstatJoc();
        } catch (error) {
          console.error("❌ Error actualitzant estat:", error);
        }
      });

      self.socket.on("disconnect", function () {
        console.log("❌ Desconectat del servidor de sockets");
      });
    },

    /**
     * Comprova si un hàbit s'està processant actualment.
     */
    comvprovarSiSestaProcessant: function (idHabit) {
      var trobat = false;
      var i;
      for (i = 0; i < this.procesantHabits.length; i++) {
        if (this.procesantHabits[i] === idHabit) {
          trobat = true;
          break;
        }
      }
      return trobat;
    },

    /**
     * Acció de completar un hàbit.
     */
    completarHabit: async function (idHabit) {
      var self = this;
      var success;

      try {
        // A. Marcar com a processant
        self.procesantHabits.push(idHabit);
        self.errorMissatge = "";

        // B. Cridar acció del store
        success = await self.gameStore.completarHabit(idHabit, self.socket);

        if (!success) {
          self.errorMissatge = "No s'ha pogut completar l'hàbit.";
        }
      } catch (error) {
        console.error("Error completant hàbit:", error);
        self.errorMissatge = "Error inesperat en completar l'hàbit.";
      } finally {
        // C. Treure de la llista de processant (clàssic)
        var novaLlista = [];
        var j;
        for (j = 0; j < self.procesantHabits.length; j++) {
            if (self.procesantHabits[j] !== idHabit) {
                novaLlista.push(self.procesantHabits[j]);
            }
        }
        self.procesantHabits = novaLlista;
      }
    },

    /**
     * Obre el modal de logros i carrega les dades des de l'API.
     */
    obrirModalLogros: function () {
        var self = this;
        self.esObertModalLogros = true;
        
        // Carregar logros (gestionat pel company)
        self.logroStore.carregarLogros()
            .then(function() {
                console.log("🏆 Logros carregats al modal");
            })
            .catch(function(err) {
                console.error("❌ Error carregant logros al modal:", err);
            });
    },

    /**
     * Tanca el modal de logros.
     */
    tancarModalLogros: function () {
        this.esObertModalLogros = false;
    }
  }
};
</script>

<style scoped>
/* Estils locals per a la pàgina d'inici */
</style>
