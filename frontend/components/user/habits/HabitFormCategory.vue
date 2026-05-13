<template>
  <div :class="embedded ? 'habit-form' : 'habit-form bento-card bg-white/95 backdrop-blur-md rounded-3xl p-4 sm:p-5 shadow-xl border border-white/50'">
    <label
      for="habit-category-select"
      :class="embedded ? 'habit-form-label' : 'sr-only'"
    >{{ $t('habits.category') }}</label>

    <!-- Sense selecció: estil compacte amb chevron -->
    <button
      v-if="!teSeleccioVisual"
      id="habit-category-select"
      data-testid="habit-category-select"
      type="button"
      class="habit-form-field-surface flex w-full cursor-pointer items-center justify-between border-gray-100 bg-gray-50/50 text-gray-800 transition-all focus:outline-none focus:ring-4 focus:border-green-500 focus:bg-white focus:ring-green-500/10"
      @click="obrirSelector"
    >
      <span class="habit-form-field-text min-w-0 text-gray-500">{{ $t('habits.category_select_placeholder') }}</span>
      <HabitFormSelectChevron />
    </button>

    <!-- Amb selecció: vora sencera amb color de categoria -->
    <button
      v-else
      id="habit-category-select"
      data-testid="habit-category-select"
      type="button"
      class="habit-form-field-surface flex w-full cursor-pointer items-center gap-3 bg-white text-left shadow-none transition-all focus:outline-none focus:ring-4 focus:ring-green-500/15"
      :style="pillTriggerStyle"
      @click="obrirSelector"
    >
      <span
        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-100 text-2xl leading-none"
        aria-hidden="true"
      >{{ iconaSeleccio }}</span>
      <span class="habit-form-field-text min-w-0 flex-1 font-semibold text-gray-800">{{ textSeleccio }}</span>
      <HabitFormSelectChevron />
    </button>
  </div>

  <HabitFormCreateUserCategorySheet
    :open="crearCategoriaObert"
    @close="tancarCrearCategoria"
    @save="onNovaCategoriaDesada"
  />

  <Teleport to="body">
    <Transition name="category-backdrop">
      <div
        v-if="selectorObert"
        class="fixed inset-0 z-[82] bg-black/40"
        @click="tancarSelector"
      ></div>
    </Transition>

    <Transition name="category-sheet-panel">
      <div
        v-if="selectorObert"
        class="category-sheet-root habit-form fixed bottom-0 left-0 right-0 z-[83] flex max-h-[min(88vh,42rem)] flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        role="dialog"
        aria-modal="true"
        :aria-label="$t('habits.category_modal_title')"
        @click.stop
      >
        <header class="create-habit-sheet__header sticky top-0 z-[1] shrink-0 bg-white px-4 pt-3 pb-2">
          <button
            type="button"
            class="create-habit-sheet__close create-habit-sheet__close--start"
            :aria-label="$t('habits.cancel')"
            @click="tancarSelector"
          >
            <span class="create-habit-sheet__close-line create-habit-sheet__close-line--1" aria-hidden="true"></span>
            <span class="create-habit-sheet__close-line create-habit-sheet__close-line--2" aria-hidden="true"></span>
          </button>
          <h3 class="create-habit-sheet__title">
            {{ $t('habits.category_modal_title') }}
          </h3>
        </header>

        <div class="category-sheet-body habit-form habit-sheet-body">
          <div class="habit-sheet-body-inner space-y-5">
            <section>
              <h3 class="habit-form-label block">
                {{ $t('habits.category_section_yours') }}
              </h3>
              <p
                v-if="!userCategories || !userCategories.length"
                class="text-sm leading-relaxed text-gray-500"
              >
                {{ $t('habits.category_none_yours') }}
              </p>
              <div v-else class="space-y-2">
                <button
                  v-for="u in userCategories"
                  :key="'uc-' + u.id"
                  type="button"
                  class="flex w-full items-center gap-3 rounded-2xl px-6 py-4 text-left transition"
                  :class="
                    esCategoriaUsuariSeleccionada(u)
                      ? ''
                      : 'border-2 border-gray-100 border-solid bg-white hover:bg-gray-50/80'
                  "
                  :style="categoriaFilaStyleUsuari(u)"
                  @click="seleccionarCategoriaUsuari(u)"
                >
                  <span class="text-xl leading-none" aria-hidden="true">{{ u.icona || '✨' }}</span>
                  <span class="habit-form-field-text min-w-0 flex-1 text-gray-800">{{ u.nom }}</span>
                </button>
              </div>
            </section>

            <section>
              <h3 class="habit-form-label block">
                {{ $t('habits.category_section_defaults') }}
              </h3>
              <div class="space-y-2">
                <button
                  v-for="cat in categories"
                  :key="cat.id"
                  type="button"
                  class="flex w-full items-center gap-3 rounded-2xl px-6 py-4 text-left transition"
                  :class="
                    String(cat.id) === normalizedSelected && !categoryCustomLabel
                      ? ''
                      : 'border-2 border-gray-100 border-solid bg-white hover:bg-gray-50/80'
                  "
                  :style="categoriaFilaStyleDefecte(cat)"
                  :data-testid="'habit-category-' + cat.key"
                  @click="seleccionarCategoria(cat.id)"
                >
                  <span class="text-xl leading-none" aria-hidden="true">{{ cat.icona }}</span>
                  <span class="habit-form-field-text min-w-0 flex-1 text-gray-800">{{ $t('habits.categories.' + cat.key) }}</span>
                </button>
              </div>
            </section>

            <section>
              <h3 class="habit-form-label block">
                {{ $t('habits.category_create_new_label') }}
              </h3>
              <button
                type="button"
                class="create-category-trigger w-full"
                data-testid="habit-open-create-category"
                :aria-label="$t('habits.category_create_button_aria')"
                @click="obrirCrearCategoria"
              >
                <span class="create-category-trigger__icon" aria-hidden="true">
                  <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="17" y1="2" x2="17" y2="31" stroke="#D8D8D8" stroke-width="4" stroke-linecap="round" />
                    <line x1="2" y1="16" x2="31" y2="16" stroke="#D8D8D8" stroke-width="4" stroke-linecap="round" />
                  </svg>
                </span>
              </button>
            </section>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import HabitFormSelectChevron from './HabitFormSelectChevron.vue'
