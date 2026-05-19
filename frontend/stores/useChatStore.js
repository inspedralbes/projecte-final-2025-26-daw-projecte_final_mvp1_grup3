/**
 * Modul JavaScript ES5: useChatStore.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { defineStore } from "pinia";
import { authFetch } from "../utils/authFetch.js";

export var useChatStore = defineStore("chat", {
  state: function () {
    return {
      messages: {},
      loading: false,
      error: null,
      onlineUsers: [],
    };
  },

  actions: {
    fetchChatHistory: async function (friendId) {
      this.loading = true;
      this.error = null;
      try {
        var resposta = await authFetch("/api/chat/" + friendId, {});
        if (!resposta.ok) throw new Error("Error en obtenir historial");
        var dades = await resposta.json();
        var msgs = dades.data || dades || [];
        this.messages[friendId] = msgs;
        return msgs;
      } catch (e) {
        this.error = e.message;
        this.messages[friendId] = [];
        return [];
      } finally {
        this.loading = false;
      }
    },
    setOnlineUsers: function (users) {
      this.onlineUsers = users;
    },
    updateUserStatus: function (userId, online) {
      // Ens assegurem que userId sigui int
      userId = parseInt(userId);
      var index = this.onlineUsers.indexOf(userId);
      if (online && index === -1) {
        this.onlineUsers.push(userId);
      } else if (!online && index !== -1) {
        this.onlineUsers.splice(index, 1);
      }
    },
  },
});