<!--
  Host global: modals tipus fulla rosa (#FF8DA6) per alertes i confirmacions.
-->
<template>
  <Teleport to="body">
    <!-- Alerta (un botó) -->
    <div
      v-if="alert.visible"
      class="loopy-modal-overlay"
      @click.self="tancarAlert"
    >
      <div class="loopy-modal-sheet" role="dialog" aria-modal="true">
        <div class="loopy-modal-handle-row">
          <div class="loopy-modal-handle" />
        </div>
        <div class="loopy-modal-body">
          <div class="loopy-modal-icon-row">
            <div class="loopy-modal-icon-circle" :class="'loopy-modal-icon-circle--' + alert.type">
              <svg
                v-if="alert.type === 'success'"
                width="28"
                height="28"
                viewBox="0 0 24 24"
                fill="none"
                stroke="white"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M20 6L9 17l-5-5" />
              </svg>
              <svg
                v-else-if="alert.type === 'error'"
                width="28"
                height="28"
                viewBox="0 0 24 24"
                fill="none"
                stroke="white"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="12" cy="12" r="10" />
                <line x1="15" y1="9" x2="9" y2="15" />
                <line x1="9" y1="9" x2="15" y2="15" />
              </svg>
              <svg
                v-else
                width="28"
                height="28"
                viewBox="0 0 24 24"
                fill="none"
                stroke="white"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
            </div>
          </div>
          <h2 v-if="alert.title" class="loopy-modal-title">
            {{ alert.title }}
          </h2>
          <p v-if="alert.message && !alert.html" class="loopy-modal-message">
            {{ alert.message }}
          </p>
          <div
            v-if="alert.html"
            class="loopy-modal-html"
            v-html="alert.html"
          />
          <div class="loopy-modal-actions loopy-modal-actions--single">
            <button type="button" class="loopy-modal-btn loopy-modal-btn--primary" @click="tancarAlert">
              {{ alert.confirmText }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmació (dos botons) -->
    <div
      v-if="confirm.visible"
      class="loopy-modal-overlay"
      @click.self="cancelConfirm"
    >
      <div class="loopy-modal-sheet" role="dialog" aria-modal="true">
        <div class="loopy-modal-handle-row">
          <div class="loopy-modal-handle" />
        </div>
        <div class="loopy-modal-body">
          <div class="loopy-modal-icon-row">
            <div class="loopy-modal-icon-circle loopy-modal-icon-circle--warning">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
            </div>
          </div>
          <h2 class="loopy-modal-title">
            {{ confirm.title }}
          </h2>
          <p v-if="confirm.message" class="loopy-modal-message">
            {{ confirm.message }}
          </p>
          <div class="loopy-modal-actions">
            <button type="button" class="loopy-modal-btn loopy-modal-btn--ghost" @click="cancelConfirm">
              {{ confirm.cancelText }}
            </button>
            <button type="button" class="loopy-modal-btn loopy-modal-btn--primary" @click="acceptConfirm">
              {{ confirm.confirmText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { useModalStore } from "~/stores/useModalStore.js";

var modalStore = useModalStore();
var refs = storeToRefs(modalStore);
var alert = refs.alert;
var confirm = refs.confirm;

function tancarAlert() {
  modalStore.closeAlert();
}

function acceptConfirm() {
  modalStore.resolveConfirm(true);
}

function cancelConfirm() {
  modalStore.resolveConfirm(false);
}
</script>

<style>
.loopy-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 10050;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  background: rgba(0, 0, 0, 0.5);
  pointer-events: auto;
}

.loopy-modal-sheet {
  position: relative;
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
  border-radius: 32px 32px 0 0;
  overflow: hidden;
  background-color: #ff8da6;
  display: flex;
  flex-direction: column;
  box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
  animation: loopy-modal-slide-up 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  max-height: 85vh;
}

.loopy-modal-handle-row {
  width: 100%;
  display: flex;
  justify-content: center;
  padding-top: 16px;
  padding-bottom: 8px;
}

.loopy-modal-handle {
  width: 48px;
  height: 6px;
  background: rgba(255, 255, 255, 0.4);
  border-radius: 999px;
}

.loopy-modal-body {
  padding: 8px 24px 28px;
  overflow-y: auto;
}

.loopy-modal-icon-row {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
}

.loopy-modal-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.loopy-modal-icon-circle--success {
  background: rgba(121, 212, 93, 0.35);
}

.loopy-modal-icon-circle--error {
  background: rgba(255, 107, 138, 0.45);
}

.loopy-modal-title {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 8px;
  text-align: center;
}

.loopy-modal-message {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.85);
  text-align: center;
  margin: 0 0 20px;
  line-height: 1.5;
}

.loopy-modal-html {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.95);
  margin: 0 0 20px;
  max-height: 50vh;
  overflow-y: auto;
}

.loopy-modal-html :deep(p),
.loopy-modal-html :deep(li),
.loopy-modal-html :deep(span) {
  color: rgba(255, 255, 255, 0.9);
}

.loopy-modal-actions {
  display: flex;
  width: 100%;
  gap: 16px;
  align-items: stretch;
}

.loopy-modal-actions--single .loopy-modal-btn {
  flex: 1;
}

.loopy-modal-btn {
  flex: 1;
  border: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 700;
  font-size: 14px;
  padding: 12px 0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.loopy-modal-btn--ghost {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  box-shadow: 0 4px 0 rgba(255, 255, 255, 0.15);
}

.loopy-modal-btn--ghost:active {
  transform: translateY(4px);
  box-shadow: none;
}

.loopy-modal-btn--primary {
  background: #ffd166;
  color: #1a1a2e;
  box-shadow: 0 4px 0 #d9a738;
}

.loopy-modal-btn--primary:active {
  transform: translateY(4px);
  box-shadow: none;
}

@keyframes loopy-modal-slide-up {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}
</style>
