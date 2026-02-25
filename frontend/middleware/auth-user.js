/**
 * Middleware per rutas d'usuari: exigeix role=user i token.
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  if (!authStore.token || authStore.role !== 'user') {
    return navigateTo('/Login');
  }
});
