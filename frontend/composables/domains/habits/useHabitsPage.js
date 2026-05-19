import { useHabitStore } from '~/stores/useHabitStore.js';
import { useGameStore } from '~/stores/gameStore.js';
import { useLogroStore } from '~/stores/useLogroStore.js';
import { useAuthStore } from '~/stores/useAuthStore.js';

/**
 * Càrrega de dades GET per a la pàgina home / hàbits.
 */
export function useHabitsPage() {
  var habitStore = useHabitStore();
  var gameStore = useGameStore();
  var logroStore = useLogroStore();

  async function carregarHomeInicial() {
    var authStore = useAuthStore();
    authStore.loadFromStorage();
    gameStore.sincronitzarUsuariId();
    habitStore.carregarHabitsLocal();

    var promises = [];
    promises.push(habitStore.obtenirHabitsDesDeApi());
    promises.push(
      gameStore.carregarDadesHome().then(function (dades) {
        if (dades && dades.logros) {
          logroStore.setLogros(dades.logros);
        }
        return dades;
      })
    );
    return Promise.all(promises);
  }

  return {
    carregarHomeInicial: carregarHomeInicial,
    habitStore: habitStore,
    gameStore: gameStore,
    logroStore: logroStore
  };
}
