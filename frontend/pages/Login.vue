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

          <form class="mt-6 space-y-4" @submit.prevent>
            <div v-if="errorMissatge" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
              {{ errorMissatge }}
            </div>
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

            <div class="pt-2">
              <button
                type="button"
                :disabled="estaCarregant"
                @click="ferLogin"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg disabled:opacity-50"
              >
                LOGIN
              </button>
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

          <div class="flex items-center justify-center">
            <div
              class="bg-white rounded-xl p-8 shadow-md w-full max-w-xl flex items-center gap-6"
            >
              <div
                class="w-24 h-24 rounded-xl bg-amber-50 flex items-center justify-center"
              ></div>
              <div>
                <div class="text-lg font-semibold uppercase">ElTeuMonstre</div>
                <div class="text-sm text-gray-400">
                  Nivell 5 · Guardià dels Boscos
                </div>
              </div>
              <div class="ml-auto text-sm text-gray-500">⭐ 42</div>
            </div>
          </div>

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
definePageMeta({ layout: false });

export default {
  data: function () {
    return {
      formulari: {
        email: "",
        contrasenya: ""
      },
      percentatgeProgres: 60,
      errorMissatge: "",
      estaCarregant: false
    };
  },

  methods: {
    ferLogin: async function () {
      var self = this;
      var email = (self.formulari.email || "").trim();
      var contrasenya = self.formulari.contrasenya || "";

      if (!email || !contrasenya) {
        self.errorMissatge = "Introduïu email i contrasenya.";
        return;
      }

      self.errorMissatge = "";
      self.estaCarregant = true;

      try {
        var authStore = useAuthStore();
        var nuxtApp = useNuxtApp();
        try {
          await authStore.loginUser(email, contrasenya);
          if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
          await navigateTo("/HomePage");
          return;
        } catch (errUser) {
          try {
            await authStore.loginAdmin(email, contrasenya);
            if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
            await navigateTo("/admin");
          } catch (errAdmin) {
            self.errorMissatge = errAdmin.message || "Credencials incorrectes";
          }
        }
      } finally {
        self.estaCarregant = false;
      }
    }
  }
};
</script>

<style scoped>
.shadow-md {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>
