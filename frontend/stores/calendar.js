/**
 * Modul JavaScript ES5: calendar.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { defineStore } from "pinia";
import { authFetch, getBaseUrl } from "~/composables/useApi.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

/**
 * Store del calendari (Arxiu d'Aventures).
 * Gestiona la càrrega i cache de snapshots diaris i resums mensuals.
 */
export var useCalendarStore = defineStore("calendar", {
  state: function () {
    return {
      selectedDate: null,
      selectedMonth: new Date().getMonth() + 1,
      selectedYear: new Date().getFullYear(),
      selectedWeekStart: null,
      snapshotCache: {},
      monthSummaryCache: {},
      categoryFilter: null,
      loading: false,
      error: null
    };
  },

  actions: {
    _getUsuariId: function () {
      var authStore = useAuthStore();
      return (authStore.user && authStore.user.id) ? authStore.user.id : 1;
    },

    fetchMonthSummary: async function (year, month) {
      var cacheKey = year + "-" + String(month).padStart(2, "0");
      if (this.monthSummaryCache[cacheKey]) {
        return this.monthSummaryCache[cacheKey];
      }

      this.loading = true;
      this.error = null;
      try {
        var baseUrl = getBaseUrl();
        var usuariId = this._getUsuariId();
        var resposta = await authFetch(baseUrl + "/api/calendar/month/" + usuariId + "/" + year + "/" + month);
        if (resposta && resposta.ok) {
          var dades = await resposta.json();
          this.monthSummaryCache[cacheKey] = dades;
          return dades;
        }
        return [];
      } catch (e) {
        this.error = e.message || "Error carregant resum mensual";
        return [];
      } finally {
        this.loading = false;
      }
    },

    fetchDaySnapshot: async function (date) {
      if (this.snapshotCache[date]) {
        return this.snapshotCache[date];
      }

      this.loading = true;
      this.error = null;
      try {
        var baseUrl = getBaseUrl();
        var usuariId = this._getUsuariId();
        var resposta = await authFetch(baseUrl + "/api/calendar/snapshot/" + usuariId + "/" + date);
        if (resposta && resposta.ok) {
          var dades = await resposta.json();
          this.snapshotCache[date] = dades;
          return dades;
        }
        return null;
      } catch (e) {
        this.error = e.message || "Error carregant snapshot";
        return null;
      } finally {
        this.loading = false;
      }
    },

    setFilter: function (categoryId) {
      this.categoryFilter = categoryId;
    },

    clearFilter: function () {
      this.categoryFilter = null;
    }
  }
});
