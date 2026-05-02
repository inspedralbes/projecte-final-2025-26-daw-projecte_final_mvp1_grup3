<template>
  <div class="global-app-container login-container">
    <div class="login-lang-switch">
      <LanguageSwitcher />
    </div>

    <!-- ===== COLUMNA IZQUIERDA (Formulario de Login) ===== -->
    <div class="login-left-col">
      <div class="login-header">
        <div class="login-logo">
          <span class="login-logo-text">Loopy</span>
          <img src="@/assets/img/LogoLoopy.png" alt="Loopy Logo" class="login-logo-image" />
        </div>
        <h1 class="login-title">{{ $t('login_welcome') }}</h1>
        <p class="login-subtitle">{{ $t('login_subtitle') }}</p>
      </div>

      <form class="login-form" @submit.prevent="ferLogin">
        <div v-if="errorMissatge" class="login-error-msg">
          {{ errorMissatge }}
        </div>
        
        <div>
          <input v-model="formulari.email" type="email" :placeholder="$t('email')" class="login-input" />
        </div>

        <div>
          <input v-model="formulari.contrasenya" type="password" :placeholder="$t('password')" class="login-input" />
        </div>

        <div class="pt-4">
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

        <div class="login-divider">
          <span class="login-divider-text">O</span>
        </div>

        <div>
          <NuxtLink to="/auth/registre" class="block w-full">
            <button type="button" class="login-btn-outline">
              {{ $t('register_button') }}
            </button>
          </NuxtLink>
        </div>
      </form>
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
</template>

<script setup>
definePageMeta({ layout: false });
</script>

<script>
export default {
  data: function () {
    return {
      formulari: { email: "", contrasenya: "" },
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
        self.errorMissatge = this.$t('error_missing_fields');
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
          await navigateTo("/home");
          return;
        } catch (errUser) {
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
      const config = useRuntimeConfig();
      const apiUrl = config.public.apiUrl;
      window.location.href = `${apiUrl}/api/auth/google/redirect`;
    }
  }
};
</script>
