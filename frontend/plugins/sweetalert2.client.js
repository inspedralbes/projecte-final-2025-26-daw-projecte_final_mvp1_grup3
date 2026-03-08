import Swal from 'sweetalert2';

/**
 * Plugin per injectar SweetAlert2 a tota l'aplicació Nuxt.
 * S'exposa com a $swal per ser accessible en scripts i templates.
 */
export default defineNuxtPlugin(function (nuxtApp) {
  return {
    provide: {
      swal: Swal
    }
  };
});
