/**
 * Fetch amb cookies i refresh automàtic.
 * Inclou lock singleton: si múltiples peticions reben 401 simultàniament,
 * només una fa el refresh i les altres esperen el resultat.
 */
var _refreshPromise = null;

export async function authFetch(url, options) {
  var config = useRuntimeConfig();
  var base = (config.public.apiUrl || '').replace(/\/$/, '');
  var fullUrl = url;
  var authStore = useAuthStore();
  var headers = {};
  var opts = options || {};

  if (typeof url === 'string') {
    if (url.indexOf('http') !== 0) {
      if (url.charAt(0) === '/') {
        fullUrl = base + url;
      } else {
        fullUrl = base + '/' + url;
      }
    }
  }

  headers = Object.assign({}, authStore.getAuthHeaders(), opts.headers || {});
  if (opts.body && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json';
  }

  var fetchOpts = Object.assign({}, opts, {
    headers: headers,
    credentials: 'include'
  });

  var resposta = await fetch(fullUrl, fetchOpts);
  if (resposta.status !== 401) {
    return resposta;
  }

  var refrescat = await intentarRefresh(authStore, base);
  if (!refrescat) {
    return resposta;
  }

  headers = Object.assign({}, authStore.getAuthHeaders(), opts.headers || {});
  fetchOpts = Object.assign({}, opts, { headers: headers, credentials: 'include' });
  resposta = await fetch(fullUrl, fetchOpts);
  return resposta;
}

async function intentarRefresh(authStore, base) {
  if (_refreshPromise) {
    return _refreshPromise;
  }

  _refreshPromise = _ferRefresh(authStore, base);

  try {
    var resultat = await _refreshPromise;
    return resultat;
  } finally {
    _refreshPromise = null;
  }
}

async function _ferRefresh(authStore, base) {
  var url = base + '/api/auth/refresh';
  try {
    var resposta = await fetch(url, {
      method: 'POST',
      headers: authStore.getAuthHeaders(),
      credentials: 'include'
    });
    if (!resposta.ok) {
      if (resposta.status === 401 || resposta.status === 403) {
        await authStore.logout();
      }
      return false;
    }
    var dades = await resposta.json();
    authStore.aplicarSessio(dades);
    return true;
  } catch (e) {
    return false;
  }
}
