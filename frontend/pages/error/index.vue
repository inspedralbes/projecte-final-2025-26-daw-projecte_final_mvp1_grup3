<template>
  <div>
    <ErrorContent
      :codi="codi"
      :titol="titol"
      :missatge="missatge"
      :mostrar-tornar="true"
      :ruta-tornar="rutaTornar"
    />
  </div>
</template>

<script setup>
/**
 * Pàgina d'error genèrica a /error.
 * Mostra el codi, títol i missatge segons query params o valors per defecte.
 */
definePageMeta({ layout: false });

var route = useRoute();
var codi = 500;
var titol = 'S\'ha produït un error';
var missatge = 'Torna-ho a provar més tard o contacta amb el suport.';

// A. Llegir codi, títol i missatge des dels query params
if (route.query.codi) {
  var codiParsejat = parseInt(route.query.codi, 10);
  if (!isNaN(codiParsejat)) {
    codi = codiParsejat;
  }
}

if (route.query.titol) {
  titol = String(route.query.titol);
}

if (route.query.missatge) {
  missatge = String(route.query.missatge);
}

// B. Determinar ruta del botó Tornar segons el rol de l'usuari
var rutaTornar = '/';
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
