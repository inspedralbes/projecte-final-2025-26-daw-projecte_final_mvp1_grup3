/**
 * Pont fin cap a $socket injectat pel plugin Nuxt.
 */
export function useSocketBridge() {
  var nuxtApp = useNuxtApp();
  var socket = nuxtApp.$socket || null;

  function emitir(event, payload) {
    if (socket && typeof socket.emit === 'function') {
      socket.emit(event, payload || {});
    }
  }

  function registrar(event, callback) {
    if (socket && typeof socket.on === 'function' && typeof callback === 'function') {
      socket.on(event, callback);
    }
  }

  function eliminar(event, callback) {
    if (socket && typeof socket.off === 'function') {
      if (callback) {
        socket.off(event, callback);
      } else {
        socket.off(event);
      }
    }
  }

  function estaConnectat() {
    if (socket && socket.connected === true) {
      return true;
    }
    return false;
  }

  function obtenirSocket() {
    return socket;
  }

  function connectarAmbToken(token) {
    if (!socket) {
      return;
    }
    if (token) {
      socket.auth = { token: token };
    }
    if (!socket.connected) {
      socket.connect();
    }
  }

  return {
    socket: socket,
    emitir: emitir,
    registrar: registrar,
    eliminar: eliminar,
    estaConnectat: estaConnectat,
    obtenirSocket: obtenirSocket,
    connectarAmbToken: connectarAmbToken
  };
}
