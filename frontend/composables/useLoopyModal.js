import { useModalStore } from '~/stores/useModalStore.js';

/**
 * API unificada de modals rosa (fulla inferior), substitueix alert()/confirm()/$swal.
 */
export function useLoopyModal() {
  var store = useModalStore();

  function alert(opts) {
    return store.openAlert(opts);
  }

  function confirm(opts) {
    return store.openConfirm(opts);
  }

  function success(title, text) {
    return store.openAlert({
      title: title,
      message: text,
      type: "success",
    });
  }

  function error(title, text) {
    return store.openAlert({
      title: title || "Error",
      message: text,
      type: "error",
    });
  }

  function info(title, text) {
    return store.openAlert({
      title: title || "Atenció",
      message: text,
      type: "info",
    });
  }

  function warning(title, text) {
    return store.openAlert({
      title: title || "Atenció",
      message: text,
      type: "warning",
    });
  }

  return {
    alert: alert,
    confirm: confirm,
    success: success,
    error: error,
    info: info,
    warning: warning,
  };
}

/**
 * Adaptador compatible amb $swal.fire() → modals Loopy.
 */
export function createLoopySwalAdapter() {
  var modal = useLoopyModal();

  function fire(opts) {
    var options = opts || {};
    if (typeof options === "string") {
      options = { title: options };
    }

    if (options.showCancelButton) {
      return modal.confirm({
        title: options.title,
        message: options.text || options.html,
        confirmText: options.confirmButtonText,
        cancelText: options.cancelButtonText,
        icon: options.icon,
      }).then(function (confirmed) {
        return {
          isConfirmed: confirmed,
          isDismissed: !confirmed,
          value: confirmed,
        };
      });
    }

    return modal.alert({
      title: options.title,
      message: options.text,
      html: options.html,
      type: options.icon || "info",
      confirmText: options.confirmButtonText,
      timer: options.timer,
    }).then(function () {
      return { isConfirmed: true, isDismissed: false };
    });
  }

  return {
    fire: fire,
  };
}
