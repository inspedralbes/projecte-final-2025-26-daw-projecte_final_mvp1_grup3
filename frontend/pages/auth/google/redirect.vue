<!--
  Component o pagina Nuxt: redirect.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="p-8 bg-white rounded-xl shadow-lg text-center">
      <div v-if="error" class="text-red-500">
        <h2 class="text-xl font-bold mb-2">Error de Login</h2>
        <p>{{ error }}</p>
        <NuxtLink to="/auth/login" class="mt-4 inline-block text-blue-500 underline">Tornar al login</NuxtLink>
      </div>
      <div v-else>
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#517d36] mx-auto mb-4"></div>
        <p class="text-gray-600">Iniciant sessió amb Google...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ layout: false });

const route = useRoute();
const authStore = useAuthStore();
const error = ref(null);

function getApiBase() {
  try {
    var config = useRuntimeConfig();
    var url = config.public.apiUrl;
    if (url && typeof url === 'string' && url.startsWith('http')) {
      return url.replace(/\/$/, '');
    }
  } catch (e) {}
  return 'http://localhost:8000';
}

onMounted(async () => {
  try {
    const token = Array.isArray(route.query.token) ? route.query.token[0] : route.query.token;
    const code = Array.isArray(route.query.code) ? route.query.code[0] : route.query.code;
    const onboarding = Array.isArray(route.query.onboarding) ? route.query.onboarding[0] : route.query.onboarding;

    if (token) {
      await authStore.completarSessioGoogle(token, onboarding);
    } else if (code) {
      window.location.href = getApiBase() + '/api/auth/google/callback?code=' + encodeURIComponent(code);
      return;
    } else {
      throw new Error("No s'ha rebut token ni codi de Google.");
    }

    const nuxtApp = useNuxtApp();
    if (nuxtApp.$updateSocketAuth) {
      nuxtApp.$updateSocketAuth();
    }
    if (authStore.requiresOnboarding) {
      authStore.reiniciarEstatOnboarding();
      const habitStore = useHabitStore();
      habitStore.establirHabitsDesDeApi([]);
      await navigateTo('/onboarding');
    } else {
      await navigateTo('/home');
    }
  } catch (err) {
    error.value = err.message || 'Error al processar el login.';
  }
});
</script>
