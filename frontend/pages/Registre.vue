<template>
  <div class="login-container">
    <!-- Componente superior derecho para el selector de idioma (absoluto) -->
    <div class="login-lang-switch">
      <LanguageSwitcher />
    </div>

    <!-- ===== COLUMNA IZQUIERDA (Formulario de Registro) ===== -->
    <div class="login-left-col">
      <!-- Sección Título y Logo -->
      <div class="login-header">
        <!-- Logo -->
        <div class="login-logo">
          <span class="login-logo-text">Loopy</span>
          <img src="@/assets/img/LogoLoopy.png" alt="Loopy Logo" class="login-logo-image" />
        </div>

        <h1 class="login-title">{{ $t('join_loopy') }}</h1>
        <p class="login-subtitle">{{ $t('create_account') }}</p>
      </div>

      <!-- Formulario -->
      <form class="login-form mt-6 space-y-4" @submit.prevent>
        <div
          v-if="errorMissatge"
          class="login-error-msg"
        >
          {{ errorMissatge }}
        </div>
        
        <!-- Input Nombre -->
        <div>
          <input
            v-model="formulari.nom"
            type="text"
            :placeholder="$t('name_placeholder')"
            class="login-input"
          />
        </div>

        <!-- Input Correo -->
        <div>
          <input
            v-model="formulari.email"
            type="email"
            placeholder="el.teu.email@exemple.com"
            class="login-input"
          />
        </div>

        <!-- Input Contraseña -->
        <div>
          <input
            v-model="formulari.contrasenya"
            type="password"
            placeholder="••••••••"
            class="login-input"
          />
        </div>

        <!-- Input Confirmar Contraseña -->
        <div>
          <input
            v-model="formulari.confirmacio"
            type="password"
            placeholder="••••••••"
            class="login-input"
          />
        </div>

        <!-- Botón Entrar -->
        <div class="pt-4">
          <button
            type="button"
            :disabled="estaCarregant"
            @click="registrarUsuari"
            class="login-btn-primary"
          >
            {{ $t('register_button') }}
          </button>
        </div>

        <!-- Divisor O continua amb -->
        <div class="login-divider">
          <span class="login-divider-text">Ja tens un compte?</span>
        </div>

        <!-- Botón Volver a Login -->
        <div>
          <NuxtLink to="/login" class="block w-full">
            <button
              type="button"
              class="login-btn-outline"
            >
              {{ $t('back_to_login') }}
            </button>
          </NuxtLink>
        </div>
      </form>
    </div>

    <!-- ===== COLUMNA DERECHA (Quiz Dinàmic / Features) ===== -->
    <div class="login-right-col">
      
      <!-- Fondo / Patrón Decorativo sutil estilo wireframe/hexagonos -->
      <div class="login-bg-pattern"></div>
      
      <!-- Contenedor general del Bento/Quiz -->
      <div class="login-bento-container" style="max-width: 48rem;">
        
        <!-- Pas 1: Introducció al Quiz Dinàmic -->
        <template v-if="!quizIniciat && !quizFinalitzat">
          <div class="bento-banner p-12 overflow-visible rounded-[2.5rem] mt-0 flex-col items-center justify-center text-center shadow-[0_20px_50px_rgba(72,107,46,0.4)] relative">
            
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-6 shadow-xl border border-white/30 z-10 relative">
              <span class="text-4xl drop-shadow-md">✨</span>
            </div>
            
            <div class="relative z-10 w-full max-w-md mx-auto pointer-events-auto">
              <h3 class="text-3xl font-extrabold text-white mb-4 tracking-wide drop-shadow-md">{{ $t('quiz.discover_path') }}</h3>
              <p class="text-white/90 font-medium mb-10 text-lg leading-relaxed drop-shadow-sm">
                {{ $t('quiz.quiz_description') }}
              </p>
              <button
                @click="iniciarOnboarding"
                class="bg-white text-[#3a5826] px-10 py-4 rounded-2xl font-black text-lg hover:bg-green-50 hover:scale-105 active:scale-95 transition-all shadow-[0_8px_20px_rgba(0,0,0,0.15)] uppercase tracking-wider relative z-20 cursor-pointer"
              >
                {{ $t('quiz.start_test') }}
              </button>
            </div>
            
            <!-- Decorativos -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-[#aed581]/20 rounded-full blur-3xl"></div>
          </div>
        </template>

        <!-- Pas 2: Preguntes Seqüencials -->
        <template v-else-if="quizIniciat && !quizFinalitzat">
            <div class="bg-white/90 backdrop-blur-md rounded-3xl p-10 shadow-2xl border border-white/50 w-full">
              
              <!-- Título / Header del Quiz -->
              <div class="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                  🎯
                </div>
                <div>
                  <h3 class="text-2xl font-bold text-gray-800 tracking-tight">Selecciona una categoría</h3>
                  <p class="text-sm font-medium text-gray-500 mt-1">Descubre tu camino en Loopy</p>
                </div>
              </div>

              <!-- Grid de Categorías -->
              <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                  <!-- Cat 1 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-green-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('nutrition')"
                  >
                     <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">🥗</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-green-600 transition-colors">{{ $t('quiz.nutrition') }}</h4>
                  </div>
                  <!-- Cat 2 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-blue-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('study')"
                  >
                      <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">📚</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-blue-600 transition-colors">{{ $t('quiz.study') }}</h4>
                  </div>
                  <!-- Cat 3 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-purple-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('reading')"
                  >
                      <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">📖</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-purple-600 transition-colors">{{ $t('quiz.reading') }}</h4>
                  </div>
                  <!-- Cat 4 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-pink-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('wellness')"
                  >
                     <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">🧘‍♀️</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-pink-600 transition-colors">{{ $t('quiz.wellness') }}</h4>
                  </div>
                  <!-- Cat 5 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-red-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('smoking')"
                  >
                     <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">🚭</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-red-600 transition-colors">{{ $t('quiz.smoke_free') }}</h4>
                  </div>
                  <!-- Cat 6 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-teal-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('cleaning')"
                  >
                     <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">🧹</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-teal-600 transition-colors">{{ $t('quiz.express_cleaning') }}</h4>
                  </div>
                  <!-- Cat 7 -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-orange-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('hobby')"
                  >
                      <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">🎨</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-orange-600 transition-colors">{{ $t('quiz.hobby') }}</h4>
                  </div>
                  <!-- Cat 8 (Physical) -->
                  <div
                  class="bento-card hover:-translate-y-2 cursor-pointer shadow-md border-b-4 border-transparent hover:border-yellow-400 group p-5 items-center text-center h-auto min-h-[120px]"
                  @click="seleccionarCategoria('gym')"
                  >
                      <div class="text-3xl mb-3 group-hover:scale-110 transition-transform">🏃‍♂️</div>
                     <h4 class="font-bold text-gray-700 group-hover:text-yellow-600 transition-colors">{{ $t('quiz.physical_activity') }}</h4>
                  </div>
              </div>
            </div>
        </template>

        <!-- Pas 3: Resultat -->
        <template v-else-if="quizFinalitzat">
          <div class="bg-white/95 backdrop-blur-md rounded-3xl p-12 shadow-2xl border border-white/50 w-full flex flex-col items-center justify-center text-center">
            
            <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-4xl shadow-inner mb-6">
              🧠
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-6 max-w-lg leading-tight">
              {{ pregunta.pregunta }}
            </h3>
            
            <div class="flex gap-4 mb-10 w-full max-w-sm justify-center">
              <!-- Botons de resposta condicionals -->
              <template v-if="pregunta.pregunta.indexOf('força o massa muscular') !== -1">
                <button type="button" @click="respondre(pregunta.id, 'forza')" :class="[obtenirClasseBoto(pregunta.id, 'forza'), 'flex-1 py-4 text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95']">
                  Força
                </button>
                <button type="button" @click="respondre(pregunta.id, 'massa_muscular')" :class="[obtenirClasseBoto(pregunta.id, 'massa_muscular'), 'flex-1 py-4 text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95']">
                  Massa Muscular
                </button>
              </template>
              <template v-else-if="pregunta.pregunta.indexOf('ansietat o per compromís social') !== -1">
                <button type="button" @click="respondre(pregunta.id, 'ansietat')" :class="[obtenirClasseBoto(pregunta.id, 'ansietat'), 'flex-1 py-4 text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95']">
                  Per Ansietat
                </button>
                <button type="button" @click="respondre(pregunta.id, 'compromis')" :class="[obtenirClasseBoto(pregunta.id, 'compromis'), 'flex-1 py-4 text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95']">
                  Per Compromís
                </button>
              </template>
              <template v-else>
                <button type="button" @click="respondre(pregunta.id, 'si')" :class="[obtenirClasseBoto(pregunta.id, 'si'), 'flex-1 py-4 px-8 text-base font-bold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95']">
                  {{ $t('quiz.yes') }}
                </button>
                <button type="button" @click="respondre(pregunta.id, 'no')" :class="[obtenirClasseBoto(pregunta.id, 'no'), 'flex-1 py-4 px-8 text-base font-bold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95']">
                  {{ $t('quiz.no') }}
                </button>
              </template>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 w-full mb-6">
              <p class="text-sm font-medium text-gray-600">
                {{ $t('quiz.quiz_result_footnote') }}
              </p>
            </div>
            
            <button
              @click="quizFinalitzat = false; quizIniciat = false; indexPregunta = 0"
              class="text-indigo-600 font-bold text-sm hover:text-indigo-800 transition-colors flex items-center gap-2"
            >
              <span>↺</span> {{ $t('quiz.repeat_test') }}
            </button>
          </div>
        </template>
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
        authStore.aplicarSessio({
          token: dades.token,
          user: dades.user,
          role: "user"
        });
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
