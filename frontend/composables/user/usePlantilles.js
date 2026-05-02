/**
 * Composable per carregar plantilles i assignar.
 * Delega a usePlantillaStore.
 */
export function usePlantilles() {
  var plantillaStore = usePlantillaStore();

  function carregarPlantilles(filter, userId) {
    return plantillaStore.obtenirPlantillesDesDeApi(filter, userId);
  }

  return {
    plantilles: computed(function () {
      return plantillaStore.plantilles;
    }),
    loading: computed(function () {
      return plantillaStore.loading;
    }),
    error: computed(function () {
      return plantillaStore.error;
    }),
    carregarPlantilles: carregarPlantilles,
    plantillaStore: plantillaStore
  };
}
