/**
 * Modul JavaScript ES5: 0.auth-init.client.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * Plugin que carrega l'auth des de localStorage el més aviat possible (client).
 * S'executa abans que qualsevol altre plugin per assegurar que el token està disponible
 * abans de peticions API o connexió socket.
 */
export default defineNuxtPlugin(function () {
  var authStore = useAuthStore();
  authStore.loadFromStorage();
  if (authStore.role === "user" && authStore.user) {
    try {
      useGameStore().hidratarCosmeticsDesDeStorage();
    } catch (_) {}
  }
});
