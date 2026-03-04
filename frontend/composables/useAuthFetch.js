/**
 * Composable per useFetch amb Authorization Bearer.
 * Útil per a pàgines admin que criden l'API Laravel.
 */
/**
 * useFetch amb cookies i headers d'autenticació.
 */
export function useAuthFetch(url, options) {
  // A. Preparar dependències i base URL
  var authStore = useAuthStore();
  var config = useRuntimeConfig();
  var baseUrl = (config.public.apiUrl || '').replace(/\/$/, '');
  var headers = authStore.getAuthHeaders();
  var opcionsFinals = {};
  var headersFinals;
  var reqHeaders;

  // B. Copiar opcions si existeixen
  if (options) {
    opcionsFinals = Object.assign({}, options);
  }

  // C. Afegir cookies de la request si és SSR
  if (import.meta.server) {
    reqHeaders = useRequestHeaders(['cookie']);
    if (reqHeaders) {
      headers = Object.assign({}, headers, reqHeaders);
    }
  }

  // D. Combinar headers finals
  if (options && options.headers) {
    headersFinals = Object.assign({}, headers, options.headers);
  } else {
    headersFinals = headers;
  }

  // E. Configurar opcions finals
  opcionsFinals.baseURL = baseUrl;
  opcionsFinals.credentials = 'include';
  opcionsFinals.headers = headersFinals;

  // F. Executar useFetch
  return useFetch(url, opcionsFinals);
}
