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

          <form class="mt-6 space-y-4" @submit.prevent>
            <div
              v-if="errorMissatge"
              class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm"
            >
              {{ errorMissatge }}
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >NOM</label
              >
              <input
                v-model="formulari.nom"
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
                v-model="formulari.email"
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
                v-model="formulari.contrasenya"
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
                v-model="formulari.confirmacio"
                type="password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>

            <div class="pt-2">
              <button
                type="button"
                :disabled="estaCarregant"
                @click="registrarUsuari"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg disabled:opacity-50"
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
                Activitat física
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-green-50"
              @click="seleccionarCategoria('nutrition')"
            >
              <div class="text-xs font-medium text-gray-700">Alimentació</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-yellow-50"
              @click="seleccionarCategoria('study')"
            >
              <div class="text-xs font-medium text-gray-700">Estudi</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-purple-50"
              @click="seleccionarCategoria('reading')"
            >
              <div class="text-xs font-medium text-gray-700">Lectura</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-pink-50"
              @click="seleccionarCategoria('wellness')"
            >
              <div class="text-xs font-medium text-gray-700">Benestar</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-red-50"
              @click="seleccionarCategoria('smoking')"
            >
              <div class="text-xs font-medium text-gray-700">
                Vida sense Fum
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-indigo-50"
              @click="seleccionarCategoria('cleaning')"
            >
              <div class="text-xs font-medium text-gray-700">
                Neteja Express
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-gray-50"
              @click="seleccionarCategoria('hobby')"
            >
              <div class="text-xs font-medium text-gray-700">Modelisme</div>
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
                Pregunta {{ index + 1 }}/{{ llistaPreguntes.length }}
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
                    type="button"
                    @click="respondre(pregunta.id, 'forza')"
                    :class="obtenirClasseBoto(pregunta.id, 'forza')"
                  >
                    Força
                  </button>
                  <button
                    type="button"
                    @click="respondre(pregunta.id, 'massa_muscular')"
                    :class="obtenirClasseBoto(pregunta.id, 'massa_muscular')"
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
                    type="button"
                    @click="respondre(pregunta.id, 'ansietat')"
                    :class="obtenirClasseBoto(pregunta.id, 'ansietat')"
                  >
                    Per Ansietat
                  </button>
                  <button
                    type="button"
                    @click="respondre(pregunta.id, 'compromis')"
                    :class="obtenirClasseBoto(pregunta.id, 'compromis')"
                  >
                    Per Compromís
                  </button>
                </template>
                <template v-else>
                  <button
                    type="button"
                    @click="respondre(pregunta.id, 'si')"
                    :class="obtenirClasseBoto(pregunta.id, 'si')"
                  >
                    Sí
                  </button>
                  <button
                    type="button"
                    @click="respondre(pregunta.id, 'no')"
                    :class="obtenirClasseBoto(pregunta.id, 'no')"
                  >
                    No
                  </button>
                </template>
              </div>
            </div>
            <div class="col-span-full mt-2 grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="seleccionarCategoria(null)"
                class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 rounded-lg text-xs"
              >
                TORNAR
              </button>
              <button
                type="button"
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
 * Segueix les normes de l'Agent Javascript (ES5 Estricte).
 */
definePageMeta({ layout: false });

