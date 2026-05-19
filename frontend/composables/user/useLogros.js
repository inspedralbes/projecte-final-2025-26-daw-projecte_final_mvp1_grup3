/**
 * Modul JavaScript ES5: useLogros.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * Composable per carregar logros.
 * Delega a useLogroStore.
 */
export function useLogros() {
  var logroStore = useLogroStore();

  function carregarLogros() {
    return logroStore.carregarLogros();
  }

  function setLogros(logrosArray) {
    logroStore.setLogros(logrosArray);
  }

  return {
    logros: computed(function () {
      return logroStore.logros;
    }),
    loading: computed(function () {
      return logroStore.loading;
    }),
    error: computed(function () {
      return logroStore.error;
    }),
    carregarLogros: carregarLogros,
    setLogros: setLogros,
    logroStore: logroStore
  };
}
