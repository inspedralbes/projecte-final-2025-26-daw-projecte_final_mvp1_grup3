<template>
  <div>
    <ErrorContent
      :codi="500"
      titol="Error del servidor"
      missatge="Hi ha hagut un problema al servidor. Torna-ho a provar més tard."
      :mostrar-tornar="true"
      :ruta-tornar="rutaTornar"
    />
  </div>
</template>

<script setup>
/**
 * Pàgina d'error 500 - Internal Server Error.
 * Es mostra quan hi ha un error inesperat al servidor.
 */
definePageMeta({ layout: false });

var rutaTornar = '/';
// Determinar ruta del botó Tornar segons el rol de l'usuari
var authStore = useAuthStore();
if (typeof authStore !== 'undefined' && authStore.loadFromStorage) {
  authStore.loadFromStorage();
  if (authStore.role === 'admin') {
    rutaTornar = '/admin';
  } else {
    if (authStore.role === 'user') {
      rutaTornar = '/HomePage';
    }
  }
}
</script>
