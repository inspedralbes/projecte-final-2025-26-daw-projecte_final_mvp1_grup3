/**
 * Modul JavaScript ES5: useSocketConfig.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

export function useSocketConfig() {
  var config = useRuntimeConfig();
  var socketUrl = config.public.socketUrl;

  return {
    socketUrl: socketUrl,
  };
}
