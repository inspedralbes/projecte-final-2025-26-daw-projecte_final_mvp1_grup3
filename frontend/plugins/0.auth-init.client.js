/**
 * Plugin que carrega l'auth des de localStorage el més aviat possible (client).
 * S'executa abans que qualsevol altre plugin per assegurar que el token està disponible
 * abans de peticions API o connexió socket.
 */
export default defineNuxtPlugin(function () {
  var authStore = useAuthStore();
  authStore.loadFromStorage();
});
