<script setup>
/**
 * Pàgina d'entrada principal.
 * Redirigeix automàticament al Login seguint el flux de l'aplicació.
 */
onMounted(function() {
  navigateTo('/Login');
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="flex flex-col items-center gap-4 animate-pulse">
      <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center text-white font-bold">L</div>
      <div class="text-gray-400 font-bold uppercase tracking-widest text-xs">Carregant Loopy</div>
    </div>
  </div>
</template>
