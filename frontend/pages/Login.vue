<template>
  <div class="min-h-screen bg-gray-50 py-12 px-8 lg:px-20 flex items-center">
    <div class="w-full">
      <div class="grid grid-cols-2 gap-12 items-center">
        <!-- Esquerra: Targeta de login -->
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
            <h2 class="text-2xl font-semibold text-gray-800">Loopy Master</h2>
            <p class="text-sm text-gray-500 text-center">
              Domina els teus hàbits, puja de nivell.
            </p>
          </div>

          <form class="mt-6 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >EMAIL</label
              >
              <input
                v-model="formulari.email"
                type="email"
                placeholder="alex.martinez@exemple.com"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >CONTRASSENYA</label
              >
              <input
                v-model="formulari.contrasenya"
                type="password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>

            <div class="pt-2 grid grid-cols-2 gap-3">
              <NuxtLink to="/admin">
                <button
                  type="button"
                  class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg"
                >
                  ADMIN
                </button>
              </NuxtLink>
              <NuxtLink to="/home">
                <button
                  type="button"
                  class="w-full bg-green-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg"
                >
                  USUARI
                </button>
              </NuxtLink>
            </div>

            <div class="pt-2">
              <NuxtLink to="/registre">
                <button
                  type="button"
                  class="w-full bg-white border border-gray-200 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50"
                >
                  REGISTRAR-SE
                </button>
              </NuxtLink>
            </div>

            <div class="text-center text-xs text-green-600 mt-3 cursor-pointer">
              Has oblidat la teva contrasenya?
            </div>
          </form>
        </div>

        <!-- Dreta: vista prèvia del dashboard -->
        <div class="grid grid-rows-3 gap-6 h-full">
          <!-- Fila superior: targeta avatar + progrés -->
          <div class="grid grid-cols-3 gap-6 items-start">
            <div class="col-span-1"></div>
            <div
              class="col-span-1 bg-white rounded-xl p-4 shadow-sm flex flex-col items-center"
            >
              <div class="text-sm font-medium mt-3">El teu Company</div>
              <div class="text-xs text-gray-400">Nivell 5 · Guardià</div>
            </div>
            <div
              class="col-span-1 bg-white rounded-xl p-3 shadow-sm flex flex-col justify-between"
            >
              <div class="text-xs text-gray-400">Progrés Diari</div>
              <div
                class="w-full bg-gray-100 rounded-full h-3 mt-3 overflow-hidden"
              >
                <div
                  class="bg-green-500 h-3 rounded-full"
                  :style="{ width: percentatgeProgres + '%' }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Fila central: targeta central mascota -->
          <div class="flex items-center justify-center">
            <div
              class="bg-white rounded-xl p-8 shadow-md w-full max-w-xl flex items-center gap-6"
            >
              <div
                class="w-24 h-24 rounded-xl bg-amber-50 flex items-center justify-center"
              >
                <!-- Imatge o icona de la mascota -->
              </div>
              <div>
                <div class="text-lg font-semibold uppercase">ElTeuMonstre</div>
                <div class="text-sm text-gray-400">
                  Nivell 5 · Guardià dels Boscos
                </div>
              </div>
              <div class="ml-auto text-sm text-gray-500">⭐ 42</div>
            </div>
          </div>

          <!-- Fila inferior: dues targetes de colors -->
          <div class="grid grid-cols-3 gap-6 items-end">
            <div
              class="col-span-1 bg-blue-50 rounded-xl p-6 shadow-sm flex items-center justify-center"
            >
              <div class="text-center">
                <div class="text-sm text-gray-500">Beure Aigua</div>
                <div class="text-xs text-gray-400">2 / 4 · Salut</div>
              </div>
            </div>
            <div class="col-span-1"></div>
            <div
              class="col-span-1 bg-yellow-50 rounded-xl p-6 shadow-sm flex items-center justify-center"
            >
              <div class="text-center">
                <div class="text-sm text-gray-700">Diari</div>
                <div class="text-xs text-gray-400">
                  Completat · Creativitat
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * Configuració de la pàgina de Login.
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
definePageMeta({ layout: false });

export default {
  /**
   * Retorna les dades inicials del component.
   */
  data: function () {
    return {
      formulari: {
        email: "",
        contrasenya: ""
      },
      percentatgeProgres: 60
    };
  },

  methods: {
    // Les accions de navegació s'han mogut directament al template amb NuxtLink
  },
};
</script>

<style scoped>
/* Ajustos per simular les ombres i l'espaiat */
.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>
