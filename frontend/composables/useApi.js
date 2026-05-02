/**
 * Capa d'API unificada per a peticions autenticades.
 * Única referència per fetch amb refresh 401, useFetch amb auth i baseURL.
 */
import { authFetch as authFetchImpl } from '~/utils/authFetch.js';

/**
 * Retorna la URL base de l'API (sense barra final).
 */
export function getBaseUrl() {
  var config = useRuntimeConfig();
  return (config.public.apiUrl || '').replace(/\/$/, '');
}

/**
 * Fetch amb cookies i refresh automàtic en 401.
 * Re-exporta la implementació de utils/authFetch.js.
 */
export async function authFetch(url, options) {
  return authFetchImpl(url, options);
}

/**
 * useFetch amb cookies i headers d'autenticació.
 * Útil per pàgines admin que criden l'API Laravel.
 */
export function useAuthFetch(url, options) {
  var authStore = useAuthStore();
  var baseUrl = getBaseUrl();
  var headers = authStore.getAuthHeaders();
  var opcionsFinals = {};
  var headersFinals;
  var reqHeaders;

  if (options) {
    opcionsFinals = Object.assign({}, options);
  }

  if (import.meta.server) {
    reqHeaders = useRequestHeaders(['cookie']);
    if (reqHeaders) {
      headers = Object.assign({}, headers, reqHeaders);
    }
  }

  if (options && options.headers) {
    headersFinals = Object.assign({}, headers, options.headers);
  } else {
    headersFinals = headers;
  }

  opcionsFinals.baseURL = baseUrl;
  opcionsFinals.credentials = 'include';
  opcionsFinals.headers = headersFinals;

  return useFetch(url, opcionsFinals);
}

/**
 * Composable que retorna totes les eines d'API.
 * Útil quan es vol accedir a authFetch, useAuthFetch o getBaseUrl des d'un setup.
 */
export function useApi() {
  return {
    authFetch: authFetch,
    useAuthFetch: useAuthFetch,
    getBaseUrl: getBaseUrl
  };
}
