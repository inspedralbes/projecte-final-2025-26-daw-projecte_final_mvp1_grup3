/**
 * Composable per carregar hàbits, progress, logs i exposar accions (completar, incrementar).
 * Delega a useHabitStore i gameStore per progrés.
 */
export function useHabits() {
  var habitStore = useHabitStore();
  var gameStore = useGameStore();

  function carregarHabits() {
    return habitStore.obtenirHabitsDesDeApi();
  }

  function carregarProgres() {
    return gameStore.obtenirProgresHabits();
  }

  function obtenirProgres(habitId) {
    var mapa = gameStore.habitProgress || {};
    if (mapa[habitId]) {
      return mapa[habitId].progress || 0;
    }
    return 0;
  }

  function habitCompletatAvui(habitId) {
    var mapa = gameStore.habitProgress || {};
    if (mapa[habitId]) {
      return !!mapa[habitId].completed_today;
    }
    return false;
  }

  return {
    habits: computed(function () {
      return habitStore.habits;
    }),
    loading: computed(function () {
      return habitStore.loading;
    }),
    error: computed(function () {
      return habitStore.error;
    }),
    habitProgress: computed(function () {
      return gameStore.habitProgress;
    }),
    carregarHabits: carregarHabits,
    carregarProgres: carregarProgres,
    obtenirProgres: obtenirProgres,
    habitCompletatAvui: habitCompletatAvui,
    afegirHabit: habitStore.afegirHabit.bind(habitStore),
    actualitzarHabit: habitStore.actualitzarHabit.bind(habitStore),
    eliminarHabit: habitStore.eliminarHabit.bind(habitStore),
    guardarOActualitzarHabit: habitStore.guardarOActualitzarHabit.bind(habitStore),
    habitStore: habitStore,
    gameStore: gameStore
  };
}
