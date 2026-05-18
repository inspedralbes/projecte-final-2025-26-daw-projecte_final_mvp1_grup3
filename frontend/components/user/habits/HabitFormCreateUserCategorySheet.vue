<template>
  <Teleport to="body">
    <Transition name="create-cat-backdrop">
      <div
        v-if="open"
        class="fixed inset-0 z-[90] bg-black/40"
        @click="onBackdrop"
      ></div>
    </Transition>
    <Transition name="create-cat-panel">
      <div
        v-if="open"
        class="create-cat-root habit-form fixed bottom-0 left-0 right-0 z-[91] flex max-h-[min(88vh,36rem)] flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        role="dialog"
        aria-modal="true"
        :aria-label="$t('habits.category_create_sheet_title')"
        @click.stop
      >
        <header class="sticky top-0 z-[2] shrink-0 bg-white flex flex-col items-center border-b border-gray-100 w-full pt-4 px-6">
          <div class="w-12 h-1.5 bg-gray-300 rounded-full mb-4"></div>
          <h3 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-[#949494] mb-4 text-center w-full">
            {{ $t('habits.category_create_sheet_title') }}
          </h3>
        </header>

        <div class="habit-sheet-body min-h-0">
          <div class="habit-sheet-body-inner space-y-5">
            <div>
              <label class="habit-form-label" for="nova-cat-nom">{{ $t('habits.category_field_name') }}</label>
              <input
                id="nova-cat-nom"
                v-model="form.nom"
                type="text"
                class="w-full habit-form-field-surface bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                :placeholder="$t('habits.category_create_placeholder')"
                autocomplete="off"
              />
            </div>

            <div class="relative z-[1]">
              <label class="habit-form-label">{{ $t('habits.category_field_icon') }}</label>
              <button
                type="button"
                class="habit-form-field-surface w-full flex items-center justify-between border-gray-100 bg-gray-50/50 text-left transition hover:border-green-200"
                @click="iconMenuObert = !iconMenuObert; colorPickerObert = false"
              >
                <span class="text-2xl leading-none" aria-hidden="true">{{ form.icona }}</span>
                <HabitFormSelectChevron />
              </button>
              <div
                v-if="iconMenuObert"
                class="absolute left-0 right-0 top-full z-[5] mt-2 max-h-48 overflow-y-auto rounded-2xl border-2 border-gray-100 bg-white p-2 shadow-lg"
              >
                <div class="grid grid-cols-6 gap-1.5">
                  <button
                    v-for="ic in iconPresets"
                    :key="ic"
                    type="button"
                    class="flex h-11 w-full items-center justify-center rounded-xl text-xl transition hover:bg-gray-100"
                    :class="form.icona === ic ? 'bg-green-50 ring-2 ring-green-400' : ''"
                    @click="form.icona = ic; iconMenuObert = false"
                  >
                    {{ ic }}
                  </button>
                </div>
              </div>
            </div>

            <div class="relative z-0">
              <label class="habit-form-label">{{ $t('habits.category_field_color') }}</label>
              <button
                type="button"
                class="habit-form-field-surface w-full flex items-center justify-between gap-3 border-gray-100 text-left transition hover:border-green-200"
                :style="colorRowSurfaceStyle"
                @click="colorPickerObert = !colorPickerObert; iconMenuObert = false"
              >
                <span class="habit-form-field-text font-semibold" :style="{ color: colorRowText }">{{ colorDisplayLabel }}</span>
                <HabitFormSelectChevron />
              </button>

              <div
                v-if="colorPickerObert"
                class="mt-3 rounded-2xl border-2 border-gray-100 bg-white p-4 space-y-4 shadow-sm"
              >
                <div
                  ref="svPanel"
                  class="relative h-44 w-full cursor-crosshair touch-none select-none overflow-hidden rounded-xl"
                  :style="svPanelStyle"
                  @pointerdown.prevent="onSvPointerDown"
                  @pointermove="onSvPointerMove"
                  @pointerup="onSvPointerUp"
                  @pointercancel="onSvPointerUp"
                  @pointerleave="onSvPointerUp"
                >
                  <div
                    class="pointer-events-none absolute h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white shadow-md"
                    :style="svKnobStyle"
                  />
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs font-bold uppercase tracking-wide text-gray-400 shrink-0">{{ $t('habits.color_hue') }}</span>
                  <input
                    v-model.number="hue"
                    type="range"
                    min="0"
                    max="359"
                    class="h-2 w-full flex-1 cursor-pointer accent-gray-700"
                    @input="syncHexFromHsv"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-400" for="nova-cat-hex">Hex</label>
                  <input
                    id="nova-cat-hex"
                    v-model="hexInput"
                    type="text"
                    maxlength="7"
                    class="w-full rounded-xl border-2 border-gray-100 bg-gray-50/80 px-3 py-2 font-mono text-sm uppercase tracking-wide text-gray-800 outline-none focus:border-green-400"
                    @change="onHexChange"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="shrink-0 bg-white px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              class="w-full rounded-xl border-0 bg-white py-2.5 text-center text-base font-normal text-[#5E5E5E] transition hover:bg-gray-50"
              @click="tancar"
            >
              {{ $t('habits.back') }}
            </button>
            <button
              type="button"
              class="w-full rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] py-2.5 text-center text-base font-normal text-white transition hover:brightness-[0.97] disabled:opacity-50"
              :disabled="!potDesar"
              @click="desar"
            >
              {{ $t('habits.save') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import HabitFormSelectChevron from './HabitFormSelectChevron.vue'
import {
  COLORS_BY_CATEGORY_ID,
  SWATCH_I18N_KEY_BY_CATEGORY_ID,
  nearestCategoryIdFromHex,
  hexToRgba
} from '~/utils/habitCategoryColor.js'
import {
  normalizeHex,
  hexToHsv,
  hsvToHex,
  hexToRgb,
  pickTextOnHexBackground
} from '~/utils/colorSpace.js'

function clamp (v, a, b) {
  return Math.max(a, Math.min(b, v))
}

export default {
  name: 'HabitFormCreateUserCategorySheet',
  components: { HabitFormSelectChevron },
  props: {
    open: { type: Boolean, default: false }
  },
  emits: ['close', 'save'],
  data: function () {
    return {
      form: { nom: '', icona: '✨' },
      hue: 150,
      sat: 0.55,
      val: 0.95,
      hexInput: '#10B981',
      iconMenuObert: false,
      colorPickerObert: false,
      svDragging: false,
      iconPresets: [
        '✨', '🏃', '🥗', '📚', '📖', '🧘', '🏠', '🎨', '💪', '🧠', '💧', '🔥', '⭐', '🎯', '🌿', '🎮',
        '💼', '🛌', '🍎', '🧩', '📝', '🎵', '❤️', '🌙', '☀️', '🚴', '🧹', '💰', '📱', '🐶', '🌸', '🥑', '🍋'
      ]
    }
  },
  computed: {
    hexActual: function () {
      return normalizeHex(hsvToHex(this.hue, this.sat, this.val))
    },
    potDesar: function () {
      return !!String(this.form.nom || '').trim()
    },
    svPanelStyle: function () {
      return {
        background: [
          'linear-gradient(to top, #000, rgba(0,0,0,0))',
          'linear-gradient(to right, #fff, rgba(255,255,255,0))',
          'hsl(' + this.hue + ', 100%, 50%)'
        ].join(', ')
      }
    },
    svKnobStyle: function () {
      return {
        left: (this.sat * 100) + '%',
        top: (100 - this.val * 100) + '%'
      }
    },
    colorRowSurfaceStyle: function () {
      var h = this.hexActual
      return {
        backgroundColor: hexToRgba(h, 0.32),
        borderColor: hexToRgba(h, 0.45)
      }
    },
    colorRowText: function () {
      return pickTextOnHexBackground(this.hexActual)
    },
    colorDisplayLabel: function () {
      var hex = this.hexActual
      var nearest = nearestCategoryIdFromHex(hex)
      var pal = normalizeHex(COLORS_BY_CATEGORY_ID[nearest])
      var rgbH = hexToRgb(hex)
      var rgbP = hexToRgb(pal)
      var dist = Math.hypot(rgbH.r - rgbP.r, rgbH.g - rgbP.g, rgbH.b - rgbP.b)
      if (dist < 22) {
        var key = SWATCH_I18N_KEY_BY_CATEGORY_ID[nearest]
        return this.$t('habits.' + key)
      }
      return hex.replace('#', '').toUpperCase()
    }
  },
  watch: {
    open: function (v) {
      if (v) {
        this.resetForm()
      }
    }
  },
  methods: {
    resetForm: function () {
      this.form = { nom: '', icona: '✨' }
      this.hexInput = '#10B981'
      this.applyHexToHsv(this.hexInput)
      this.iconMenuObert = false
      this.colorPickerObert = false
      this.svDragging = false
    },
    applyHexToHsv: function (hex) {
      var hsv = hexToHsv(normalizeHex(hex))
      this.hue = Math.round(hsv.h)
      this.sat = hsv.s
      this.val = hsv.v
    },
    syncHexFromHsv: function () {
      this.hexInput = this.hexActual
    },
    onHexChange: function () {
      this.applyHexToHsv(this.hexInput)
      this.hexInput = this.hexActual
    },
    onSvPointerDown: function (e) {
      if (!e.isPrimary) {
        return
      }
      var panel = this.$refs.svPanel
      if (!panel) {
        return
      }
      this.svDragging = true
      try {
        panel.setPointerCapture(e.pointerId)
      } catch (err) {}
      this.updateSvFromEvent(e)
    },
    onSvPointerMove: function (e) {
      if (!this.svDragging || !e.isPrimary) {
        return
      }
      this.updateSvFromEvent(e)
    },
    onSvPointerUp: function (e) {
      if (!this.svDragging) {
        return
      }
      this.svDragging = false
      var panel = this.$refs.svPanel
      if (panel && e && e.pointerId != null) {
        try {
          panel.releasePointerCapture(e.pointerId)
        } catch (err) {}
      }
      this.hexInput = this.hexActual
    },
    updateSvFromEvent: function (e) {
      var el = this.$refs.svPanel
      if (!el) {
        return
      }
      var r = el.getBoundingClientRect()
      var x = clamp((e.clientX - r.left) / r.width, 0, 1)
      var y = clamp((e.clientY - r.top) / r.height, 0, 1)
      this.sat = x
      this.val = 1 - y
      this.syncHexFromHsv()
    },
    onBackdrop: function () {
      this.tancar()
    },
    tancar: function () {
      this.iconMenuObert = false
      this.colorPickerObert = false
      this.$emit('close')
    },
    desar: function () {
      if (!this.potDesar) {
        return
      }
      var hex = this.hexActual
      this.$emit('save', {
        nom: String(this.form.nom).trim(),
        icona: this.form.icona || '✨',
        color: hex,
        baseCategoryId: nearestCategoryIdFromHex(hex)
      })
    }
  }
}
</script>

<style scoped>
.create-cat-backdrop-enter-active,
.create-cat-backdrop-leave-active {
  transition: opacity 0.2s ease;
}
.create-cat-backdrop-enter-from,
.create-cat-backdrop-leave-to {
  opacity: 0;
}
.create-cat-panel-enter-active,
.create-cat-panel-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.create-cat-panel-enter-from,
.create-cat-panel-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}

.create-habit-sheet__header {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 2.75rem;
  padding-left: 0;
  padding-right: 0;
}
.create-habit-sheet__title {
  margin: 0;
  width: 100%;
  text-align: center;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  color: #949494;
}
.create-habit-sheet__close {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  border: none;
  padding: 0;
  margin: 0;
  background: transparent;
  cursor: pointer;
}
.create-habit-sheet__close--start {
  left: 8px;
  right: auto;
}
.create-habit-sheet__close-line {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 18.5px;
  height: 4px;
  background-color: #d8d8d8;
  border-radius: 999px;
  transform-origin: center;
  pointer-events: none;
}
.create-habit-sheet__close-line--1 {
  transform: translate(-50%, -50%) rotate(43.17deg);
}
.create-habit-sheet__close-line--2 {
  transform: translate(-50%, -50%) rotate(-44.87deg);
}
</style>
