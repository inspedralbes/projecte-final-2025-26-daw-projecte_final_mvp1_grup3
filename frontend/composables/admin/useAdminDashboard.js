/**
 * Composable per carregar dades del dashboard admin.
 * Dashboard, rankings, usuaris recents.
 */
import { useAuthFetch } from "~/composables/useApi.js";

export function useAdminDashboard() {
  var { data: statsData, refresh: refreshStats } = useAuthFetch("/api/admin/dashboard", {
    key: "admin_dashboard_stats"
  });

  var { data: rankingsData, refresh: refreshRankings } = useAuthFetch("/api/admin/rankings/mensual", {
    key: "admin_rankings"
  });

  var { data: usuarisData, refresh: refreshUsuaris } = useAuthFetch("/api/admin/usuaris/tots/1/4/false/none", {
    key: "admin_usuaris_recents"
  });

  var stats = computed(function () {
    if (statsData.value && statsData.value.success) {
      return statsData.value.data;
    }
    return { totalUsuaris: 0, totalHabits: 0, connectats: 0, prohibits: 0, logrosActius: 0 };
  });

  var rankings = computed(function () {
    if (rankingsData.value && rankingsData.value.success) {
      return rankingsData.value.data;
    }
    return [];
  });

  var usuaris = computed(function () {
    if (usuarisData.value && usuarisData.value.success && usuarisData.value.data && usuarisData.value.data.data) {
      return usuarisData.value.data.data;
    }
    return [];
  });

  return {
    statsData: statsData,
    rankingsData: rankingsData,
    usuarisData: usuarisData,
    stats: stats,
    rankings: rankings,
    usuaris: usuaris,
    refreshStats: refreshStats,
    refreshRankings: refreshRankings,
    refreshUsuaris: refreshUsuaris
  };
}
