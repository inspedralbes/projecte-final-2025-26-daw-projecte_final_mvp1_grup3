/**
 * Composable per useFetch amb Authorization Bearer.
 * Útil per a pàgines admin que criden l'API Laravel.
 */
export function useAuthFetch(url, options) {
  // A. Carregar token si estem al client
  var authStore = useAuthStore();
  if (process.client) {
    authStore.loadFromStorage();
  }

  // B. Construir URL base normalitzada
  var config = useRuntimeConfig();
  var baseUrl = (config.public.apiUrl || '').replace(/\/$/, '');
  var headers = authStore.getAuthHeaders();

  // C. Preparar opcions sense spread operator
  var opcionsFinals = {};
  if (options) {
    for (var clau in options) {
      if (Object.prototype.hasOwnProperty.call(options, clau)) {
        opcionsFinals[clau] = options[clau];
      }
    }
  }

  var headersFinals = {};
  for (var h in headers) {
    if (Object.prototype.hasOwnProperty.call(headers, h)) {
      headersFinals[h] = headers[h];
    }
  }
  if (options && options.headers) {
    for (var h2 in options.headers) {
      if (Object.prototype.hasOwnProperty.call(options.headers, h2)) {
        headersFinals[h2] = options.headers[h2];
      }
    }
  }

  opcionsFinals.baseURL = baseUrl;
  opcionsFinals.headers = headersFinals;

  // D. Executar useFetch amb opcions finals
  return useFetch(url, opcionsFinals);
}
