/**
 * Middleware per rutas d'admin: exigeix role=admin i token.
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  if (!authStore.token || authStore.role !== 'admin') {
    return navigateTo('/Login');
  }
});
