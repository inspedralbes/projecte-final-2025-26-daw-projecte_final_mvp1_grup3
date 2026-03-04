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
              {{ $t('join_loopy') }}
            </h2>
            <p class="text-sm text-gray-500 text-center">
              {{ $t('create_account') }}
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
                >{{ $t('name') }}</label
              >
              <input
                v-model="formulari.nom"
                type="text"
                :placeholder="$t('name_placeholder')"
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
                {{ $t('register_button') }}
              </button>
            </div>

            <div class="pt-2">
              <NuxtLink to="/login">
                <button
                  type="button"
                  class="w-full bg-white border border-gray-200 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50"
                >
                  {{ $t('back_to_login') }}
                </button>
              </NuxtLink>
            </div>
          </form>
        </div>

        <!-- Dreta: Test de preguntes -->
        <div class="grid grid-cols-2 gap-4 h-full">
          <!-- Pas 1: Introducció al Quiz Dinàmic -->
          <template v-if="!quizIniciat && !quizFinalitzat">
            <div
              class="col-span-2 bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-8 shadow-sm flex flex-col items-center justify-center text-center"
            >
              <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                <span class="text-3xl">✨</span>
              </div>
              <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $t('quiz.discover_path') }}</h3>
              <p class="text-sm text-gray-600 mb-6">
                {{ $t('quiz.quiz_description') }}
              </p>
              <button
                @click="iniciarOnboarding"
                class="bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors shadow-lg"
              >
                {{ $t('quiz.start_test') }}
              </button>
            </div>
          </template>

          <!-- Pas 2: Preguntes Seqüencials -->
          <template v-else-if="quizIniciat && !quizFinalitzat">
            <div
              class="col-span-2 bg-white rounded-2xl p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center min-h-[300px]"
            >
              <div class="text-xs font-medium text-gray-700">
                {{ $t('quiz.physical_activity') }}
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-green-50"
              @click="seleccionarCategoria('nutrition')"
            >
              <div class="text-xs font-medium text-gray-700">{{ $t('quiz.nutrition') }}</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-yellow-50"
              @click="seleccionarCategoria('study')"
            >
              <div class="text-xs font-medium text-gray-700">{{ $t('quiz.study') }}</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-purple-50"
              @click="seleccionarCategoria('reading')"
            >
              <div class="text-xs font-medium text-gray-700">{{ $t('quiz.reading') }}</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-pink-50"
              @click="seleccionarCategoria('wellness')"
            >
              <div class="text-xs font-medium text-gray-700">{{ $t('quiz.wellness') }}</div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-red-50"
              @click="seleccionarCategoria('smoking')"
            >
              <div class="text-xs font-medium text-gray-700">
                {{ $t('quiz.smoke_free') }}
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-indigo-50"
              @click="seleccionarCategoria('cleaning')"
            >
              <div class="text-xs font-medium text-gray-700">
                {{ $t('quiz.express_cleaning') }}
              </div>
            </div>
            <div
              class="bg-white rounded-xl p-2 shadow-sm flex items-center justify-center cursor-pointer hover:bg-gray-50"
              @click="seleccionarCategoria('hobby')"
            >
              <div class="text-xs font-medium text-gray-700">{{ $t('quiz.hobby') }}</div>
            </div>
          </template>

          <!-- Pas 3: Resultat -->
          <template v-else-if="quizFinalitzat">
            <div
              class="col-span-2 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-8 shadow-sm flex flex-col items-center justify-center text-center"
            >
              <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                <span class="text-3xl">🎯</span>
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
                    {{ $t('quiz.yes') }}
                  </button>
                  <button
                    type="button"
                    @click="respondre(pregunta.id, 'no')"
                    :class="obtenirClasseBoto(pregunta.id, 'no')"
                  >
                    {{ $t('quiz.no') }}
                  </button>
                </template>
              </div>

              <p class="text-xs text-gray-500 mb-6 px-4">
                {{ $t('quiz.quiz_result_footnote') }}
              </p>
              <button
                @click="quizFinalitzat = false; quizIniciat = false; indexPregunta = 0"
                class="text-indigo-600 font-bold text-xs hover:underline"
              >
                {{ $t('quiz.repeat_test') }}
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ layout: false });
</script>

<script>
/**
 * Pàgina de Registre amb Onboarding interactiu.
 * Refactoritzada per utilitzar l'API d'opcions correctament.
 */

export default {
  /**
   * Retorna les dades inicials del component.
   */
  data: function () {
    return {
      quizIniciat: false,
      quizFinalitzat: false,
      indexPregunta: 0,
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

  computed: {
    /**
     * Retorna la pregunta actual segons l'índex.
     */
    pregunta: function () {
      if (this.llistaPreguntes && this.llistaPreguntes.length > 0) {
        return this.llistaPreguntes[this.indexPregunta];
      }
      return { id: 0, pregunta: "" };
    },
  },

  methods: {
    /**
     * Inicia el flux d'onboarding carregant les preguntes dinàmiques.
     */
    iniciarOnboarding: async function () {
      var self = this;
      self.estaCarregant = true;
      try {
        var base = (self.$config.public.apiUrl || "").replace(/\/$/, "");
        var resposta = await fetch(base + "/api/onboarding/questions");
        var dades = await resposta.json();

        if (dades && dades.success) {
          self.llistaPreguntes = dades.preguntes;
          self.quizIniciat = true;
          self.indexPregunta = 0;
        }
      } catch (err) {
        console.error("Error en iniciar onboarding:", err);
      } finally {
        self.estaCarregant = false;
      }
    },

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
     * Desa la resposta d'una pregunta específica i avança.
     */
    respondre: function (preguntaId, resposta) {
      this.respostes[preguntaId] = resposta;
      if (this.indexPregunta < this.llistaPreguntes.length - 1) {
        this.indexPregunta++;
      } else {
        this.quizFinalitzat = true;
      }
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
      alert(this.$t("quiz_finished_alert"));

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
            title: this.$t("account_created_title"),
            text: this.$t("account_created_text"),
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
      var missatge = this.$t("error_registration_generic");
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
        self.errorMissatge = this.$t("error_empty_fields");
        return;
      }

      if (self.formulari.contrasenya !== self.formulari.confirmacio) {
        self.errorMissatge = this.$t("error_password_mismatch");
        return;
      }

      if (self.formulari.contrasenya.length < 6) {
        self.errorMissatge = this.$t("error_password_short");
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
          self.errorMissatge = this.$t("error_connection");
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
