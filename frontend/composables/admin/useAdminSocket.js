/**
 * Modul JavaScript ES5: useAdminSocket.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * Composable per esdeveniments socket d'admin.
 * admin_join, admin:request_connected, admin:connected_users, admin_action.
 */
var connectJoinRegistrat = false;

export function useAdminSocket() {
  var nuxtApp = useNuxtApp();
  var socket = nuxtApp.$socket || null;

  function adminJoin() {
    if (socket && typeof socket.emit === "function" && socket.connected) {
      socket.emit("admin_join", {});
    }
  }

  /**
   * Uneix a admins_broadcast quan el socket està connectat (i en cada reconnect).
   */
  function ensureAdminJoin() {
    if (!socket || typeof socket.emit !== "function") {
      return;
    }
    adminJoin();
    if (!connectJoinRegistrat && typeof socket.on === "function") {
      connectJoinRegistrat = true;
      socket.on("connect", adminJoin);
    }
  }

  function onReportUpdated(callback) {
    if (socket && typeof socket.on === "function" && typeof callback === "function") {
      socket.on("admin_report_updated", callback);
    }
  }

  function offReportUpdated(callback) {
    if (socket && typeof socket.off === "function" && typeof callback === "function") {
      socket.off("admin_report_updated", callback);
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
    ensureAdminJoin: ensureAdminJoin,
    onReportUpdated: onReportUpdated,
    offReportUpdated: offReportUpdated,
    requestConnected: requestConnected,
    onConnectedUsers: onConnectedUsers,
    onActionConfirmed: onActionConfirmed,
    emitAction: emitAction
  };
}
