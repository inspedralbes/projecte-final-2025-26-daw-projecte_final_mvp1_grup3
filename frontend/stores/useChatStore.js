import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";

export var useChatStore = defineStore("chat", {
  state: function () {
    return {
      messages: {},
      loading: false,
      error: null,
    };
  },
  actions: {
    sendMessage: async function (receiverId, content) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/chat/" + receiverId, {
          method: "POST",
          body: JSON.stringify({ contingut: content }),
        });
        if (!resposta.ok) {
          var errorData = await resposta.json();
          throw new Error(errorData.error || "Error en enviar missatge");
        }
        var message = await resposta.json();
        this.addMessage(receiverId, message);
        return message;
      } catch (e) {
        this.error = e.message;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    fetchChatHistory: async function (friendId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/chat/" + friendId, {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir historial");
        }
        var dades = await resposta.json();
        this.messages[friendId] = dades.data || dades || [];
        return this.messages[friendId];
      } catch (e) {
        this.error = e.message;
        this.messages[friendId] = [];
        return [];
      } finally {
        this.loading = false;
      }
    },

    markAsRead: async function (friendId) {
      try {
        var resposta = await authFetch("/api/chat/" + friendId + "/read", {
          method: "PUT",
        });
        if (!resposta.ok) {
          console.error("Error en marcar com a llegit");
        }
      } catch (e) {
        console.error("Error en marcar com a llegit:", e);
      }
    },

    addMessage: function (friendId, message) {
      if (!this.messages[friendId]) {
        this.messages[friendId] = [];
      }
      this.messages[friendId].unshift(message);
    },

    receiveMessage: function (friendId, message) {
      if (!this.messages[friendId]) {
        this.messages[friendId] = [];
      }
      this.messages[friendId].push(message);
    },
  },
});