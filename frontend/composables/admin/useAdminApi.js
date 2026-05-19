/**
 * Modul JavaScript ES5: useAdminApi.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * Composable per crides d'API admin.
 * Utilitza useAuthFetch amb baseURL; endpoints tipus /api/admin/*
 */
import { useAuthFetch, authFetch, getBaseUrl } from "~/composables/useApi.js";

export function useAdminApi() {
  function fetchAdmin(url, options) {
    var fullUrl = url.indexOf("http") === 0 ? url : getBaseUrl() + (url.indexOf("/") === 0 ? url : "/" + url);
    return authFetch(fullUrl, options || {});
  }

  return {
    useAuthFetch: useAuthFetch,
    authFetch: authFetch,
    fetchAdmin: fetchAdmin,
    getBaseUrl: getBaseUrl
  };
}
