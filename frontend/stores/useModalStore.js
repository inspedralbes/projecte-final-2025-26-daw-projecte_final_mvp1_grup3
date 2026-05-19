import { defineStore } from "pinia";

/**
 * Estat global dels modals tipus fulla rosa (#FF8DA6), com ConfirmModal / ReportUserModal.
 */
export var useModalStore = defineStore("loopyModal", {
  state: function () {
    return {
      alert: {
        visible: false,
        title: "",
        message: "",
        html: "",
        type: "info",
        confirmText: "D'acord",
        timer: null,
        _resolve: null,
        _timerId: null,
      },
      confirm: {
        visible: false,
        title: "",
        message: "",
        confirmText: "Confirmar",
        cancelText: "Enrere",
        _resolve: null,
      },
    };
  },

  actions: {
    openAlert: function (opts) {
      var self = this;
      var options = opts || {};
      if (typeof options === "string") {
        options = { message: options };
      }

      if (self.alert._timerId) {
        clearTimeout(self.alert._timerId);
        self.alert._timerId = null;
      }

      return new Promise(function (resolve) {
        self.alert = {
          visible: true,
          title: options.title || "",
          message: options.message || options.text || "",
          html: options.html || "",
          type: options.type || options.icon || "info",
          confirmText: options.confirmText || "D'acord",
          timer: options.timer || null,
          _resolve: resolve,
          _timerId: null,
        };

        if (options.timer && options.timer > 0) {
          self.alert._timerId = setTimeout(function () {
            self.closeAlert();
          }, options.timer);
        }
      });
    },

    closeAlert: function () {
      var resolve = this.alert._resolve;
      if (this.alert._timerId) {
        clearTimeout(this.alert._timerId);
      }
      this.alert.visible = false;
      this.alert._resolve = null;
      this.alert._timerId = null;
      if (resolve) {
        resolve({ isConfirmed: true });
      }
    },

    openConfirm: function (opts) {
      var self = this;
      var options = opts || {};
      if (typeof options === "string") {
        options = { message: options };
      }

      return new Promise(function (resolve) {
        self.confirm = {
          visible: true,
          title: options.title || "Estàs segur?",
          message: options.message || options.text || "",
          confirmText: options.confirmText || options.confirmButtonText || "Confirmar",
          cancelText: options.cancelText || options.cancelButtonText || "Enrere",
          _resolve: resolve,
        };
      });
    },

    resolveConfirm: function (confirmed) {
      var resolve = this.confirm._resolve;
      this.confirm.visible = false;
      this.confirm._resolve = null;
      if (resolve) {
        resolve(!!confirmed);
      }
    },
  },
});
