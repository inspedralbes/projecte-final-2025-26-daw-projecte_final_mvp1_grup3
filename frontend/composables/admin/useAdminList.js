/**
 * Composable genèric per llistats paginats d'admin.
 * Usuaris, habits, plantilles, logros, missions, notificacions.
 */
import { useAuthFetch } from "~/composables/useApi.js";

/**
 * @param {string} endpoint - Ex: '/api/admin/usuaris/tots', '/api/admin/habits', etc.
 * @param {Object} opts - { key, page, perPage, ... }
 */
export function useAdminList(endpoint, opts) {
  var options = opts || {};
  var page = options.page !== undefined ? options.page : 1;
  var perPage = options.perPage !== undefined ? options.perPage : 50;
  var key = options.key || "admin_list_" + endpoint.replace(/\//g, "_");

  var url = endpoint;
  if (typeof endpoint === "function") {
    url = endpoint();
  }

  var { data, refresh, pending, error } = useAuthFetch(url, {
    key: key,
    watch: options.watch || []
  });

  var items = computed(function () {
    if (data.value && data.value.success && data.value.data) {
      var d = data.value.data;
      return Array.isArray(d) ? d : (d.data || []);
    }
    return [];
  });

  var total = computed(function () {
    if (data.value && data.value.success && data.value.data && !Array.isArray(data.value.data)) {
      return data.value.data.total || 0;
    }
    return 0;
  });

  return {
    data: data,
    items: items,
    total: total,
    refresh: refresh,
    pending: pending,
    error: error
  };
}
