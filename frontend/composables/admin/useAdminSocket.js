/**
 * Composable per esdeveniments socket d'admin.
 * admin_join, admin:request_connected, admin:connected_users, admin_action.
 */
export function useAdminSocket() {
  var nuxtApp = useNuxtApp();
  var socket = nuxtApp.$socket || null;

  function adminJoin() {
    if (socket && typeof socket.emit === "function") {
      socket.emit("admin_join", {});
    }
  }

  function requestConnected() {
    if (socket && typeof socket.emit === "function") {
      socket.emit("admin:request_connected");
    }
  }

  function onConnectedUsers(callback) {
    if (socket && typeof socket.on === "function" && typeof callback === "function") {
      socket.on("admin:connected_users", callback);
    }
  }

  function onActionConfirmed(callback) {
    if (socket && typeof socket.on === "function" && typeof callback === "function") {
      socket.on("admin_action_confirmed", callback);
    }
  }

  function emitAction(payload) {
    if (socket && typeof socket.emit === "function") {
      socket.emit("admin_action", payload);
    }
  }

  return {
    socket: socket,
    adminJoin: adminJoin,
    requestConnected: requestConnected,
    onConnectedUsers: onConnectedUsers,
    onActionConfirmed: onActionConfirmed,
    emitAction: emitAction
  };
}
