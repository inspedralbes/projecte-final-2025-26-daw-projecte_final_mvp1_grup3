import { defineStore } from "pinia";
import { authFetch } from "../utils/authFetch.js";

export var useChatStore = defineStore("chat", {
  state: function () {
    return {
      messages: {},
      loading: false,
      error: null,
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
  },
});