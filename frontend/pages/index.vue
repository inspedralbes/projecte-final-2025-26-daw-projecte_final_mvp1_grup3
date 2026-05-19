<!--
  Component o pagina Nuxt: index.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<script setup>
/**
 * Pàgina d'entrada principal.
 * El middleware global ja redirigeix / segons rol; aquí cobrim el client
 * (p. ex. si index es monta sense haver passat encara per la mateixa lògica).
 */
onMounted(function() {
  var auth = useAuthStore();
  auth.loadFromStorage();
  if (auth.role === 'admin') {
    navigateTo('/admin');
    return;
  }
  if (auth.role === 'user' && auth.token) {
    navigateTo('/home');
    return;
  }
  navigateTo('/auth/login');
});
</script>

<template>
  <div class="min-h-screen bg-transparent flex items-center justify-center">
    <div class="flex flex-col items-center gap-4 animate-pulse">
      <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center text-white font-bold">L</div>
      <div class="text-gray-400 font-bold uppercase tracking-widest text-xs">{{ $t('loading_loopy') }}</div>
      <div class="mt-4">
        <LanguageSwitcher />
      </div>
    </div>
  </div>
</template>
