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
  const code = route.query.code;
  if (!code) {
    error.value = "No s'ha rebut cap codi de Google.";
    return;
  }

  try {
    await authStore.loginWithGoogle(code);
    const nuxtApp = useNuxtApp();
    if (nuxtApp.$updateSocketAuth) nuxtApp.$updateSocketAuth();
    await navigateTo("/home");
  } catch (err) {
    error.value = err.message || "Error al processar el login.";
  }
});
</script>
