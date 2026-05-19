/**
 * Alertes modal centradas per al panell admin (SweetAlert2 estil Loopy).
 * Evita alert()/confirm() del navegador i toasts superiors.
 */
export function useAdminSwal() {
  var nuxtApp = useNuxtApp();
  var swal = nuxtApp.$swal;

  var customClass = {
    popup: 'loopy-swal-popup loopy-admin-swal-popup',
    title: 'loopy-swal-title loopy-admin-swal-title',
    htmlContainer: 'loopy-admin-swal-text',
    confirmButton: 'loopy-swal-confirm loopy-admin-swal-confirm',
    cancelButton: 'loopy-swal-cancel loopy-admin-swal-cancel'
  };

  var base = {
    toast: false,
    position: 'center',
    buttonsStyling: false,
    customClass: customClass,
    backdrop: 'rgba(15, 23, 42, 0.55)',
    allowOutsideClick: true,
    heightAuto: false
  };

  function adminSuccess(title, text) {
    if (!swal) {
      return Promise.resolve();
    }
    return swal.fire(Object.assign({}, base, {
      icon: 'success',
      title: title,
      text: text || undefined,
      showConfirmButton: true,
      confirmButtonText: "D'acord",
      timer: text ? undefined : 2200,
      timerProgressBar: !text
    }));
  }

  function adminError(title, text) {
    if (!swal) {
      return Promise.resolve();
    }
    return swal.fire(Object.assign({}, base, {
      icon: 'error',
      title: title,
      text: text || undefined,
      showConfirmButton: true,
      confirmButtonText: "D'acord"
    }));
  }

  function adminWarning(title, text) {
    if (!swal) {
      return Promise.resolve();
    }
    return swal.fire(Object.assign({}, base, {
      icon: 'warning',
      title: title,
      text: text || undefined,
      showConfirmButton: true,
      confirmButtonText: "D'acord"
    }));
  }

  function adminConfirm(options) {
    if (!swal) {
      return Promise.resolve({ isConfirmed: false });
    }
    var opts = typeof options === 'string' ? { title: options } : (options || {});
    return swal.fire(Object.assign({}, base, {
      icon: opts.icon || 'question',
      title: opts.title || 'Confirmar',
      text: opts.text,
      showCancelButton: true,
      confirmButtonText: opts.confirmText || 'Sí',
      cancelButtonText: opts.cancelText || 'Cancel·lar',
      reverseButtons: true
    }));
  }

  return {
    adminSuccess: adminSuccess,
    adminError: adminError,
    adminWarning: adminWarning,
    adminConfirm: adminConfirm
  };
}
