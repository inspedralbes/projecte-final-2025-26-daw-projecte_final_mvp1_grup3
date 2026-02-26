<template>
  <div>
    <ErrorContent
      :codi="403"
      titol="Accés no autoritzat"
      :missatge="missatge"
      :mostrar-tornar="true"
      :ruta-tornar="rutaTornar"
    />
  </div>
</template>

<script setup>
/**
 * Pàgina d'error 403 - Forbidden.
 * Es mostra quan l'usuari intenta accedir a una ruta sense els permisos de rol adequats.
 */
definePageMeta({ layout: false });

var route = useRoute();
var missatge = 'No tens els permisos necessaris per accedir a aquesta pàgina.';
var rutaTornar = '/';

// A. Personalitzar missatge si s'indica la ruta bloquejada (query from)
if (route.query.from) {
  missatge = 'No tens els permisos necessaris per accedir a "' + String(route.query.from) + '".';
}

// B. Determinar ruta del botó Tornar segons el rol de l'usuari
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
