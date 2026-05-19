<template>
  <div v-if="show" class="confirm-overlay" @click.self="$emit('cancel')">
    <div class="confirm-sheet">
      <!-- Handle -->
      <div class="confirm-handle-row">
        <div class="confirm-handle"></div>
      </div>

      <div class="confirm-body">
        <!-- Icon d'advertència -->
        <div class="confirm-icon-row">
          <div class="confirm-icon-circle">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
          </div>
        </div>

        <h2 class="confirm-title">
          {{ title || 'Estàs segur?' }}
        </h2>
        <p class="confirm-message">
          {{ message || 'Aquesta acció no es pot desfer.' }}
        </p>

        <div class="confirm-buttons">
          <button
            type="button"
            class="confirm-btn confirm-btn--cancel"
            @click="$emit('cancel')"
          >
            Enrere
          </button>
          <button
            type="button"
            class="confirm-btn confirm-btn--confirm"
            @click="$emit('confirm')"
          >
            {{ confirmText || 'Confirmar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ConfirmModal",
  props: {
    show: { type: Boolean, required: true },
    title: { type: String, default: null },
    message: { type: String, default: null },
    confirmText: { type: String, default: null }
  },
  emits: ["confirm", "cancel"]
};
</script>

<style>
/* NO scoped — so it works inside Teleport */
.confirm-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  background: rgba(0, 0, 0, 0.5);
  pointer-events: auto;
}

.confirm-sheet {
  position: relative;
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
  border-radius: 32px 32px 0 0;
  overflow: hidden;
  background-color: #FF8DA6;
  display: flex;
  flex-direction: column;
  box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
  animation: confirm-slide-up 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.confirm-handle-row {
  width: 100%;
  display: flex;
  justify-content: center;
  padding-top: 16px;
  padding-bottom: 8px;
}

.confirm-handle {
  width: 48px;
  height: 6px;
  background: rgba(255, 255, 255, 0.4);
  border-radius: 999px;
}

.confirm-body {
  padding: 8px 24px 24px;
}

.confirm-icon-row {
  display: flex;
  justify-content: center;
  margin-bottom: 12px;
}

.confirm-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-title {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 8px;
  text-align: center;
}

.confirm-message {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.8);
  text-align: center;
  margin: 0 0 20px;
}

.confirm-buttons {
  display: flex;
  width: 100%;
  gap: 16px;
  align-items: stretch;
}

.confirm-btn {
  flex: 1;
  border: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 700;
  font-size: 14px;
  padding: 12px 0;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.confirm-btn--cancel {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  box-shadow: 0 4px 0 rgba(255, 255, 255, 0.15);
}

.confirm-btn--cancel:hover {
  background: rgba(255, 255, 255, 0.3);
}

.confirm-btn--cancel:active {
  transform: translateY(4px);
  box-shadow: none;
}

.confirm-btn--confirm {
  background: #FFD166;
  color: #1a1a2e;
  box-shadow: 0 4px 0 #d9a738;
}

.confirm-btn--confirm:hover {
  background: #ffc233;
}

.confirm-btn--confirm:active {
  transform: translateY(4px);
  box-shadow: none;
}

@keyframes confirm-slide-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
</style>
