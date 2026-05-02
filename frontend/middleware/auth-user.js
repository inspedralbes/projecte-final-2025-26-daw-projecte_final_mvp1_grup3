/**
 * Middleware per rutas d'usuari: exigeix role=user i token.
 * Si no s'aplica a cap pàgina, la protecció ve de require-auth.global.js.
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var authStore = useAuthStore();
  authStore.loadFromStorage();
  var roleCookie = useCookie('loopy_role');
  var role = authStore.role;
  if (!role && roleCookie && roleCookie.value) {
    role = roleCookie.value;
  }

  // Redirigir a Login si no hi ha token o el rol no és user
  if (role !== 'user') {
    return navigateTo('/auth/login');
  }
});
