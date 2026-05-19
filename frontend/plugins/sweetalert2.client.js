/**
 * Modals Loopy (fulla rosa) + adaptador $swal compatible.
 * Substituïx alert()/confirm() del navegador i SweetAlert per defecte.
 */

import Swal from "sweetalert2";
import { useLoopyModal, createLoopySwalAdapter } from "~/composables/useLoopyModal.js";

export default defineNuxtPlugin(function (nuxtApp) {
  var loopyModal = useLoopyModal();
  var swalAdapter = createLoopySwalAdapter();

  // Nuxt 3 ja exposa provide() com a $loopyModal / $swal (getter).
  // No assignar globalProperties manualment: provoca "only has a getter".
  nuxtApp.provide("loopyModal", loopyModal);
  nuxtApp.provide("swal", swalAdapter);
  nuxtApp.provide("swalNative", Swal);
});
