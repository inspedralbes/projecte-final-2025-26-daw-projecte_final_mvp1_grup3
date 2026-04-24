import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";

export var useFriendshipStore = defineStore("friendship", {
  state: function () {
    return {
      friends: [],
      pendingRequests: [],
      loading: false,
      error: null,
    };
  },
  actions: {
    sendFriendRequest: async function (addresseeId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/friends/request", {
          method: "POST",
          body: JSON.stringify({ addressee_id: addresseeId }),
        });
        if (!resposta.ok) {
          var errorData = await resposta.json();
          throw new Error(errorData.error || "Error en enviar sol·licitud");
        }
        return await resposta.json();
      } catch (e) {
        this.error = e.message;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    acceptFriendRequest: async function (friendshipId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/friends/accept/" + friendshipId, {
          method: "PUT",
        });
        if (!resposta.ok) {
          throw new Error("Error en acceptar sol·licitud");
        }
        await this.fetchPendingRequests();
        await this.fetchFriendsList();
        return await resposta.json();
      } catch (e) {
        this.error = e.message;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    rejectFriendRequest: async function (friendshipId) {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/friends/reject/" + friendshipId, {
          method: "PUT",
        });
        if (!resposta.ok) {
          throw new Error("Error en rebutjar sol·licitud");
        }
        await this.fetchPendingRequests();
        return await resposta.json();
      } catch (e) {
        this.error = e.message;
        throw e;
      } finally {
        this.loading = false;
      }
    },

    fetchFriendsList: async function () {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/friends", {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir llista d'amics");
        }
        var dades = await resposta.json();
        this.friends = dades.data || dades || [];
        return this.friends;
      } catch (e) {
        this.error = e.message;
        this.friends = [];
        return [];
      } finally {
        this.loading = false;
      }
    },

    fetchPendingRequests: async function () {
      this.loading = true;
      this.error = null;

      try {
        var resposta = await authFetch("/api/friends/pending", {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir sol·licituds pendents");
        }
        var dades = await resposta.json();
        this.pendingRequests = dades.data || dades || [];
        return this.pendingRequests;
      } catch (e) {
        this.error = e.message;
        this.pendingRequests = [];
        return [];
      } finally {
        this.loading = false;
      }
    },
  },
});