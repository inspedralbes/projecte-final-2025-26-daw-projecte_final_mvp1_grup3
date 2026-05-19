/**
 * Modul JavaScript ES5: useAuthFetch.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * @deprecated Usar useAuthFetch des de composables/useApi.js
 * Re-export per compatibilitat; les pàgines admin poden seguir usant useAuthFetch.
 */
export { useAuthFetch } from '~/composables/useApi.js';
