/**
 * Middleware per rutas d'usuari: exigeix role=user i token.
 * Si no s'aplica a cap pàgina, la protecció ve de require-auth.global.js.
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  // Redirigir a Login si no hi ha token o el rol no és user
  if (!authStore.token || authStore.role !== 'user') {
    return navigateTo('/Login');
  }
});
