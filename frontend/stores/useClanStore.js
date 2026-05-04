import { defineStore } from "pinia";
import { authFetch } from "../utils/authFetch";

export var useClanStore = defineStore("clan", {
    state: function () {
        return {
            clans: [],
            currentClan: null,
            myClans: [],
            clanMembers: [],
            pendingRequests: [],
            loading: false,
            error: null
        };
    },

    getters: {
        //
    },

    actions: {
        fetchClans: async function (search) {
            this.loading = true;
            this.error = null;
            var query = search ? "?search=" + encodeURIComponent(search) : "";
            try {
                var resposta = await authFetch("/api/clans" + query, {
                    method: "GET"
                });
                if (!resposta.ok) throw new Error("Error fetching clans");
                var data = await resposta.json();
                this.clans = data.data || data;
                return this.clans;
            } catch (e) {
                this.error = e.message;
                return [];
            } finally {
                this.loading = false;
            }
        },

        getMyClan: async function () {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/me", {
                    method: "GET"
                });
                if (!resposta.ok) throw new Error("Error fetching my clan");
                var data = await resposta.json();
                this.currentClan = data.clan;
                return data.clan;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        getClan: async function (id) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + id, {
                    method: "GET"
                });
                if (!resposta.ok) throw new Error("Error fetching clan metadata");
                var data = await resposta.json();
                this.currentClan = data.data || data;
                return this.currentClan;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        createClan: async function (clanData) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans", {
                    method: "POST",
                    body: JSON.stringify(clanData)
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.message || "Error creating clan");
                }
                var data = await resposta.json();
                this.clans.push(data.clan || data.data || data);
                return data;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        updateClan: async function (id, clanData) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + id, {
                    method: "PUT",
                    body: JSON.stringify(clanData)
                });
                if (!resposta.ok) throw new Error("Error updating clan");
                var data = await resposta.json();
                if (this.currentClan && this.currentClan.id === id) {
                    this.currentClan = data.data || data;
                }
                return data;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        fetchMembers: async function (id) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + id + "/members", {
                    method: "GET"
                });
                if (!resposta.ok) throw new Error("Error fetching members");
                var data = await resposta.json();
                this.clanMembers = data.data || data;
                return this.clanMembers;
            } catch (e) {
                this.error = e.message;
                return [];
            } finally {
                this.loading = false;
            }
        },

        removeMember: async function (clanId, userId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/members/" + userId, {
                    method: "DELETE"
                });
                if (!resposta.ok) throw new Error("Error removing member");
                // Also need to remove it from state if successful
                var i;
                for (i = 0; i < this.clanMembers.length; i++) {
                    if (this.clanMembers[i].id == userId || this.clanMembers[i].usuari_id == userId) {
                        this.clanMembers.splice(i, 1);
                        break;
                    }
                }
                return true;
            } catch (e) {
                this.error = e.message;
                return false;
            } finally {
                this.loading = false;
            }
        },

        joinPublic: async function (clanId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/join", {
                    method: "POST"
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.error || errResponse.message || "Error joining clan");
                }
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        requestJoin: async function (clanId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/request", {
                    method: "POST"
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.message || "Error requesting to join clan");
                }
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        fetchPendingRequests: async function (clanId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/requests", {
                    method: "GET"
                });
                if (!resposta.ok) throw new Error("Error fetching pending requests");
                var data = await resposta.json();
                this.pendingRequests = data.data || data;
                return this.pendingRequests;
            } catch (e) {
                this.error = e.message;
                return [];
            } finally {
                this.loading = false;
            }
        },

        acceptRequest: async function (requestId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clan-requests/" + requestId + "/accept", {
                    method: "POST"
                });
                if (!resposta.ok) throw new Error("Error accepting request");
                var i;
                for (i = 0; i < this.pendingRequests.length; i++) {
                    if (this.pendingRequests[i].id == requestId) {
                        this.pendingRequests.splice(i, 1);
                        break;
                    }
                }
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        rejectRequest: async function (requestId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clan-requests/" + requestId + "/reject", {
                    method: "POST"
                });
                if (!resposta.ok) throw new Error("Error rejecting request");
                var i;
                for (i = 0; i < this.pendingRequests.length; i++) {
                    if (this.pendingRequests[i].id == requestId) {
                        this.pendingRequests.splice(i, 1);
                        break;
                    }
                }
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        leaveClan: async function (clanId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/leave", {
                    method: "POST"
                });
                if (!resposta.ok) throw new Error("Error leaving clan");
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        inviteUser: async function (clanId, userId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/invite", {
                    method: "POST",
                    body: JSON.stringify({ user_id: userId })
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.error || errResponse.message || "Error al convidar");
                }
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        acceptInvitation: async function (invitationId) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clan-invitations/" + invitationId + "/accept", {
                    method: "PUT"
                });
                if (!resposta.ok) throw new Error("Error accepting invitation");
                return await resposta.json();
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        }
    }
});
