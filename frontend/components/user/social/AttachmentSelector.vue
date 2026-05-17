<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition name="attach-backdrop">
      <div
        v-if="show"
        class="attach-backdrop"
        @click="$emit('close')"
      ></div>
    </Transition>

    <!-- Bottom Sheet -->
    <Transition name="attach-sheet">
      <div
        v-if="show"
        class="attach-sheet"
      >
        <!-- Header -->
        <div class="attach-sheet__header">
          <h3 class="attach-sheet__title">Seleccionar Adjunt</h3>
          <button
            type="button"
            class="attach-sheet__close"
            aria-label="Tancar"
            @click="$emit('close')"
          >
            <span class="attach-sheet__close-line attach-sheet__close-line--1" aria-hidden="true"></span>
            <span class="attach-sheet__close-line attach-sheet__close-line--2" aria-hidden="true"></span>
          </button>
        </div>

        <!-- Body -->
        <div class="attach-sheet__body">

          <!-- Field 1: Tipus d'adjunt -->
          <div class="attach-field">
            <label class="attach-label" for="attach-type-select">Tipus d'adjunt</label>
            <div class="attach-select-wrap">
              <select
                id="attach-type-select"
                v-model="tipusSeleccionat"
                class="attach-select"
                @change="onTipusChange"
              >
                <option value="">Selecciona un tipus...</option>
                <option value="habit">Hàbits</option>
                <option value="plantilla">Plantilla</option>
              </select>
              <span class="attach-select-chevron" aria-hidden="true"></span>
            </div>
          </div>

          <!-- Field 2: Selecciona els adjunts -->
          <div class="attach-field">
            <label class="attach-label">Selecciona els adjunts</label>

            <!-- Dashed placeholder / trigger button (shown when list is collapsed) -->
            <button
              type="button"
              class="attach-trigger"
              :class="{ 'attach-trigger--active': listOpen }"
              :disabled="!tipusSeleccionat"
              @click="toggleList"
            >
              <span class="attach-trigger__icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </span>
              <span class="attach-trigger__text">
                <template v-if="!tipusSeleccionat">Primer selecciona un tipus</template>
                <template v-else-if="seleccionats.length === 0">
                  {{ tipusSeleccionat === 'habit' ? 'Afegir hàbits...' : 'Afegir plantilles...' }}
                </template>
                <template v-else>
                  {{ seleccionats.length }} {{ tipusSeleccionat === 'habit' ? (seleccionats.length === 1 ? 'hàbit seleccionat' : 'hàbits seleccionats') : (seleccionats.length === 1 ? 'plantilla seleccionada' : 'plantilles seleccionades') }}
                </template>
              </span>
            </button>

            <!-- Chips of selected items -->
            <div v-if="seleccionats.length > 0" class="attach-chips">
              <span
                v-for="id in seleccionats"
                :key="id"
                class="attach-chip"
              >
                {{ getNomItem(id) }}
                <button type="button" class="attach-chip__remove" @click="treureSelecci(id)" aria-label="Eliminar">×</button>
              </span>
            </div>

            <!-- Expandable list -->
            <div v-if="listOpen && tipusSeleccionat" class="attach-list-wrap">
              <div v-if="loading" class="attach-list-loading">
                <span class="attach-list-spinner"></span>
              </div>
              <div v-else-if="items.length === 0" class="attach-list-empty">
                {{ tipusSeleccionat === 'habit' ? 'No tens hàbits actius' : 'No tens plantilles' }}
              </div>
              <div v-else class="attach-list">
                <button
                  v-for="item in items"
                  :key="item.id"
                  type="button"
                  class="attach-list-item"
                  :class="{ 'attach-list-item--selected': seleccionats.indexOf(item.id) !== -1 }"
                  @click="toggleItem(item)"
                >
                  <div class="attach-list-item__check">
                    <SharedMissionStyleCheckIcon :selected="seleccionats.indexOf(item.id) !== -1" :size="28" />
                  </div>
                  <div class="attach-list-item__content">
                    <div class="attach-list-item__icon-blob" v-if="tipusSeleccionat === 'habit'">
                      <svg class="attach-list-item__blob-svg" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="item.color || '#79D45D'" />
                      </svg>
                      <span class="attach-list-item__emoji">{{ item.icona || '💧' }}</span>
                    </div>
                    <div class="attach-list-item__icon-blob" v-else>
                      <svg class="attach-list-item__blob-svg" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" fill="#94bef0" />
                      </svg>
                      <span class="attach-list-item__emoji">📋</span>
                    </div>
                    <span class="attach-list-item__name">{{ item.nom || item.titol }}</span>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="attach-actions">
            <button
              type="button"
              class="attach-btn attach-btn--cancel"
              @click="$emit('close')"
            >
              Enrere
            </button>
            <button
              type="button"
              class="attach-btn attach-btn--confirm"
              :disabled="seleccionats.length === 0"
              @click="confirmar"
            >
              Adjuntar{{ seleccionats.length > 0 ? ' (' + seleccionats.length + ')' : '' }}
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { useHabitStore } from "~/stores/useHabitStore.js";
import { usePlantillaStore } from "~/stores/usePlantillaStore.js";

