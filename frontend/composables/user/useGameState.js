/**
 * Composable per gestionar l'estat del joc (game_state, nivell, XP, ratxa, monedes, missió).
 * Delega al gameStore.
 */
export function useGameState() {
  var gameStore = useGameStore();

  function carregarDadesHome() {
    return gameStore.carregarDadesHome();
  }

  function obtenirEstatJoc() {
    return gameStore.obtenirEstatJoc();
  }

  return {
    // Estat reactiu des del store
    userId: computed(function () {
      return gameStore.userId;
    }),
    ratxa: computed(function () {
      return gameStore.ratxa;
    }),
    ratxaMaxima: computed(function () {
      return gameStore.ratxaMaxima;
    }),
    xpTotal: computed(function () {
      return gameStore.xpTotal;
    }),
    monedes: computed(function () {
      return gameStore.monedes;
    }),
    nivell: computed(function () {
      return gameStore.nivell;
    }),
    xpActualNivel: computed(function () {
      return gameStore.xpActualNivel;
    }),
    xpObjetivoNivel: computed(function () {
      return gameStore.xpObjetivoNivel;
    }),
    canSpinRoulette: computed(function () {
      return gameStore.canSpinRoulette;
    }),
    ruletaUltimaTirada: computed(function () {
      return gameStore.ruletaUltimaTirada;
    }),
    missioDiaria: computed(function () {
      return gameStore.missioDiaria;
    }),
    missioCompletada: computed(function () {
      return gameStore.missioCompletada;
    }),
    habitProgress: computed(function () {
      return gameStore.habitProgress;
    }),
    // Accions
    carregarDadesHome: carregarDadesHome,
    obtenirEstatJoc: obtenirEstatJoc,
    actualitzarRatxa: gameStore.actualitzarRatxa.bind(gameStore),
    actualitzarXP: gameStore.actualitzarXP.bind(gameStore),
    actualitzarDesDeXpUpdate: gameStore.actualitzarDesDeXpUpdate.bind(gameStore),
    sincronitzarUsuariId: gameStore.sincronitzarUsuariId.bind(gameStore),
    // Store directe per a accions que el necessiten
    gameStore: gameStore
  };
}
