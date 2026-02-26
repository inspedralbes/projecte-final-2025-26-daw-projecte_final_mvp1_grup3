/**
 * Middleware global: usuari no autenticat no pot accedir a cap ruta
 * excepte /Login, /registre, /error/* i / (que redirigeix a Login).
 * Comprova rol per restringir accés a /admin (només admin) i a rutes usuari (només user).
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  // A. Carregar estat d'autenticació des de localStorage
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  var path = to.path || '';
  var rutasPubliques = ['/Login', '/login', '/registre', '/'];
  var esPublica = false;
  // B. Comprovar si la ruta actual és pública
  for (var i = 0; i < rutasPubliques.length; i++) {
    var r = rutasPubliques[i];
    if (path === r || path.startsWith(r + '?')) {
      esPublica = true;
      break;
    }
  }

  // C. Les rutes /error/* són accessibles sense autenticació
  if (path.startsWith('/error/')) {
    return;
  }

  // D. Rutes públiques: permetre accés o redirigir / cap al Login/dashboard
  if (esPublica) {
    if (path === '/' || path === '') {
      if (authStore.token && authStore.role) {
        if (authStore.role === 'admin') {
          return navigateTo('/admin');
        } else {
          return navigateTo('/HomePage');
        }
      }
      return navigateTo('/Login');
    }
    return;
  }

  // E. Rutes protegides: exigeix token vàlid
  if (!authStore.token || !authStore.isAuthenticated) {
    return navigateTo('/Login');
  }

  // F. Comprovar rol: admin només a /admin, user només a rutes no-admin
  var esAdmin = path.startsWith('/admin');
  if (esAdmin && authStore.role !== 'admin') {
    return navigateTo('/error/403?from=' + encodeURIComponent(path));
  }
  if (!esAdmin && authStore.role === 'admin') {
    return navigateTo('/error/403?from=' + encodeURIComponent(path));
  }
});