export default {
  name: "AttachmentSelector",
  props: {
    show: { type: Boolean, default: false }
  },
  emits: ["close", "selected"],
  data: function () {
    return {
      tipusSeleccionat: "",
      seleccionats: [],
      loading: false,
      listOpen: false,
      habits: [],
      plantilles: []
    };
  },
  computed: {
    items: function () {
      return this.tipusSeleccionat === "habit" ? this.habits : this.plantilles;
    }
  },
  watch: {
    show: function (newVal) {
      if (newVal) {
        this.reset();
      }
    }
  },
  methods: {
    reset: function () {
      this.tipusSeleccionat = "";
      this.seleccionats = [];
      this.listOpen = false;
      this.habits = [];
      this.plantilles = [];
    },
    onTipusChange: function () {
      this.seleccionats = [];
      this.listOpen = false;
      this.habits = [];
      this.plantilles = [];
    },
    toggleList: async function () {
      if (!this.tipusSeleccionat) return;
      if (!this.listOpen) {
        await this.loadData();
      }
      this.listOpen = !this.listOpen;
    },
    loadData: async function () {
      this.loading = true;
      try {
        if (this.tipusSeleccionat === "habit") {
          var habitStore = useHabitStore();
          await habitStore.obtenirHabitsDesDeApi();
          this.habits = habitStore.habits;
        } else {
          var plantillaStore = usePlantillaStore();
          await plantillaStore.obtenirPlantillesDesDeApi("my");
          this.plantilles = plantillaStore.plantilles;
        }
      } catch (e) {
        console.error("Error loading attachments:", e);
      } finally {
        this.loading = false;
      }
    },
    toggleItem: function (item) {
      var idx = this.seleccionats.indexOf(item.id);
      if (idx === -1) {
        this.seleccionats = this.seleccionats.concat([item.id]);
      } else {
        var arr = this.seleccionats.slice();
        arr.splice(idx, 1);
        this.seleccionats = arr;
      }
    },
    treureSelecci: function (id) {
      var arr = this.seleccionats.slice();
      var idx = arr.indexOf(id);
      if (idx !== -1) {
        arr.splice(idx, 1);
        this.seleccionats = arr;
      }
    },
    getNomItem: function (id) {
      var item = this.items.find(function (i) { return i.id === id; });
      return item ? (item.nom || item.titol || id) : id;
    },
    confirmar: function () {
      var self = this;
      if (self.seleccionats.length === 0) return;

      // Emit each selected item
      self.seleccionats.forEach(function (id) {
        var item = self.items.find(function (i) { return i.id === id; });
        if (item) {
          self.$emit("selected", {
            type: self.tipusSeleccionat,
            id: item.id,
            titol: item.nom || item.titol
          });
        }
      });

      self.$emit("close");
    }
  }
};
</script>

<style scoped>
/* ── Backdrop ─────────────────────────────────────────────── */
.attach-backdrop {
  position: fixed;
  inset: 0;
  z-index: 80;
  background: rgba(0, 0, 0, 0.40);
}

/* ── Sheet ────────────────────────────────────────────────── */
.attach-sheet {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 81;
  background: #fff;
  border-radius: 24px 24px 0 0;
  box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.14);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
}

/* ── Header ───────────────────────────────────────────────── */
.attach-sheet__header {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #fff;
  border-radius: 24px 24px 0 0;
  padding: 14px 20px 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f0f0f0;
  flex-shrink: 0;
}

.attach-sheet__title {
  font-family: "Bricolage Grotesque", "Comfortaa", system-ui, sans-serif;
  font-size: 16px;
  font-weight: 800;
  color: #707070;
  letter-spacing: 0.01em;
  margin: 0;
  text-align: center;
  flex: 1;
}

.attach-sheet__close {
  width: 36px;
  height: 36px;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 0;
  flex-shrink: 0;
}

.attach-sheet__close-line {
  display: block;
  width: 22px;
  height: 2.5px;
  background: #b0b0b0;
  border-radius: 99px;
  transition: background 0.15s;
  transform-origin: center;
}

.attach-sheet__close-line--1 {
  transform: rotate(45deg) translate(3.5px, 3.5px);
}

.attach-sheet__close-line--2 {
  transform: rotate(-45deg) translate(3.5px, -3.5px);
}

.attach-sheet__close:hover .attach-sheet__close-line {
  background: #ff6b8a;
}

