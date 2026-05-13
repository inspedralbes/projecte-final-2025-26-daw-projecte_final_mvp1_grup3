<template>
  <div>
    <ErrorContent
      :codi="codiError"
      :titol="titolError"
      :missatge="missatgeError"
      :mostrar-tornar="true"
      :ruta-tornar="rutaTornar"
    />
  </div>
</template>

<script setup>
/**
 * Pàgina d'error global de Nuxt.
 * Es mostra quan es llança un error (404, 500, etc.) o es crida showError().
 */
var props = defineProps({
  error: {
    type: Object,
    default: function () {
      return {};
    },
  },
});

var codiError = 500;
var titolError = "Error del servidor";
var missatgeError = "Hi ha hagut un problema. Torna-ho a provar més tard.";

// A. Assignar codi, títol i missatge segons el statusCode de l'error
if (props.error && props.error.statusCode) {
  codiError = props.error.statusCode;
  if (props.error.statusCode === 404) {
    titolError = "Pàgina no trobada";
    missatgeError = "La pàgina que cerques no existeix o ha estat moguda.";
  } else {
    if (props.error.statusCode === 403) {
      titolError = "Accés no autoritzat";
      missatgeError =
        "No tens els permisos necessaris per accedir a aquesta pàgina.";
    } else {
      if (props.error.statusCode >= 500) {
        titolError = "Error del servidor";
        if (props.error.message) {
          missatgeError = props.error.message;
        } else {
          missatgeError =
            "Hi ha hagut un problema al servidor. Torna-ho a provar més tard.";
        }
      }
    }
  }
}

// B. Evitar composables aquí: en errors SSR primerencs poden no estar disponibles.
//    Fem servir una ruta segura i neutral.
var rutaTornar = "/";
</script>
