/**
 * Controla la direcció de la transició entre /auth/login i /auth/registre.
 */
export default defineNuxtRouteMiddleware(function (to, from) {
  var dir = useState('authSlideDir', function () {
    /** @type {'forward' | 'reverse' | null} */
    return null;
  });
  if (!from || !to) {
    return;
  }
  if (from.path === '/auth/login' && to.path === '/auth/registre') {
    dir.value = 'forward';
  } else if (from.path === '/auth/registre' && to.path === '/auth/login') {
    dir.value = 'reverse';
  } else {
    var auth = ['/auth/login', '/auth/registre'];
    var fromAuth = auth.includes(from.path);
    var toAuth = auth.includes(to.path);
    if (!fromAuth || !toAuth) {
      dir.value = null;
    }
  }
});
