<template>
  <div>
    <ErrorContent
      :codi="404"
      titol="Pàgina no trobada"
      missatge="La pàgina que cerques no existeix o ha estat moguda."
      :mostrar-tornar="true"
      :ruta-tornar="rutaTornar"
    />
  </div>
</template>

<script setup>
/**
 * Pàgina d'error 404 - Not Found.
 * Es mostra quan l'usuari intenta accedir a una ruta que no existeix.
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