export default {
  /**
   * Retorna les dades inicials del component.
   */
  data: function () {
    return {
      categoriaSeleccionada: null,
      llistaPreguntes: [],
      respostes: {},
      errorMissatge: "",
      estaCarregant: false,
      formulari: {
        nom: "",
        email: "",
        contrasenya: "",
        confirmacio: "",
      },
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
     * Selecciona una categoria i carrega les preguntes des de l'API Laravel.
     */
    seleccionarCategoria: async function (categoria) {
      var self = this;
      var idCategoria;
      var base;
      var resposta;
      var dades;

      // A. Assignar la categoria
      self.categoriaSeleccionada = categoria;

      if (categoria) {
        // B. Obtenir l'ID corresponent
        idCategoria = self.mapaCategories[categoria];

        try {
          base = self.$config.public.apiUrl;
          if (base.endsWith("/")) {
            base = base.slice(0, -1);
          }

          // C. Cridar a l'API via fetch (GET)
          resposta = await fetch(
            base + "/api/preguntes-registre/" + idCategoria,
          );
          dades = await resposta.json();

          if (dades && dades.preguntes) {
            self.llistaPreguntes = dades.preguntes;
          }
        } catch (error) {
          console.error("Error al carregar les preguntes:", error);
        }
      } else {
        // D. Netejar si es deselecciona
        self.llistaPreguntes = [];
        self.respostes = {};
      }
    },

    /**
     * Retorna el llistat de preguntes carregades.
     */
    obtenirPreguntesActuals: function () {
      return this.llistaPreguntes;
    },

    /**
     * Desa la resposta d'una pregunta específica.
     */
    respondre: function (preguntaId, resposta) {
      this.respostes[preguntaId] = resposta;
    },

    /**
     * Finalitza el test i processa les respostes.
     */
    finalitzarTest: function () {
      var self = this;
      var textRespostes;

      // A. Mostrar respostes per consola
      textRespostes = JSON.stringify(self.respostes);
      console.log("Respostes a enviar:", textRespostes);

      // B. Alerta de finalització
      alert(
        "Test finalitzat! Revisa la consola per veure les teves respostes.",
      );

      // C. Reset de l'estat local
      self.categoriaSeleccionada = null;
      self.llistaPreguntes = [];
      self.respostes = {};
    },

    /**
     * Mostra SweetAlert confirmant que el compte s'ha creat correctament.
     * En clicar OK, redirigeix a HomePage i actualitza el socket.
     */
    mostrarAlertaCompteCreat: function () {
      var self = this;
      var mostrarAlerta = function () {
        if (typeof window !== "undefined" && window.Swal) {
          window.Swal.fire({
            title: "Compte creat correctament",
            text: "Benvingut a Loopy! El teu compte s'ha creat amb èxit.",
            icon: "success",
            confirmButtonText: "OK",
          }).then(function (result) {
            if (result.isConfirmed) {
              var nuxtApp = useNuxtApp();
              if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
              navigateTo("/home");
            }
          });
        } else {
          navigateTo("/home");
        }
      };

      if (typeof window !== "undefined" && window.Swal) {
        mostrarAlerta();
      } else if (typeof document !== "undefined") {
        var script = document.createElement("script");
        script.src =
          "https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js";
        script.onload = mostrarAlerta;
        document.head.appendChild(script);
      } else {
        navigateTo("/home");
      }
    },

    /**
     * Extreu el missatge d'error de la resposta de l'API.
     * Evita Object.values, flat i ternaris (AgentJavascript).
     */
    extraureMissatgeError: function (dades) {
      var missatge = "Error en el registre";
      if (dades.message) {
        return String(dades.message);
      }
      if (dades.errors && typeof dades.errors === "object") {
        var parts = [];
        var claus = Object.keys(dades.errors);
        for (var i = 0; i < claus.length; i++) {
          var val = dades.errors[claus[i]];
          if (Array.isArray(val)) {
            for (var j = 0; j < val.length; j++) {
              parts.push(val[j]);
            }
          } else {
            parts.push(String(val));
          }
        }
        if (parts.length > 0) {
          return parts.join(" ");
        }
      }
      return missatge;
    },

    /**
     * Retorna la classe CSS del botó de resposta segons si està seleccionat.
     * Evita ternaris al template (AgentJavascript).
     */
    obtenirClasseBoto: function (preguntaId, valor) {
      if (this.respostes[preguntaId] === valor) {
        return "px-3 py-1 text-xs rounded-md bg-blue-500 text-white";
      }
      return "px-3 py-1 text-xs rounded-md bg-gray-200";
    },

    /**
     * Acció per registrar un usuari. POST /api/auth/register
     */
    registrarUsuari: async function () {
      var self = this;

      if (
        !self.formulari.nom ||
        !self.formulari.email ||
        !self.formulari.contrasenya
      ) {
        self.errorMissatge = "Si us plau, omple tots els camps.";
        return;
      }

      if (self.formulari.contrasenya !== self.formulari.confirmacio) {
        self.errorMissatge = "Les contrasenyes no coincideixen.";
        return;
      }

      if (self.formulari.contrasenya.length < 6) {
        self.errorMissatge = "La contrasenya ha de tenir almenys 6 caràcters.";
        return;
      }

      self.errorMissatge = "";
      self.estaCarregant = true;

      try {
        var config = useRuntimeConfig();
        var base = (config.public.apiUrl || "").replace(/\/$/, "");
        var resposta = await fetch(base + "/api/auth/register", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            nom: self.formulari.nom,
            email: self.formulari.email,
            contrasenya: self.formulari.contrasenya,
            contrasenya_confirmation: self.formulari.confirmacio,
          }),
        });
        var dades = await resposta.json();

        if (!resposta.ok) {
          self.errorMissatge = self.extraureMissatgeError(dades);
          return;
        }

        var authStore = useAuthStore();
        authStore.token = dades.token;
        authStore.user = dades.user;
        authStore.admin = null;
        authStore.role = "user";
        authStore.isAuthenticated = true;
        if (typeof localStorage !== "undefined") {
          localStorage.setItem("loopy_token", dades.token);
          localStorage.setItem("loopy_user", JSON.stringify(dades.user));
          localStorage.removeItem("loopy_admin");
          localStorage.setItem("loopy_role", "user");
        }
        self.mostrarAlertaCompteCreat();
      } catch (err) {
        if (err.message) {
          self.errorMissatge = err.message;
        } else {
          self.errorMissatge = "Error de connexió";
        }
      } finally {
        self.estaCarregant = false;
      }
    },
  },
};
</script>

<style scoped>
/* Estils locals del registre */
</style>
