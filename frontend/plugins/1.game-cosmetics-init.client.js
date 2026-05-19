/**
 * Carrega skin/fons des de Laravel (game_state) abans de mostrar l'app.
 * Primer hidrata cache local (sense flash); després confirma amb l'API.
 */
export default defineNuxtPlugin(async function () {
  var authStore = useAuthStore();
  authStore.loadFromStorage();

  if (!authStore.role || authStore.role === "admin") {
    return;
  }

  var gameStore = useGameStore();
  if (!gameStore) {
    return;
  }

  gameStore.hidratarCosmeticsDesDeStorage();

  try {
    await gameStore.obtenirEstatJoc();
  } catch (_) {
    gameStore.cosmeticsReady = true;
  }
});