import HabitFormCreateUserCategorySheet from './HabitFormCreateUserCategorySheet.vue'
import {
  getDefaultColorForCategoryId,
  getCategorySelectionSurfaceStyle,
  getSurfaceStyleForHex
} from '~/utils/habitCategoryColor.js'

export default {
  name: 'HabitFormCategory',
  components: {
    HabitFormSelectChevron,
    HabitFormCreateUserCategorySheet
  },
  props: {
    categories: { type: Array, required: true },
    selectedId: { type: [Number, String], default: '' },
    userCategories: { type: Array, default: function () { return [] } },
    categoryCustomLabel: { type: String, default: '' },
    categoryCustomIcona: { type: String, default: '' },
    selectedUserCategoryId: { type: [Number, String], default: null },
    embedded: { type: Boolean, default: false }
  },
  emits: ['select', 'select-user', 'add-user-category'],
  data: function () {
    return {
      selectorObert: false,
      crearCategoriaObert: false
    }
  },
  computed: {
    normalizedSelected: function () {
      var s = this.selectedId
      if (s === '' || s === null || s === undefined) {
        return ''
      }
      return String(s)
    },
    teSeleccioVisual: function () {
      return this.normalizedSelected !== '' || !!(this.categoryCustomLabel && String(this.categoryCustomLabel).trim())
    },
    iconaSeleccio: function () {
      if (this.categoryCustomLabel && String(this.categoryCustomLabel).trim()) {
        return this.categoryCustomIcona && String(this.categoryCustomIcona).trim()
          ? String(this.categoryCustomIcona).trim()
          : '✨'
      }
      if (this.normalizedSelected === '') {
        return '💧'
      }
      var current = (this.categories || []).find(function (cat) {
        return String(cat.id) === this.normalizedSelected
      }, this)
      return current ? current.icona : '💧'
    },
    textSeleccio: function () {
      if (this.categoryCustomLabel && String(this.categoryCustomLabel).trim()) {
        return String(this.categoryCustomLabel).trim()
      }
      if (this.normalizedSelected === '') {
        return this.$t('habits.category_select_placeholder')
      }
      var current = (this.categories || []).find(function (cat) {
        return String(cat.id) === this.normalizedSelected
      }, this)
      if (!current) {
        return this.$t('habits.category_select_placeholder')
      }
      return this.$t('habits.categories.' + current.key)
    },
    accentIdPerColor: function () {
      if (this.categoryCustomLabel && this.selectedUserCategoryId !== null && this.selectedUserCategoryId !== undefined && this.selectedUserCategoryId !== '') {
        var uid = Number(this.selectedUserCategoryId)
        var u = (this.userCategories || []).find(function (x) {
          return Number(x.id) === uid
        })
        if (u && u.baseCategoryId != null) {
          var b = parseInt(String(u.baseCategoryId), 10)
          if (!Number.isNaN(b)) {
            return b
          }
        }
        return 8
      }
      if (this.normalizedSelected !== '') {
        var n = parseInt(this.normalizedSelected, 10)
        if (!Number.isNaN(n)) {
          return n
        }
      }
      return null
    },
    accentHex: function () {
      if (this.categoryCustomLabel && this.selectedUserCategoryId !== null && this.selectedUserCategoryId !== undefined && this.selectedUserCategoryId !== '') {
        var uid = Number(this.selectedUserCategoryId)
        var u = (this.userCategories || []).find(function (x) {
          return Number(x.id) === uid
        })
        if (u && u.color && String(u.color).trim()) {
          var c = String(u.color).trim()
          if (c[0] !== '#') {
            c = '#' + c
          }
          return c.length === 7 ? c.toUpperCase() : getDefaultColorForCategoryId(u.baseCategoryId || 8)
        }
      }
      if (this.accentIdPerColor == null) {
        return null
      }
      return getDefaultColorForCategoryId(this.accentIdPerColor)
    },
    pillTriggerStyle: function () {
      var o = {
        backgroundColor: '#ffffff',
        border: '2px solid #E5E7EB'
      }
      if (this.accentHex) {
        o.boxShadow = 'inset 6px 0 0 0 ' + this.accentHex
      }
      return o
    }
  },
  methods: {
    colorPerCategoriaDefecte: function (cat) {
      return getDefaultColorForCategoryId(cat.id)
    },
    colorPerCategoriaUsuari: function (u) {
      if (u && u.color && String(u.color).trim()) {
        var c = String(u.color).trim()
        return c[0] === '#' ? c : '#' + c
      }
      var b = u.baseCategoryId != null ? parseInt(String(u.baseCategoryId), 10) : 8
      if (Number.isNaN(b)) {
        b = 8
      }
      return getDefaultColorForCategoryId(b)
    },
    categoriaFilaStyleDefecte: function (cat) {
      var hex = this.colorPerCategoriaDefecte(cat)
      var sel = String(cat.id) === this.normalizedSelected && !this.categoryCustomLabel
      if (sel) {
        return getCategorySelectionSurfaceStyle(cat.id)
      }
      return { boxShadow: 'inset 6px 0 0 0 ' + hex }
    },
    categoriaFilaStyleUsuari: function (u) {
      var hex = this.colorPerCategoriaUsuari(u)
      if (this.esCategoriaUsuariSeleccionada(u)) {
        if (u.color && String(u.color).trim()) {
          return getSurfaceStyleForHex(u.color)
        }
        var baseId = u.baseCategoryId != null ? parseInt(String(u.baseCategoryId), 10) : 8
        return getCategorySelectionSurfaceStyle(Number.isNaN(baseId) ? 8 : baseId)
      }
      return { boxShadow: 'inset 6px 0 0 0 ' + hex }
    },
    esCategoriaUsuariSeleccionada: function (u) {
      if (u == null || this.selectedUserCategoryId === null || this.selectedUserCategoryId === undefined || this.selectedUserCategoryId === '') {
        return false
      }
      return Number(u.id) === Number(this.selectedUserCategoryId)
    },
    obrirSelector: function () {
      this.selectorObert = true
    },
    tancarSelector: function () {
      this.selectorObert = false
    },
    obrirCrearCategoria: function () {
      this.crearCategoriaObert = true
    },
    tancarCrearCategoria: function () {
      this.crearCategoriaObert = false
    },
    onNovaCategoriaDesada: function (payload) {
      this.$emit('add-user-category', payload)
      this.crearCategoriaObert = false
    },
    seleccionarCategoria: function (id) {
      var n = parseInt(String(id), 10)
      if (!Number.isNaN(n)) {
        this.$emit('select', n)
      }
      this.tancarSelector()
    },
    seleccionarCategoriaUsuari: function (u) {
      if (!u) {
        return
      }
      var baseId = u.baseCategoryId != null ? parseInt(String(u.baseCategoryId), 10) : 8
      var colorHex = this.colorPerCategoriaUsuari(u)
      this.$emit('select-user', {
        id: u.id,
        nom: u.nom,
        icona: u.icona || '✨',
        baseCategoryId: Number.isNaN(baseId) ? 8 : baseId,
        color: colorHex
      })
      this.tancarSelector()
    }
  }
}
</script>

