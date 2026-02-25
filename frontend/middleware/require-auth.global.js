/**
 * Middleware global: usuari no autenticat no pot accedir a cap ruta
 * excepte /Login, /registre i / (que redirigeix a Login).
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  var path = to.path || '';
  var rutasPubliques = ['/Login', '/login', '/registre', '/'];
  var esPublica = rutasPubliques.some(function (r) {
    return path === r || path.startsWith(r + '?');
  });

  if (esPublica) {
    if (path === '/' || path === '') {
      if (authStore.token && authStore.role) {
        return navigateTo(authStore.role === 'admin' ? '/admin' : '/HomePage');
      }
      return navigateTo('/Login');
    }
    return;
  }

  if (!authStore.token || !authStore.isAuthenticated) {
    return navigateTo('/Login');
  }

  var esAdmin = path.startsWith('/admin');
  if (esAdmin && authStore.role !== 'admin') {
    return navigateTo('/HomePage');
  }
  if (!esAdmin && authStore.role === 'admin') {
    return navigateTo('/admin');
  }
});
