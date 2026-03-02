/**
 * Fetch amb cookies i refresh automàtic.
 */
export async function authFetch(url, options) {
  // A. Preparar configuració base i variables
  var config = useRuntimeConfig();
  var base = (config.public.apiUrl || '').replace(/\/$/, '');
  var fullUrl = url;
  var authStore = useAuthStore();
  var headers = {};
  var opts = options || {};

  // B. Normalitzar URL si és relativa
  if (typeof url === 'string') {
    if (url.indexOf('http') !== 0) {
      if (url.charAt(0) === '/') {
        fullUrl = base + url;
      } else {
        fullUrl = base + '/' + url;
      }
    }
  }

  // C. Preparar headers i opcions del fetch
  headers = Object.assign({}, authStore.getAuthHeaders(), opts.headers || {});

  var fetchOpts = Object.assign({}, opts, {
    headers: headers,
    credentials: 'include'
  });

  // D. Fer la petició principal
  var resposta = await fetch(fullUrl, fetchOpts);
  if (resposta.status !== 401) {
    return resposta;
  }

  // E. Intentar refresh si el token ha caducat
  var refrescat = await intentarRefresh(authStore, base);
  if (!refrescat) {
    return resposta;
  }

  // F. Reintentar la petició original
  resposta = await fetch(fullUrl, fetchOpts);
  return resposta;
}

async function intentarRefresh(authStore, base) {
  // A. Construir URL de refresh
  var url = base + '/api/auth/refresh';
  try {
    // B. Fer la petició de refresh amb cookies
    var resposta = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json'
      },
      credentials: 'include'
    });
    // C. Si falla, fer logout
    if (!resposta.ok) {
      await authStore.logout();
      return false;
    }
    // D. Aplicar nova sessió
    var dades = await resposta.json();
    authStore.aplicarSessio(dades);
    return true;
  } catch (e) {
    // E. Error inesperat: logout
    await authStore.logout();
    return false;
  }
}
