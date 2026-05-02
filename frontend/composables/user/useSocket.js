/**
 * Composable per exposar el socket i emetre esdeveniments.
 * habit_action, roulette_spin, habit_progress, habit_complete, etc.
 */
export function useSocket() {
  var nuxtApp = useNuxtApp();
  var socket = nuxtApp.$socket || null;

  function emitir(event, payload) {
    if (socket && typeof socket.emit === "function") {
      socket.emit(event, payload || {});
    }
  }

  function enviarProgresHabit(idHabit, delta) {
    emitir("habit_progress", { habit_id: idHabit, valor: delta });
  }

  function confirmarHabit(idHabit) {
    emitir("habit_complete", {
      habit_id: idHabit,
      data: new Date().toISOString()
    });
  }

  function enviarSpinRuleta() {
    emitir("roulette_spin", {});
  }

  function registrarListener(event, callback) {
    if (socket && typeof socket.on === "function" && typeof callback === "function") {
      socket.on(event, callback);
    }
  }

  function eliminarListener(event, callback) {
    if (socket && typeof socket.off === "function") {
      socket.off(event, callback);
    }
  }

  return {
    socket: socket,
    emitir: emitir,
    enviarProgresHabit: enviarProgresHabit,
    confirmarHabit: confirmarHabit,
    enviarSpinRuleta: enviarSpinRuleta,
    registrarListener: registrarListener,
    eliminarListener: eliminarListener
  };
}