<style scoped>
.category-backdrop-enter-active,
.category-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.category-backdrop-enter-from,
.category-backdrop-leave-to {
  opacity: 0;
}

.category-sheet-panel-enter-active,
.category-sheet-panel-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.category-sheet-panel-enter-from,
.category-sheet-panel-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}

.create-habit-sheet__header {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2.75rem;
  padding-left: 2.75rem;
  padding-right: 2.75rem;
}

.create-habit-sheet__title {
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

.create-habit-sheet__close:focus {
  outline: none;
}

.create-habit-sheet__close:focus-visible {
  box-shadow: 0 0 0 2px rgba(148, 148, 148, 0.4);
  border-radius: 6px;
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
  box-sizing: border-box;
  pointer-events: none;
}

.create-habit-sheet__close-line--1 {
  transform: translate(-50%, -50%) rotate(43.17deg);
}

.create-habit-sheet__close-line--2 {
  transform: translate(-50%, -50%) rotate(-44.87deg);
}

.create-category-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 338px;
  min-height: 64px;
  margin: 0 auto;
  padding: 0;
  border-radius: 10px;
  background: #f0f0f0;
  border: 2px dashed #d8d8d8;
  box-shadow: none;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.create-category-trigger:hover {
  background: #ebebeb;
}

.create-category-trigger:focus {
  outline: none;
}

.create-category-trigger:focus-visible {
  box-shadow: 0 0 0 2px rgba(216, 216, 216, 0.55);
}

.create-category-trigger__icon {
  width: 33px;
  height: 33px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
