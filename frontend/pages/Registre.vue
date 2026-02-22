<template>
  <div class="min-h-screen bg-gray-50 py-12 px-8 lg:px-20 flex items-center">
    <div class="w-full">
      <div
        class="grid grid-cols-2 gap-12 items-stretch px-8 py-8 bg-white rounded-2xl shadow-sm"
      >
        <!-- Esquerra: Targeta de registre -->
        <div class="bg-white rounded-xl p-8 shadow-md" style="max-width: 420px">
          <div class="flex flex-col items-center gap-4">
            <div
              class="w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center"
            >
              <div
                class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold"
              >
                ∞
              </div>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">
              Uneix-te a Loopy
            </h2>
            <p class="text-sm text-gray-500 text-center">
              Crea el teu compte i comença el teu viatge.
            </p>
          </div>

          <form class="mt-6 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >NOM</label
              >
              <input
                type="text"
                placeholder="El teu nom"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >EMAIL</label
              >
              <input
                type="email"
                placeholder="el.teu.email@exemple.com"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >CONTRASSENYA</label
              >
              <input
                type="password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >CONFIRMAR CONTRASSENYA</label
              >
              <input
                type="password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>

            <div class="pt-2">
              <button
                type="button"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg"
              >
                REGISTRAR-SE
              </button>
            </div>

            <div class="pt-2">
              <NuxtLink to="/login">
                <button
                  type="button"
                  class="w-full bg-white border border-gray-200 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50"
                >
                  TORNAR AL LOGIN
                </button>
              </NuxtLink>
            </div>
          </form>
        </div>

        <!-- Dreta: Test de preguntes -->
        <div class="grid grid-cols-2 gap-4 h-full">
          <!-- Pas 1: Pregunta Maestra -->
          <template v-if="categoriaSeleccionada === null">
            <div
              class="col-span-2 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 shadow-sm flex flex-col items-center justify-center"
            >
              <div class="text-xs font-bold text-gray-600 text-center">
                En quina àrea et vols centrar?
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-blue-50"
              @click="seleccionarCategoria('gym')"
            >
              <div class="text-xs font-medium text-gray-700">
                💪 Activitat física
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-green-50"
              @click="seleccionarCategoria('nutrition')"
            >
              <div class="text-xs font-medium text-gray-700">
                🥗 Alimentació
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-yellow-50"
              @click="seleccionarCategoria('study')"
            >
              <div class="text-xs font-medium text-gray-700">📚 Estudi</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-purple-50"
              @click="seleccionarCategoria('reading')"
            >
              <div class="text-xs font-medium text-gray-700">📖 Lectura</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-pink-50"
              @click="seleccionarCategoria('wellness')"
            >
              <div class="text-xs font-medium text-gray-700">🧘 Benestar</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-red-50"
              @click="seleccionarCategoria('smoking')"
            >
              <div class="text-xs font-medium text-gray-700">
                🚭 Vida sense Fum
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-indigo-50"
              @click="seleccionarCategoria('cleaning')"
            >
              <div class="text-xs font-medium text-gray-700">
                🏠 Neteja Express
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-gray-50"
              @click="seleccionarCategoria('hobby')"
            >
              <div class="text-xs font-medium text-gray-700">🎨 Modelisme</div>
            </div>
          </template>

          <!-- Pas 2: Preguntes de Profundització -->
          <template v-else>
            <div
              v-for="(pregunta, index) in obtenirPreguntesActuals()"
              :key="pregunta.id"
              class="bg-white rounded-xl p-3 shadow-sm flex flex-col items-center justify-center overflow-hidden"
            >
              <div class="text-xs font-bold text-gray-600 text-center mb-2">
                Pregunta {{ index + 1 }}/{{ obtenirPreguntesActuals().length }}
              </div>
              <div class="text-xs text-gray-700 text-center leading-tight mb-3">
                {{ pregunta.pregunta }}
              </div>
              <div class="flex gap-2">
                <!-- Botons de resposta condicionals -->
                <template
                  v-if="
                    pregunta.pregunta.indexOf('força o massa muscular') !== -1
                  "
                >
                  <button
                    @click="respondre(pregunta.id, 'forza')"
                    :class="[
                      'px-3 py-1 text-xs rounded-md',
                      respostes[pregunta.id] === 'forza'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200',
                    ]"
                  >
                    Força
                  </button>
                  <button
                    @click="respondre(pregunta.id, 'massa_muscular')"
                    :class="[
                      'px-3 py-1 text-xs rounded-md',
                      respostes[pregunta.id] === 'massa_muscular'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200',
                    ]"
                  >
                    Massa Muscular
                  </button>
                </template>
                <template
                  v-else-if="
                    pregunta.pregunta.indexOf(
                      'ansietat o per compromís social',
                    ) !== -1
                  "
                >
                  <button
                    @click="respondre(pregunta.id, 'ansietat')"
                    :class="[
                      'px-3 py-1 text-xs rounded-md',
                      respostes[pregunta.id] === 'ansietat'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200',
                    ]"
                  >
                    Per Ansietat
                  </button>
                  <button
                    @click="respondre(pregunta.id, 'compromis')"
                    :class="[
                      'px-3 py-1 text-xs rounded-md',
                      respostes[pregunta.id] === 'compromis'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200',
                    ]"
                  >
                    Per Compromís
                  </button>
                </template>
                <template v-else>
                  <button
                    @click="respondre(pregunta.id, 'si')"
                    :class="[
                      'px-3 py-1 text-xs rounded-md',
                      respostes[pregunta.id] === 'si'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200',
                    ]"
                  >
                    Sí
                  </button>
                  <button
                    @click="respondre(pregunta.id, 'no')"
                    :class="[
                      'px-3 py-1 text-xs rounded-md',
                      respostes[pregunta.id] === 'no'
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200',
                    ]"
                  >
                    No
                  </button>
                </template>
              </div>
            </div>
            <div class="col-span-full mt-2 grid grid-cols-2 gap-2">
              <button
                @click="seleccionarCategoria(null)"
                class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 rounded-lg text-xs"
              >
                TORNAR
              </button>
              <button
                @click="finalitzarTest"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg text-xs"
              >
                FINALITZAR
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Configuració de la pàgina de Registre.
 */