/* ── Body ─────────────────────────────────────────────────── */
.attach-sheet__body {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* ── Field ────────────────────────────────────────────────── */
.attach-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* ── Label ────────────────────────────────────────────────── */
.attach-label {
  font-family: "Bricolage Grotesque", "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 800;
  color: #2b2d42;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

/* ── Select ───────────────────────────────────────────────── */
.attach-select-wrap {
  position: relative;
}

.attach-select {
  width: 100%;
  appearance: none;
  -webkit-appearance: none;
  background: #f7f7f7;
  border: 2px solid #e8e8e8;
  border-radius: 14px;
  padding: 12px 40px 12px 16px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  color: #2b2d42;
  outline: none;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.attach-select:focus {
  border-color: #79D45D;
  box-shadow: 0 0 0 3px rgba(121, 212, 93, 0.15);
}

.attach-select-chevron {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  pointer-events: none;
}

.attach-select-chevron::before,
.attach-select-chevron::after {
  content: "";
  display: block;
  position: absolute;
  width: 8px;
  height: 2px;
  background: #b0b0b0;
  border-radius: 2px;
  top: 5px;
}

.attach-select-chevron::before {
  left: 0;
  transform: rotate(45deg);
}

.attach-select-chevron::after {
  right: 0;
  transform: rotate(-45deg);
}

/* ── Trigger (dashed + button) ────────────────────────────── */
.attach-trigger {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  background: transparent;
  border: 2px dashed #d8d8d8;
  border-radius: 14px;
  padding: 14px 16px;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  text-align: left;
}

.attach-trigger:hover:not(:disabled) {
  border-color: #79D45D;
  background: rgba(121, 212, 93, 0.05);
}

.attach-trigger--active {
  border-color: #79D45D;
  background: rgba(121, 212, 93, 0.05);
}

.attach-trigger:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.attach-trigger__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #79D45D;
  border-radius: 10px;
  color: #fff;
  flex-shrink: 0;
}

.attach-trigger__text {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  color: #707070;
}

/* ── Chips ────────────────────────────────────────────────── */
.attach-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.attach-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #ecfdf3;
  border: 1px solid #bbf7d0;
  border-radius: 99px;
  padding: 4px 10px 4px 12px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 600;
  color: #2b7a4b;
}

.attach-chip__remove {
  border: none;
  background: transparent;
  color: #888;
  cursor: pointer;
  font-size: 14px;
  line-height: 1;
  padding: 0;
  margin-left: 2px;
  transition: color 0.15s;
}

.attach-chip__remove:hover {
  color: #ff6b8a;
}

/* ── List container ───────────────────────────────────────── */
.attach-list-wrap {
  background: #fafafa;
  border: 1.5px solid #ececec;
  border-radius: 14px;
  overflow: hidden;
}

.attach-list-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 28px;
}

.attach-list-spinner {
  display: inline-block;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 3px solid #e5e5e5;
  border-top-color: #79D45D;
  animation: attach-spin 0.7s linear infinite;
}

@keyframes attach-spin {
  to { transform: rotate(360deg); }
}

.attach-list-empty {
  padding: 24px;
  text-align: center;
  color: #b0b0b0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
}

.attach-list {
  max-height: min(40vh, 18rem);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* ── List item ────────────────────────────────────────────── */
.attach-list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 1px solid #f0f0f0;
  padding: 10px 14px;
  cursor: pointer;
  text-align: left;
  transition: background 0.15s;
}

.attach-list-item:last-child {
  border-bottom: none;
}

.attach-list-item:hover {
  background: rgba(121, 212, 93, 0.07);
}

.attach-list-item--selected {
  background: rgba(121, 212, 93, 0.10);
}

.attach-list-item__check {
  flex-shrink: 0;
}

.attach-list-item__content {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.attach-list-item__icon-blob {
  position: relative;
  width: 44px;
  height: 32px;
  flex-shrink: 0;
}

.attach-list-item__blob-svg {
  width: 44px;
  height: 32px;
  display: block;
}

.attach-list-item__emoji {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 14px;
  line-height: 1;
}

.attach-list-item__name {
  font-family: "Bricolage Grotesque", "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #2b2d42;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── Actions ──────────────────────────────────────────────── */
.attach-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  padding-top: 4px;
  flex-shrink: 0;
}

.attach-btn {
  border-radius: 14px;
  padding: 13px 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.15s, filter 0.15s;
  border: none;
}

.attach-btn--cancel {
  background: transparent;
  color: #5e5e5e;
}

.attach-btn--cancel:hover {
  opacity: 0.75;
}

.attach-btn--confirm {
  background: #79D45D;
  border: 2px solid #6FBC58;
  color: #fff;
}

.attach-btn--confirm:hover:not(:disabled) {
  filter: brightness(0.97);
}

.attach-btn--confirm:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

/* ── Transitions ──────────────────────────────────────────── */
.attach-backdrop-enter-active,
.attach-backdrop-leave-active {
  transition: opacity 0.25s ease;
}

.attach-backdrop-enter-from,
.attach-backdrop-leave-to {
  opacity: 0;
}

.attach-sheet-enter-active,
.attach-sheet-leave-active {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}

.attach-sheet-enter-from,
.attach-sheet-leave-to {
  transform: translateY(100%);
}
</style>
