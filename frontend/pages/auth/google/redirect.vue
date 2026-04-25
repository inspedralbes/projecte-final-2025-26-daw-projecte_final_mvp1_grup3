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

onMounted(async () => {
  try {
    const token = Array.isArray(route.query.token) ? route.query.token[0] : route.query.token;
    const code = Array.isArray(route.query.code) ? route.query.code[0] : route.query.code;

    if (token) {
      // Flux actual del backend: retorna al frontend amb ?token=...
      authStore.aplicarSessio({ token, role: "user" });
      const sessioOk = await authStore.refrescarSessio();
      if (!sessioOk) {
        throw new Error("No s'ha pogut validar la sessió de Google.");
      }
    } else if (code) {
      // Si rebem ?code al frontend, fem redirecció de navegador al callback backend.
      // Evitem fetch aquí perquè el callback backend redirigeix i això pot donar CORS.
      const config = useRuntimeConfig();
      const base = (config.public.apiUrl || "").replace(/\/$/, "");
      window.location.href = `${base}/api/auth/google/callback?code=${encodeURIComponent(code)}`;
      return;
    } else {
      throw new Error("No s'ha rebut token ni codi de Google.");
    }

    const nuxtApp = useNuxtApp();
    if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
    await navigateTo("/home");
  } catch (err) {
    error.value = err.message || "Error al processar el login.";
  }
});
</script>
