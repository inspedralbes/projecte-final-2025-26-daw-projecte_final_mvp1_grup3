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

        <div class="login-divider">
          <span class="login-divider-text">{{ $t('login_continue_with') }}</span>
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
    }
  }
};
</script>
