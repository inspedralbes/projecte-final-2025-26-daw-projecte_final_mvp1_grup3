/**
 * Composable per useFetch amb Authorization Bearer.
 * Útil per a pàgines admin que criden l'API Laravel.
 */
export function useAuthFetch(url, options) {
  var authStore = useAuthStore();
  if (import.meta.client) {
    authStore.loadFromStorage();
  }
  var config = useRuntimeConfig();
  var baseUrl = (config.public.apiUrl || '').replace(/\/$/, '');
  var headers = authStore.getAuthHeaders();
  return useFetch(url, {
    baseURL: baseUrl,
    ...options,
    headers: { ...headers, ...(options && options.headers) }
  });
}