definePageMeta({ layout: false });

export default {
  /**
   * Retorna les dades inicials del component.
   */
  data: function () {
    return {
      categoriaSeleccionada: null,
      preguntes: [],
      respostes: {},
      mapaCategories: {
        gym: 1,
        nutrition: 2,
        study: 3,
        reading: 4,
        wellness: 5,
        smoking: 6,
        cleaning: 7,
        hobby: 8,
      },
    };
  },

  methods: {
    /**
     * Selecciona una categoria i carrega les preguntes des de l'API.
     * @param {string} categoria - El nom de la categoria seleccionada.
     */
    seleccionarCategoria: async function (categoria) {
      var self = this;
      var idCategoria;
      var resultat;
      var dades;

      // A. Assignar la categoria
      self.categoriaSeleccionada = categoria;

      if (categoria) {
        // B. Obtenir l'ID corresponent
        idCategoria = self.mapaCategories[categoria];

        try {
          // C. Cridar a l'API per obtenir les preguntes
          resultat = await useFetch(
            "http://localhost:8000/api/preguntes-registre/" + idCategoria,
          );
          dades = resultat.data.value;

          if (dades && dades.preguntes) {
            self.preguntes = dades.preguntes;
          }
        } catch (error) {
          console.error("Error al carregar les preguntes:", error);
        }
      } else {
        // D. Netejar si es deselecciona
        self.preguntes = [];
        self.respostes = {};
      }
    },

    /**
     * Retorna el llistat de preguntes actuals.
     */
    obtenirPreguntesActuals: function () {
      return this.preguntes;
    },

    /**
     * Desa la resposta d'una pregunta específica.
     * @param {number} preguntaId - L'ID de la pregunta.
     * @param {string} resposta - La resposta triada.
     */
    respondre: function (preguntaId, resposta) {
      this.respostes[preguntaId] = resposta;
    },

    /**
     * Finalitza el test i processa les respostes.
     */
    finalitzarTest: function () {
      var self = this;
      var respostesText;

      // A. Mostrar respostes per consola (simulació)
      respostesText = JSON.stringify(self.respostes);
      console.log("Respostes a enviar:", respostesText);

      // B. Alerta de finalització
      alert(
        "Test finalitzat! Revisa la consola per veure les teves respostes.",
      );

      // C. Reset de l'estat
      self.categoriaSeleccionada = null;
      self.preguntes = [];
      self.respostes = {};
    },
  },
};
</script>

<style scoped>
/* Pequeños ajustes para simular las sombras y el espaciado del diseño */
.shadow-inner {
  box-shadow: 0 8px 30px rgba(16, 24, 40, 0.06);
}

/* Estils per als botons de resposta seleccionats */
.bg-blue-500 {
  background-color: #3b82f6;
}
.text-white {
  color: #ffffff;
}
.bg-gray-200 {
  background-color: #e5e7eb;
}
</style>
