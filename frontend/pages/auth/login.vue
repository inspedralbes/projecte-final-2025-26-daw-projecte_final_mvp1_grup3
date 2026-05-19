<!--
  Component o pagina Nuxt: login.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <Teleport to="body">
    <div
      v-if="mostraSequenciaEntrada"
      class="app-entry-sequence-root"
      aria-hidden="true"
      @contextmenu.prevent
      @keydown.capture.prevent.stop
    >
      <div v-if="faseEntrada === 'video'" class="app-entry-video-layer">
        <video
          ref="videoEntradaRef"
          class="app-entry-video-el"
          :src="videoEntradaUrl"
          tabindex="-1"
          muted
          playsinline
          autoplay
          disablepictureinpicture
          disableremoteplayback
          controlslist="nodownload noplaybackrate noremoteplayback"
          @ended="finalitzarVideoEntrada"
          @error="finalitzarVideoEntrada"
        />
      </div>
      <div v-else-if="faseEntrada === 'loopy'" class="app-entry-loopy-layer">
        <div
          class="app-entry-loopy-burst"
          :class="{ 'app-entry-loopy-burst--animate': loopyBurstAnimate }"
          @animationend="onLoopyBurstAnimationEnd"
        />
        <span class="app-entry-loopy-word">Loopy</span>
      </div>
    </div>
  </Teleport>
  <div class="global-app-container login-container login-page-auth">
    <div class="login-auth-bg-desktop" aria-hidden="true" />
    <div class="login-lang-switch">
      <LanguageSwitcher />
    </div>

    <!-- Mòbil: dues pantalles apilades (efecte scroll); escriptori: sense canvi -->
    <div class="login-left-col">
      <div
        class="auth-m-track max-lg:flex max-lg:w-full max-lg:flex-col max-lg:transition-transform max-lg:duration-[550ms] max-lg:ease-[cubic-bezier(0.32,0.72,0,1)] lg:contents"
        :class="{ 'max-lg:-translate-y-1/2': mobilMostraRegistre }"
      >
        <div class="auth-m-panel-login max-lg:relative max-lg:flex max-lg:min-h-[100dvh] max-lg:w-full max-lg:flex-shrink-0 max-lg:flex-col max-lg:justify-between lg:contents">
          <div class="login-auth-bg-mobile lg:hidden" aria-hidden="true" />
          <div class="login-form-area">
        <div class="login-header">
          <div class="login-logo">
            <span class="login-logo-text">{{ $t('brand_name') }}</span>
            <img src="@/assets/img/Icones/Icona_Logo_Perfil.png" alt="Loopy Logo" class="login-logo-image" />
          </div>
          <h1 class="login-title">{{ $t('login_welcome') }}</h1>
          <p class="login-subtitle">{{ $t('login_subtitle') }}</p>
        </div>

      <form class="login-form" novalidate @submit.prevent="ferLogin">
        <div v-if="errorMissatge && !authStore.loginBanShow" class="login-error-msg">
          {{ errorMissatge }}
        </div>
        
        <div>
          <input v-model="formulari.email" type="email" autocomplete="username" :placeholder="$t('email')" class="login-input" />
        </div>

        <div>
          <input v-model="formulari.contrasenya" type="password" autocomplete="current-password" :placeholder="$t('password')" class="login-input" />
        </div>

        <div>
          <button type="submit" :disabled="estaCarregant" class="login-btn-primary">
            {{ $t('login_button') }}
          </button>
        </div>

        <div>
          <button type="button" @click="loginAmbGoogle" class="login-btn-google">
            <svg class="google-icon-svg" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              <path fill="none" d="M1 1h22v22H1z"/>
            </svg>
            {{ $t('login_google') }}
          </button>
        </div>

      </form>
          </div>

      <div class="login-register-section">
        <div class="login-divider">
          <span class="login-divider-text">{{ $t('no_account') }}</span>
        </div>
        <div class="w-full max-w-sm mx-auto">
          <button type="button" class="login-btn-outline w-full" data-cy="login-go-register" @click="anarARegistre">
            {{ $t('register_button') }}
          </button>
        </div>
      </div>
        </div>

        <div class="auth-m-panel-register register-page-auth max-lg:flex max-lg:min-h-[100dvh] max-lg:w-full max-lg:flex-col max-lg:shrink-0 lg:hidden">
          <div class="register-form-shell">
            <form class="login-form mt-6 space-y-4" novalidate @submit.prevent>
              <div v-if="errorMissatgeRegistre" class="login-error-msg">
                {{ errorMissatgeRegistre }}
              </div>
              <div>
                <input v-model="formulariRegistre.nom" type="text" :placeholder="$t('name_placeholder')" class="login-input" />
              </div>
              <div>
                <input v-model="formulariRegistre.email" type="email" :placeholder="$t('email_placeholder')" class="login-input" />
              </div>
              <div>
                <input v-model="formulariRegistre.contrasenya" type="password" :placeholder="$t('password_placeholder_dots')" class="login-input" />
              </div>
              <div>
                <input v-model="formulariRegistre.confirmacio" type="password" :placeholder="$t('password_placeholder_dots')" class="login-input" />
              </div>
              <div>
                <button type="button" :disabled="estaCarregantRegistre" class="login-btn-primary" data-cy="login-register-submit" @click="registrarUsuariMobil">
                  {{ $t('register_button') }}
                </button>
              </div>
              <div class="login-divider">
                <span class="login-divider-text">{{ $t('already_have_account') }}</span>
              </div>
              <div>
                <button type="button" class="login-btn-outline w-full" data-cy="login-back-from-register" @click="tornarAlLoginMobil">
                  {{ $t('back_to_login') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== COLUMNA DERECHA (Bento Grid) ===== -->
    <div class="login-right-col">
      <div class="login-bg-pattern"></div>
      <div class="login-bento-container">
        <div class="login-bento-grid">
          <div class="bento-card-col">
            <div class="bento-card bento-card-small">
              <div class="bento-icon text-red-500">🏆</div>
              <h3 class="bento-title">{{ $t('preview.growth') }}</h3>
              <p class="bento-desc">{{ $t('preview.growth_desc') }}</p>
            </div>
            <div class="bento-card bento-card-small">
              <div class="bento-icon text-blue-500">🔲</div>
              <h3 class="bento-title">{{ $t('preview.templates') }}</h3>
              <p class="bento-desc">{{ $t('preview.templates_desc') }}</p>
            </div>
          </div>
          
          <div class="bento-card-col-mid">
            <div class="bento-card bento-card-small">
              <div class="bento-icon text-yellow-400">🔥</div>
              <h3 class="bento-title">{{ $t('preview.streaks') }}</h3>
              <p class="bento-desc">{{ $t('preview.streaks_desc') }}</p>
            </div>
            <div class="bento-card bento-card-small">
              <div class="bento-icon text-orange-500">💬</div>
              <h3 class="bento-title">{{ $t('preview.community') }}</h3>
              <p class="bento-desc">{{ $t('preview.community_desc') }}</p>
            </div>
          </div>

          <div class="bento-card-col">
             <div class="bento-card bento-card-tall">
              <div class="bento-icon text-purple-600">📈</div>
              <h3 class="bento-title">{{ $t('preview.stats') }}</h3>
              <p class="bento-desc">{{ $t('preview.stats_desc') }}</p>
            </div>
             <div class="bento-card bento-card-tall">
              <div class="bento-icon text-teal-600">🎯</div>
              <h3 class="bento-title">{{ $t('preview.challenges') }}</h3>
              <p class="bento-desc">{{ $t('preview.challenges_desc') }}</p>
            </div>
          </div>
        </div>

        <div class="bento-banner">
          <div class="bento-banner-content">
            <h2 class="bento-banner-title">{{ $t('preview.your_companion') }}</h2>
            <p class="bento-banner-subtitle">{{ $t('preview.companion_desc') }}</p>
          </div>
           <div class="bento-banner-ghost-wrap">
              <svg viewBox="0 0 100 100" class="bento-banner-ghost-svg">
                 <path d="M 20 50 C 20 20, 80 20, 80 50 L 80 95 C 80 95, 75 90, 70 95 C 65 100, 60 90, 50 95 C 40 100, 35 90, 30 95 C 25 100, 20 95, 20 95 Z" />
                 <circle cx="40" cy="50" r="5" fill="#fdfdfd" opacity="0.4" />
                 <circle cx="60" cy="50" r="5" fill="#fdfdfd" opacity="0.4" />
              </svg>
           </div>
           <div class="bento-banner-accent"></div>
        </div>
      </div>
    </div>
  </div>

  <Teleport to="body">
    <LoginBanSheet
      :show="authStore.loginBanShow"
      :ban="authStore.loginBanInfo"
      @close="authStore.tancarLoginBan()"
    />
  </Teleport>
</template>

<script setup>
definePageMeta({ layout: false });
import videoEntradaUrl from '~/assets/img/Onboarding/video/1-VideoEntradaApp.mp4';
import LoginBanSheet from '~/components/auth/LoginBanSheet.vue';

var authStore = useAuthStore();
</script>

<script>
var STORAGE_APP_ENTRY_VIDEO = 'loopy_app_entry_video_done';

export default {
  data: function () {
    return {
      formulari: { email: "", contrasenya: "" },
      mobilMostraRegistre: false,
      formulariRegistre: { nom: "", email: "", contrasenya: "", confirmacio: "" },
      errorMissatgeRegistre: "",
      estaCarregantRegistre: false,
      apiBaseRegistre: "http://localhost:8000",
      percentatgeProgres: 60,
      errorMissatge: "",
      estaCarregant: false,
      mostraSequenciaEntrada: false,
      faseEntrada: 'video',
      loopyBurstAnimate: false,
      timeoutEntradaVideoId: null,
      timeoutDespresLoopyId: null,
      timeoutFallbackLoopyId: null,
      sequenciaEntradaJaCompletada: false
    };
  },
  beforeMount: function () {
    if (typeof window === 'undefined') {
      return;
    }
    if (window.innerWidth >= 1024) {
      return;
    }
    try {
      if (window.sessionStorage.getItem(STORAGE_APP_ENTRY_VIDEO) === '1') {
        return;
      }
    } catch (e) {
      return;
    }
    this.mostraSequenciaEntrada = true;
    this.faseEntrada = 'video';
    this.sequenciaEntradaJaCompletada = false;
  },
  watch: {
    '$route.query.register': {
      immediate: true,
      handler: function (val) {
        this.mobilMostraRegistre = val === '1' || val === 1;
      }
    }
  },
  mounted: function () {
    var self = this;
    try {
      var c = useRuntimeConfig();
      self.apiBaseRegistre = (c.public.apiUrl || 'http://localhost:8000').replace(/\/$/, '');
    } catch (e) {
      self.apiBaseRegistre = 'http://localhost:8000';
    }
    if (!this.mostraSequenciaEntrada || this.faseEntrada !== 'video') {
      return;
    }
    this.$nextTick(function () {
      var el = self.$refs.videoEntradaRef;
      if (el) {
        el.muted = true;
        el.setAttribute('muted', '');
        el.play().catch(function () {
          self.finalitzarVideoEntrada();
        });
      }
      self.timeoutEntradaVideoId = window.setTimeout(function () {
        self.finalitzarVideoEntrada();
      }, 120000);
    });
  },
  beforeUnmount: function () {
    if (this.timeoutEntradaVideoId != null) {
      clearTimeout(this.timeoutEntradaVideoId);
      this.timeoutEntradaVideoId = null;
    }
    if (this.timeoutDespresLoopyId != null) {
      clearTimeout(this.timeoutDespresLoopyId);
      this.timeoutDespresLoopyId = null;
    }
    if (this.timeoutFallbackLoopyId != null) {
      clearTimeout(this.timeoutFallbackLoopyId);
      this.timeoutFallbackLoopyId = null;
    }
  },
  methods: {
    finalitzarVideoEntrada: function () {
      if (!this.mostraSequenciaEntrada || this.faseEntrada !== 'video') {
        return;
      }
      if (this.timeoutEntradaVideoId != null) {
        clearTimeout(this.timeoutEntradaVideoId);
        this.timeoutEntradaVideoId = null;
      }
      var el = this.$refs.videoEntradaRef;
      if (el) {
        try {
          el.pause();
          el.removeAttribute('src');
          el.load();
        } catch (e2) {}
      }
      this.faseEntrada = 'loopy';
      this.loopyBurstAnimate = false;
      var self = this;
      this.$nextTick(function () {
        requestAnimationFrame(function () {
          self.loopyBurstAnimate = true;
          if (self.timeoutFallbackLoopyId != null) {
            clearTimeout(self.timeoutFallbackLoopyId);
          }
          self.timeoutFallbackLoopyId = window.setTimeout(function () {
            if (!self.sequenciaEntradaJaCompletada) {
              self.completarSequenciaEntradaAlLogin();
            }
          }, 4000);
        });
      });
    },
    onLoopyBurstAnimationEnd: function (ev) {
      if (ev.target !== ev.currentTarget) {
        return;
      }
      if (this.sequenciaEntradaJaCompletada) {
        return;
      }
      var self = this;
      if (this.timeoutDespresLoopyId != null) {
        clearTimeout(this.timeoutDespresLoopyId);
      }
      this.timeoutDespresLoopyId = window.setTimeout(function () {
        self.completarSequenciaEntradaAlLogin();
      }, 650);
    },
    completarSequenciaEntradaAlLogin: function () {
      if (this.sequenciaEntradaJaCompletada) {
        return;
      }
      this.sequenciaEntradaJaCompletada = true;
      if (this.timeoutEntradaVideoId != null) {
        clearTimeout(this.timeoutEntradaVideoId);
        this.timeoutEntradaVideoId = null;
      }
      if (this.timeoutDespresLoopyId != null) {
        clearTimeout(this.timeoutDespresLoopyId);
        this.timeoutDespresLoopyId = null;
      }
      if (this.timeoutFallbackLoopyId != null) {
        clearTimeout(this.timeoutFallbackLoopyId);
        this.timeoutFallbackLoopyId = null;
      }
      try {
        if (typeof window !== 'undefined') {
          window.sessionStorage.setItem(STORAGE_APP_ENTRY_VIDEO, '1');
        }
      } catch (e) {}
      this.mostraSequenciaEntrada = false;
      this.faseEntrada = 'video';
      this.loopyBurstAnimate = false;
    },
    ferLogin: async function () {
      var self = this;
      var authStore = useAuthStore();
      var email = (self.formulari.email || "").trim();
      var contrasenya = self.formulari.contrasenya || "";
      if (!email || !contrasenya) {
        self.errorMissatge = this.$t('error_missing_fields');
        return;
      }
      self.errorMissatge = "";
      authStore.tancarLoginBan();
      self.estaCarregant = true;
      try {
        var nuxtApp = useNuxtApp();
        try {
          await authStore.loginUser(email, contrasenya);
          if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
          if (authStore.requiresOnboarding) {
            authStore.reiniciarEstatOnboarding();
            var habitStore = useHabitStore();
            habitStore.establirHabitsDesDeApi([]);
            if (typeof window !== 'undefined') {
              sessionStorage.setItem('loopy_register_onboarding_entrance', '1');
            }
            await navigateTo('/onboarding');
          } else {
            await navigateTo('/home');
          }
          return;
        } catch (errUser) {
          if (authStore.esErrorBanLogin(errUser)) {
            self.errorMissatge = "";
            return;
          }
          try {
            await authStore.loginAdmin(email, contrasenya);
            if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
            await navigateTo("/admin");
          } catch (errAdmin) {
            self.errorMissatge = errAdmin.message || this.$t('error_credentials');
          }
        }
      } finally {
        self.estaCarregant = false;
      }
    },
    loginAmbGoogle: function () {
      window.location.href = 'http://localhost:8000/api/auth/google/redirect';
    },
    anarARegistre: function () {
      if (typeof window !== 'undefined' && window.matchMedia('(max-width: 1023px)').matches) {
        this.mobilMostraRegistre = true;
        this.$router.replace({ path: '/auth/login', query: { register: '1' } }).catch(function () {});
        return;
      }
      useState('authSlideDir').value = 'forward';
      navigateTo('/auth/registre');
    },
    tornarAlLoginMobil: function () {
      this.mobilMostraRegistre = false;
      this.errorMissatgeRegistre = '';
      this.$router.replace({ path: '/auth/login', query: {} }).catch(function () {});
    },
    registrarUsuariMobil: async function () {
      var self = this;
      var f = self.formulariRegistre;
      if (!f.nom || !f.email || !f.contrasenya) {
        self.errorMissatgeRegistre = this.$t('error_all_fields_required');
        return;
      }
      if (f.contrasenya.length < 6) {
        self.errorMissatgeRegistre = this.$t('error_password_short');
        return;
      }
      if (f.contrasenya !== f.confirmacio) {
        self.errorMissatgeRegistre = this.$t('error_password_mismatch');
        return;
      }
      self.errorMissatgeRegistre = '';
      self.estaCarregantRegistre = true;
      try {
        var base = self.apiBaseRegistre || 'http://localhost:8000';
        var resposta = await fetch(base + '/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          credentials: 'include',
          body: JSON.stringify({
            nom: f.nom,
            email: f.email,
            contrasenya: f.contrasenya,
            contrasenya_confirmation: f.confirmacio
          })
        });
        var dades = await resposta.json();
        if (!resposta.ok) {
          self.errorMissatgeRegistre = dades.message || this.$t('error_registration_generic');
          return;
        }
        var authStore = useAuthStore();
        authStore.aplicarSessio({ token: dades.token, user: dades.user, role: 'user', requires_onboarding: true });
        authStore.reiniciarEstatOnboarding();
        var habitStore = useHabitStore();
        habitStore.establirHabitsDesDeApi([]);
        if (typeof window !== 'undefined') {
          sessionStorage.setItem('loopy_register_onboarding_entrance', '1');
        }
        await navigateTo('/onboarding');
      } catch (err) {
        self.errorMissatgeRegistre = this.$t('error_connection');
      } finally {
        self.estaCarregantRegistre = false;
      }
    }
  }
};
</script>

<style scoped>
.app-entry-sequence-root {
  position: fixed;
  inset: 0;
  z-index: 99999;
  pointer-events: auto;
}

.app-entry-video-layer {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000;
}

.app-entry-video-el {
  width: 100%;
  height: 100%;
  max-width: 100vw;
  max-height: 100vh;
  object-fit: cover;
  pointer-events: none;
  user-select: none;
  -webkit-user-select: none;
}

.app-entry-loopy-layer {
  position: absolute;
  inset: 0;
  background: #000;
  overflow: hidden;
}

.app-entry-loopy-burst {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  background-color: #7eb356;
  -webkit-clip-path: circle(0px at 50% 50%);
  clip-path: circle(0px at 50% 50%);
  will-change: clip-path;
}

.app-entry-loopy-burst--animate {
  animation: app-entry-burst-grow 1.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes app-entry-burst-grow {
  from {
    -webkit-clip-path: circle(0px at 50% 50%);
    clip-path: circle(0px at 50% 50%);
  }
  to {
    -webkit-clip-path: circle(160vmax at 50% 50%);
    clip-path: circle(160vmax at 50% 50%);
  }
}

.app-entry-loopy-word {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  z-index: 2;
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: clamp(2.75rem, 12vw, 5.5rem);
  font-weight: 800;
  letter-spacing: 0.04em;
  color: #ffffff;
  text-shadow: 0 4px 28px rgba(0, 0, 0, 0.35);
  pointer-events: none;
  user-select: none;
  opacity: 0;
  animation: app-entry-loopy-word-in 0.55s ease 0.18s forwards;
}

@keyframes app-entry-loopy-word-in {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.92);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}
</style>
