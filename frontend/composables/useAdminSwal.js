/**
 * Alertes del panell admin via modals Loopy (fulla rosa).
 */
import { useLoopyModal } from "~/composables/useLoopyModal.js";

export function useAdminSwal() {
  var modal = useLoopyModal();

  function adminSuccess(title, text) {
    return modal.success(title, text);
  }

  function adminError(title, text) {
    return modal.error(title, text);
  }

  function adminWarning(title, text) {
    return modal.warning(title, text);
  }

  function adminConfirm(options) {
    var opts = typeof options === "string" ? { title: options } : (options || {});
    return modal.confirm({
      title: opts.title || "Confirmar",
      message: opts.text,
      confirmText: opts.confirmText || "Sí",
      cancelText: opts.cancelText || "Cancel·lar",
    }).then(function (confirmed) {
      return { isConfirmed: confirmed, isDismissed: !confirmed };
    });
  }

  return {
    adminSuccess: adminSuccess,
    adminError: adminError,
    adminWarning: adminWarning,
    adminConfirm: adminConfirm,
  };
}
