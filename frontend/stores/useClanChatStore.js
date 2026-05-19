/**
 * Modul JavaScript ES5: useClanChatStore.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { defineStore } from "pinia";
import { authFetch } from "../utils/authFetch";

export var useClanChatStore = defineStore("clanChat", {
    state: function () {
        return {
            messages: [],
            loading: false,
            error: null
        };
    },

    actions: {
        fetchMessages: async function (clanId, page) {
            if (!page) page = 1;
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/" + clanId + "/messages?page=" + page, {
                    method: "GET"
                });
                if (!resposta.ok) throw new Error("Error fetching chat messages");
                var data = await resposta.json();

                if (page === 1) {
                    this.messages = data.data || data;
                    // Most recent first or bottom up? Assuming normal pagination where newest is at bottom depending on UI we may reverse.
                    // A typical chat UI will fetch recent and prepend. If data returns newest first, reverse them.
                    if (Array.isArray(this.messages)) {
                        this.messages.reverse();
                    }
                } else {
                    var olderMessages = data.data || data;
                    if (Array.isArray(olderMessages)) {
                        olderMessages.reverse();
                    }
                    this.messages = olderMessages.concat(this.messages);
                }

                return data;
            } catch (e) {
                this.error = e.message;
                return { data: [] };
            } finally {
                this.loading = false;
            }
        },

        sendMessage: async function (clanId, contingut, habitId, plantillaId) {
            this.loading = true;
            this.error = null;
            try {
                var bodyData = { contingut: contingut };
                if (habitId) bodyData.habit_id = habitId;
                if (plantillaId) bodyData.plantilla_id = plantillaId;

                var resposta = await authFetch("/api/clans/" + clanId + "/messages", {
                    method: "POST",
                    body: JSON.stringify(bodyData)
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.message || "Error sending message");
                }
                var data = await resposta.json();
                return data.message || data.data || data;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        shareHabit: async function (clanId, habitId) {
            return await this.sendMessage(clanId, "He compartit un hàbit!", habitId, null);
        },

        sharePlantilla: async function (clanId, plantillaId) {
            return await this.sendMessage(clanId, "He compartit una plantilla!", null, plantillaId);
        },

        importHabit: async function (messageId, diesSetmana) {
            this.loading = true;
            this.error = null;
            try {
                var resposta = await authFetch("/api/clans/messages/" + messageId + "/import-habit", {
                    method: "POST",
                    body: JSON.stringify({
                        dies_setmana: diesSetmana || ["dl", "dm", "dc", "dj", "dv", "ds", "dg"]
                    })
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.message || "Error al importar hàbit");
                }
                var result = await resposta.json();
                return result;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        importPlantilla: async function (messageId, habitIds) {
            this.loading = true;
            this.error = null;
            try {
                var bodyData = {};
                if (habitIds) bodyData.habit_ids = habitIds;
                var resposta = await authFetch("/api/clans/messages/" + messageId + "/import-plantilla", {
                    method: "POST",
                    body: JSON.stringify(bodyData)
                });
                if (!resposta.ok) {
                    var errResponse = await resposta.json();
                    throw new Error(errResponse.message || "Error al importar plantilla");
                }
                var result = await resposta.json();
                return result;
            } catch (e) {
                this.error = e.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        // For socket event
        handleNewMessage: function (message) {
            // Si el missatge ja existeix no l'afegim
            var i;
            for (i = 0; i < this.messages.length; i++) {
                if (this.messages[i].id == message.id) {
                    return;
                }
            }
            this.messages.push(message);
        }
    }
});
