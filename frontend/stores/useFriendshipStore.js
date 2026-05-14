import { defineStore } from "pinia";
import { authFetch } from "~/composables/useApi.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

function dedupeById(items) {
  var seen = {};
  var result = [];
  for (var i = 0; i < (items ? items.length : 0); i++) {
    var item = items[i];
    if (!item || item.id == null) continue;
    if (!seen[item.id]) {
      seen[item.id] = true;
      result.push(item);
    }
  }
  return result;
}

export var useFriendshipStore = defineStore("friendship", {
  state: function () {
    return {
      friends: [],
      pendingRequests: [],
      loading: false,
      error: null,
      friendsPage: 1,
      friendsLastPage: 1,
      friendsTotal: 0,
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
        var authStore = useAuthStore();
        var nuxtApp = useNuxtApp();
        var socket = nuxtApp.$socket;
        if (socket) {
          socket.emit("friend_request_notify", {
            addressee_id: addresseeId,
            requester_name: authStore.user?.nom || "Usuari",
          });
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
        var friendship = this.pendingRequests.find(function(r) { return r.id === friendshipId; });
        var authStore = useAuthStore();
        var nuxtApp = useNuxtApp();
        var socket = nuxtApp.$socket;
        if (friendship && friendship.requester && socket) {
          socket.emit("friend_request_accepted_notify", {
            requester_id: friendship.requester.id,
            acceptor_id: authStore.user?.id,
            acceptor_name: authStore.user?.nom || "Usuari",
          });
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

    fetchFriendsList: async function (page) {
      this.loading = true;
      this.error = null;

      try {
        var url = "/api/friends";
        if (page) {
          url += "?page=" + page;
        }
        var resposta = await authFetch(url, {});
        if (!resposta.ok) {
          throw new Error("Error en obtenir llista d amics");
        }
        var dades = await resposta.json();
        this.friends = dedupeById(dades.data || dades || []);
        this.friendsPage = dades.current_page || 1;
        this.friendsLastPage = dades.last_page || 1;
        this.friendsTotal = dades.total || 0;
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
        this.pendingRequests = dedupeById(dades.data || dades || []);
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
