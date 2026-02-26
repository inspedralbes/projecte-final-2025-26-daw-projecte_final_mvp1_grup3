/**
 * Middleware per rutas d'admin: exigeix role=admin i token.
 * Si no s'aplica a cap pàgina, la protecció ve de require-auth.global.js.
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  // Redirigir a Login si no hi ha token o el rol no és admin
  if (!authStore.token || authStore.role !== 'admin') {
    return navigateTo('/Login');
  }
});
