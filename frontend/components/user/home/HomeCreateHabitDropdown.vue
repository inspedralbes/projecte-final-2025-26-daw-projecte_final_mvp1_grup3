<!--
  Component o pagina Nuxt: HomeCreateHabitDropdown.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <section class="lg:hidden">
    <button
      type="button"
      class="create-habit-trigger w-full"
      :aria-expanded="formulariObert ? 'true' : 'false'"
      @click="toggleFormulari"
    >
      <span class="create-habit-trigger__icon" aria-hidden="true">
        <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
          <line x1="17" y1="2" x2="17" y2="31" stroke="white" stroke-width="4" stroke-linecap="round"/>
          <line x1="2" y1="16" x2="31" y2="16" stroke="white" stroke-width="4" stroke-linecap="round"/>
        </svg>
      </span>
    </button>

    <Teleport to="body">
      <Transition name="sheet-backdrop">
        <div
          v-if="formulariObert"
          class="fixed inset-0 z-[80] bg-black/40"
          @click="tancarFormulari"
        ></div>
      </Transition>

      <Transition name="sheet-panel">
        <div
          v-if="formulariObert"
          class="fixed left-0 right-0 bottom-0 z-[81] bg-white rounded-t-3xl shadow-2xl max-h-[85vh] flex flex-col pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
          <div
            class="sticky top-0 z-[1] bg-white rounded-t-3xl flex flex-col items-center shrink-0 border-b border-gray-100 w-full pt-4 px-6"
          >
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mb-4"></div>
            <h3 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-[#949494] mb-4 text-center w-full">
              {{ editantHabitId !== null ? $t("habits.edit_sheet_heading") : $t("habits.create_sheet_heading") }}
            </h3>
          </div>

          <div
            class="sheet-form-plain habit-form flex-1 min-h-0 overflow-y-auto px-4 py-3 space-y-3 pb-[max(1.25rem,env(safe-area-inset-bottom))]"
          >
            <HabitFormDetails
              v-model="formulari"
              :categories="categories"
              :user-categories="userCategories"
              :category-custom-label="formulari.userCategoriaEtiqueta || ''"
              :category-custom-icona="formulari.icona"
              :selected-user-category-id="formulari.userCategoriaId"
              :is-day-selected="isDaySelected"
              @select-category="seleccionarCategoria"
              @select-user-category="seleccionarCategoriaUsuari"
              @add-user-category="afegirCategoriaUsuari"
              @toggle-day="toggleDay"
            />

            <div class="bento-card rounded-3xl p-4">
              <button
                type="button"
                class="w-full flex items-center justify-between gap-3 rounded-2xl bg-gray-50/60 px-4 py-3 transition hover:bg-gray-100/70"
                @click="obrirApiSheet"
              >
                <span class="flex items-center gap-3 min-w-0">
                  <span class="w-9 h-9 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-lg shrink-0">🔎</span>
                  <span class="min-w-0 text-left">
                    <span class="block text-sm font-bold text-gray-800">Context extern (opcional)</span>
                    <span class="block text-xs text-gray-500 truncate">Pots vincular llibre, rutina o vídeo</span>
                  </span>
                </span>
                <HabitFormSelectChevron style="transform: rotate(180deg);" />
              </button>
            </div>

            <div v-if="editantHabitId" class="pt-2">
              <button
                type="button"
                class="w-full rounded-xl border-2 border-[#D14D6B] bg-[#FF6B8A] py-2.5 text-center text-base font-bold text-white transition hover:brightness-[0.97]"
                @click="eliminarHabit"
              >
                {{ $t("habits.delete") || "Eliminar" }}
              </button>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-4">
              <button
                type="button"
                class="flex w-full min-w-0 items-center justify-center border-0 bg-transparent py-2.5 text-center text-base font-normal text-[#5E5E5E] shadow-none outline-none ring-0 transition hover:opacity-80 focus-visible:underline"
                @click="tancarFormulari"
              >
                {{ $t("habits.back") }}
              </button>
              <button
                type="button"
                class="w-full min-w-0 rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] py-2.5 text-center text-base font-normal text-white transition hover:brightness-[0.97] disabled:opacity-50"
                :disabled="estaCarregant"
                @click="guardarHabit"
              >
                {{ estaCarregant ? "..." : $t("habits.save") }}
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <Transition name="sheet-backdrop">
        <div
          v-if="formulariObert && apiSectionOberta"
          class="fixed inset-0 z-[86] bg-black/40"
          @click="tancarApiSheet"
        ></div>
      </Transition>

      <Transition name="sheet-panel">
        <div
          v-if="formulariObert && apiSectionOberta"
          class="fixed left-0 right-0 bottom-0 z-[87] bg-white rounded-t-3xl shadow-2xl max-h-[82vh] flex flex-col pb-[max(0.5rem,env(safe-area-inset-bottom))] habit-form"
        >
          <div
            class="sticky top-0 z-[1] bg-white rounded-t-3xl flex flex-col items-center shrink-0 border-b border-gray-100 w-full pt-4 px-6"
          >
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mb-4"></div>
            <h3 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-[#949494] mb-4 text-center w-full">
              Context extern
            </h3>
          </div>
          <div class="habit-sheet-body">
            <div class="habit-sheet-body-inner space-y-3">
            <div v-if="proveidorExternActiu" class="space-y-4">
              <div>
                <label class="habit-form-label">Cerca externa</label>
                <div class="flex gap-2">
                  <input
                    v-model="cercaExterna.query"
                    type="text"
                    :placeholder="placeholderCercaExterna"
                    class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                    @keydown.enter.prevent="cercarRecursosExteriors"
                  />
                  <button
                    type="button"
                    class="shrink-0 rounded-2xl border-2 border-[#6FBC58] bg-[#79D45D] px-5 py-3 text-sm font-bold text-white transition hover:brightness-[0.97] disabled:opacity-50"
                    :disabled="cercaExterna.carregant"
                    @click="cercarRecursosExteriors"
                  >
                    {{ cercaExterna.carregant ? "..." : "Cercar" }}
                  </button>
                </div>
              </div>

              <div v-if="cercaExterna.error" class="flex items-center gap-2 p-3 rounded-2xl bg-red-50 border border-red-100">
                <span class="text-base flex-shrink-0">⚠️</span>
                <p class="text-sm text-red-600 flex-1">{{ cercaExterna.error }}</p>
                <button
                  type="button"
                  :disabled="cercaExterna.carregant"
                  class="flex-shrink-0 text-xs px-3 py-1.5 bg-white text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition font-bold disabled:opacity-50"
                  @click="cercarRecursosExteriors"
                >
                  Reintentar
                </button>
              </div>

              <div v-if="cercaExterna.resultats.length > 0" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                <button
                  v-for="item in cercaExterna.resultats"
                  :key="item.api_id + item.titol"
                  type="button"
                  class="w-full flex items-center gap-3 p-3 border-2 rounded-2xl text-left transition"
                  :class="itemSeleccionatEs(item) ? 'border-[#6FBC58] bg-green-50' : 'border-gray-100 bg-white hover:border-[#79D45D]/40'"
                  @click="onResultatClick(item)"
                >
                  <img v-if="item.url_imatge" :src="item.url_imatge" alt="" class="w-10 h-10 rounded-lg object-cover" />
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ item.titol }}</p>
                    <p class="text-xs text-gray-500">{{ item.tipus_api }}</p>
                  </div>
                  <span v-if="item.tipus_api === 'wger'" class="text-xs text-[#6FBC58] font-bold flex-shrink-0">Veure detall →</span>
                </button>
              </div>

              <div v-if="detallExercici.carregant" class="text-center py-4 text-sm text-gray-400">Carregant detall de l'exercici...</div>
              <p v-if="detallExercici.error" class="text-sm text-red-600">{{ detallExercici.error }}</p>

              <div v-if="detallExercici.data" class="rounded-2xl border-2 border-[#6FBC58]/30 bg-green-50 p-4 space-y-3">
                <div class="flex items-start gap-3">
                  <img
                    v-if="detallExercici.data.url_imatge"
                    :src="detallExercici.data.url_imatge"
                    alt=""
                    class="w-16 h-16 rounded-xl object-cover flex-shrink-0"
                  />
                  <div class="min-w-0 flex-1">
                    <p class="font-black text-gray-800 text-base leading-tight">{{ detallExercici.data.titol }}</p>
                  </div>
                </div>
                <div class="flex gap-2 pt-1">
                  <button
                    type="button"
                    @click="confirmarSeleccioExercici"
                    class="flex-1 py-2.5 rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] text-white text-sm font-bold transition hover:brightness-[0.97]"
                  >
                    Seleccionar exercici
                  </button>
                  <button
                    type="button"
                    @click="tancarDetallExercici"
                    class="py-2.5 px-4 rounded-xl bg-white border-2 border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition"
                  >
                    Tancar
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="text-sm text-gray-500 mb-4">
              Aquesta categoria no té cercador extern; pots usar l'entrada manual.
            </div>

            <div class="mt-4 space-y-4">
              <div class="flex items-center justify-between">
                <label class="habit-form-label !mb-0">Entrada manual</label>
                <button type="button" class="text-xs text-[#6FBC58] font-semibold" @click="activarModeManual">
                  Usar dades manuals
                </button>
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-semibold text-[#2b2d42]">Títol</label>
                <input
                  v-model="manualExtern.titol"
                  type="text"
                  placeholder="Títol del recurs"
                  class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                />
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-semibold text-[#2b2d42]">URL imatge</label>
                <input
                  v-model="manualExtern.url_imatge"
                  type="text"
                  placeholder="https://exemple.com/imatge.jpg"
                  class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                />
                <div v-if="manualExtern.url_imatge && manualExtern.url_imatge.trim()" class="mt-2 flex justify-center">
                  <img
                    :src="manualExtern.url_imatge"
                    alt="Previsualització"
                    class="max-h-32 max-w-full rounded-xl object-contain border-2 border-gray-100"
                    @error="$event.target.style.display='none'"
                    @load="$event.target.style.display=''"
                  />
                </div>
              </div>
            </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] border-t border-gray-100 shrink-0">
            <button
              type="button"
              class="flex w-full min-w-0 items-center justify-center border-0 bg-transparent py-2.5 text-center text-base font-normal text-[#5E5E5E] shadow-none outline-none ring-0 transition hover:opacity-80 focus-visible:underline"
              @click="tancarApiSheet"
            >
              Enrere
            </button>
            <button
              type="button"
              class="w-full min-w-0 rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] py-2.5 text-center text-base font-normal text-white transition hover:brightness-[0.97]"
              @click="tancarApiSheet"
            >
              Guardar
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <Teleport to="body">
      <ConfirmModal
        :show="showDeleteConfirm"
        title="Eliminar hàbit?"
        message="Estàs segur que vols eliminar aquest hàbit? Aquesta acció no es pot desfer."
        confirm-text="Eliminar"
        @confirm="confirmDeleteHabit"
        @cancel="showDeleteConfirm = false"
      />
    </Teleport>
  </section>
</template>

<script>
import HabitFormDetails from "~/components/user/habits/HabitFormDetails.vue";
import HabitFormSelectChevron from "~/components/user/habits/HabitFormSelectChevron.vue";
import ConfirmModal from "~/components/user/social/ConfirmModal.vue";
import { authFetch } from "~/composables/useApi.js";
import { getEndpointByProvider, getProviderByCategoryId } from "~/utils/habitExternal.js";
import { getDefaultColorForCategoryId, nearestCategoryIdFromHex } from "~/utils/habitCategoryColor.js";
import { normalizeHex } from "~/utils/colorSpace.js";
import { useSocketUiCallbacks } from "~/stores/useSocketUiCallbacks.js";

export default {
  name: "HomeCreateHabitDropdown",
  components: {
    HabitFormDetails,
    HabitFormSelectChevron,
    ConfirmModal
  },
  emits: ["habit-creat"],
  data: function () {
    return {
      formulariObert: false,
      editantHabitId: null,
      estaCarregant: false,
      showDeleteConfirm: false,
      socket: null,
      categories: [
        { id: 1, key: "physical", icona: "🏃" },
        { id: 2, key: "food", icona: "🥗" },
        { id: 3, key: "study", icona: "📚" },
        { id: 4, key: "reading", icona: "📖" },
        { id: 5, key: "wellness", icona: "🧘" },
        { id: 6, key: "improvement", icona: "✨" },
        { id: 7, key: "home", icona: "🏠" },
        { id: 8, key: "hobby", icona: "🎨" }
      ],
      userCategories: [],
      categoriaAnterior: null,
      formulari: {
        nom: "",
        icona: "💧",
        categoria: "",
        frequencia: "diaria",
        recordatori: "08:00",
        momentDia: "tot_dia",
        objectiuVegades: 1,
        dificultat: "facil",
        unitat: "vegades",
        color: getDefaultColorForCategoryId(1),
        dies_setmana: [true, true, true, true, true, true, true],
        dataFinalitzacio: "",
        repeticio_interval: 1,
        dies_mes: [],
        userCategoriaEtiqueta: null,
        userCategoriaId: null
      },
      recursExternSeleccionat: null,
      cercaExterna: {
        query: "",
        carregant: false,
        error: "",
        resultats: []
      },
      detallExercici: {
        carregant: false,
        data: null,
        error: ""
      },
      manualExtern: {
        titol: "",
        url_imatge: ""
      },
      apiSectionOberta: false
    };
  },
  computed: {
    proveidorExternActiu: function () {
      return getProviderByCategoryId(this.formulari.categoria);
    },
    placeholderCercaExterna: function () {
      if (this.proveidorExternActiu === "google_books") return "Cerca llibre...";
      if (this.proveidorExternActiu === "wger") return "Cerca exercici...";
      if (this.proveidorExternActiu === "api_ninjas") return "Cerca aliment...";
      if (this.proveidorExternActiu === "youtube") return "Cerca vídeo...";
      return "Cerca...";
    }
  },
  mounted: function () {
    this.carregarCategoriesUsuari();
    this.socket = useNuxtApp().$socket;
    this._onHabitActionConfirmed = this.onHabitActionConfirmedSocket.bind(this);
    useSocketUiCallbacks().registrarHabitConfirmed(this._onHabitActionConfirmed);
  },
  beforeUnmount: function () {
    if (this._onHabitActionConfirmed) {
      useSocketUiCallbacks().eliminarHabitConfirmed(this._onHabitActionConfirmed);
    }
  },
  methods: {
    toggleFormulari: function () {
      if (!this.formulariObert) {
        this.editantHabitId = null;
        this.reiniciarFormulari();
      }
      this.formulariObert = !this.formulariObert;
    },
    tancarFormulari: function () {
      this.formulariObert = false;
      this.apiSectionOberta = false;
      this.editantHabitId = null;
      this.reiniciarFormulari();
    },
    /**
     * Obre el bottom sheet en mode edició (des de la llista d'hàbits a home).
     * @param {object} habit - Hàbit del store (mapHabitFromApi)
     */
    obrirPerEdicio: function (habit) {
      if (!habit || habit.id == null) {
        return;
      }
      this.reiniciarFormulari();
      this.carregarCategoriesUsuari();
      this.aplicarHabitAlFormulari(habit);
      this.editantHabitId = habit.id;
      this.apiSectionOberta = false;
      this.formulariObert = true;
    },
    aplicarHabitAlFormulari: function (habit) {
      var catId = habit.categoriaId != null ? habit.categoriaId : habit.categoria_id;
      this.formulari.nom = habit.nom || habit.titol || "";
      this.formulari.icona = habit.icona || "💧";
      this.formulari.categoria = catId === null || catId === undefined || catId === "" ? "" : catId;
      this.formulari.frequencia = habit.frequenciaTipus || habit.frequencia_tipus || "diaria";
      this.formulari.recordatori = habit.recordatori || "08:00";
      this.formulari.momentDia = habit.momentDia || habit.moment_dia || "tot_dia";
      if (habit.color && String(habit.color).trim()) {
        this.formulari.color = normalizeHex(habit.color);
      } else {
        this.formulari.color = getDefaultColorForCategoryId(Number(this.formulari.categoria) || 1);
      }
      this.formulari.objectiuVegades = habit.objectiuVegades != null ? habit.objectiuVegades : (habit.objectiu_vegades || 1);
      this.formulari.unitat = habit.unitat || "vegades";
      this.formulari.dificultat = habit.dificultat || "facil";
      var dies = habit.diesSetmana || habit.dies_setmana;
      this.formulari.dies_setmana = Array.isArray(dies) && dies.length > 0
        ? dies.slice()
        : [true, true, true, true, true, true, true];
      this.formulari.dataFinalitzacio = habit.dataFinalitzacio || habit.data_finalitzacio || "";
      this.formulari.repeticio_interval = habit.repeticioInterval != null ? habit.repeticioInterval : (habit.repeticio_interval || 1);
      var diesMes = habit.diesMes || habit.dies_mes;
      this.formulari.dies_mes = Array.isArray(diesMes) ? diesMes.slice() : [];

      this.categoriaAnterior = this.formulari.categoria;
      this.cercaExterna.query = "";
      this.cercaExterna.resultats = [];
      this.cercaExterna.error = "";
      this.recursExternSeleccionat = null;
      this.manualExtern.titol = "";
      this.manualExtern.url_imatge = "";

      this.formulari.userCategoriaEtiqueta = null;
      this.formulari.userCategoriaId = null;

      var meta = habit.metadata;
      if (meta && typeof meta === "object") {
        if (meta.user_categoria_nom) {
          this.formulari.userCategoriaEtiqueta = meta.user_categoria_nom;
          this.formulari.userCategoriaId = meta.user_categoria_id != null ? meta.user_categoria_id : null;
          if (meta.user_categoria_icona) {
            this.formulari.icona = meta.user_categoria_icona;
          }
        }
        if (meta.tipus_api === "manual") {
          this.manualExtern.titol = meta.titol || "";
          this.manualExtern.url_imatge = meta.url_imatge || "";
        } else if (meta.tipus_api) {
          this.recursExternSeleccionat = {
            api_id: meta.api_id || "",
            titol: meta.titol || "",
            url_imatge: meta.url_imatge || "",
            tipus_api: meta.tipus_api || ""
          };
        }
      }
    },
    obrirApiSheet: function () {
      this.apiSectionOberta = true;
    },
    tancarApiSheet: function () {
      this.apiSectionOberta = false;
    },
    reiniciarFormulari: function () {
      this.editantHabitId = null;
      this.formulari.nom = "";
      this.formulari.icona = "💧";
      this.formulari.categoria = "";
      this.formulari.frequencia = "diaria";
      this.formulari.recordatori = "08:00";
      this.formulari.momentDia = "tot_dia";
      this.formulari.objectiuVegades = 1;
      this.formulari.dificultat = "facil";
      this.formulari.unitat = "vegades";
      this.formulari.color = getDefaultColorForCategoryId(1);
      this.formulari.dies_setmana = [true, true, true, true, true, true, true];
      this.formulari.dataFinalitzacio = "";
      this.formulari.repeticio_interval = 1;
      this.formulari.dies_mes = [];
      this.formulari.userCategoriaEtiqueta = null;
      this.formulari.userCategoriaId = null;
      this.categoriaAnterior = null;
      this.recursExternSeleccionat = null;
      this.cercaExterna.query = "";
      this.cercaExterna.resultats = [];
      this.cercaExterna.error = "";
      this.detallExercici.data = null;
      this.detallExercici.error = "";
      this.detallExercici.carregant = false;
      this.manualExtern.titol = "";
      this.manualExtern.url_imatge = "";
    },
    seleccionarCategoria: function (id) {
      var self = this;
      var hiHaContextExtern = this.recursExternSeleccionat !== null || this.manualExtern.titol !== "" || this.manualExtern.url_imatge !== "";

      function aplicarCanviCategoria() {
        self.formulari.categoria = id;
        self.formulari.userCategoriaEtiqueta = null;
        self.formulari.userCategoriaId = null;
        var cat = self.categories.find(function (c) { return Number(c.id) === Number(id); });
        if (cat) {
          self.formulari.icona = cat.icona;
        }
        self.formulari.color = getDefaultColorForCategoryId(id);
        self.categoriaAnterior = id;
        self.cercaExterna.query = "";
        self.cercaExterna.resultats = [];
        self.cercaExterna.error = "";
        self.recursExternSeleccionat = null;
        self.detallExercici.data = null;
        self.detallExercici.error = "";
        self.detallExercici.carregant = false;
      }

      if (this.categoriaAnterior && this.categoriaAnterior !== id && hiHaContextExtern) {
        this.$swal.fire({
          title: "Canviar categoria?",
          text: "Si canvies la categoria, s'eliminaran els aspectes vinculats (llibre, rutina, etc.).",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, canviar",
          cancelButtonText: "Cancel·lar"
        }).then(function (resultat) {
          if (resultat && resultat.isConfirmed) {
            self.manualExtern.titol = "";
            self.manualExtern.url_imatge = "";
            aplicarCanviCategoria();
          }
        });
        return;
      }

      aplicarCanviCategoria();
    },
    seleccionarCategoriaUsuari: function (payload) {
      var self = this;
      if (!payload || payload.baseCategoryId == null) {
        return;
      }
      var id = parseInt(String(payload.baseCategoryId), 10);
      if (Number.isNaN(id)) {
        return;
      }
      var hiHaContextExtern = this.recursExternSeleccionat !== null || this.manualExtern.titol !== "" || this.manualExtern.url_imatge !== "";

      function aplicarUsuari() {
        self.formulari.categoria = id;
        self.formulari.icona = payload.icona || "✨";
        self.formulari.color = payload.color && String(payload.color).trim()
          ? normalizeHex(payload.color)
          : getDefaultColorForCategoryId(id);
        self.formulari.userCategoriaEtiqueta = payload.nom;
        self.formulari.userCategoriaId = payload.id;
        self.categoriaAnterior = id;
        self.cercaExterna.query = "";
        self.cercaExterna.resultats = [];
        self.cercaExterna.error = "";
        self.recursExternSeleccionat = null;
        self.detallExercici.data = null;
        self.detallExercici.error = "";
        self.detallExercici.carregant = false;
      }

      if (this.categoriaAnterior && this.categoriaAnterior !== id && hiHaContextExtern) {
        this.$swal.fire({
          title: "Canviar categoria?",
          text: "Si canvies la categoria, s'eliminaran els aspectes vinculats (llibre, rutina, etc.).",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, canviar",
          cancelButtonText: "Cancel·lar"
        }).then(function (resultat) {
          if (resultat && resultat.isConfirmed) {
            self.manualExtern.titol = "";
            self.manualExtern.url_imatge = "";
            aplicarUsuari();
          }
        });
        return;
      }

      aplicarUsuari();
    },
    carregarCategoriesUsuari: function () {
      try {
        var raw = localStorage.getItem("loopy_user_habit_categories");
        if (!raw) {
          this.userCategories = [];
          return;
        }
        var parsed = JSON.parse(raw);
        this.userCategories = Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        this.userCategories = [];
      }
    },
    persistirCategoriesUsuari: function () {
      try {
        localStorage.setItem("loopy_user_habit_categories", JSON.stringify(this.userCategories));
      } catch (e) {}
    },
    afegirCategoriaUsuari: function (payload) {
      var nom = "";
      var icona = "✨";
      var colorHex = null;
      var baseId = 8;
      if (typeof payload === "string") {
        nom = String(payload || "").trim();
        baseId = (this.userCategories.length % 8) + 1;
      } else if (payload && typeof payload === "object") {
        nom = String(payload.nom || "").trim();
        icona = payload.icona && String(payload.icona).trim() ? String(payload.icona).trim() : "✨";
        if (payload.color && String(payload.color).trim()) {
          colorHex = normalizeHex(payload.color);
        }
        if (payload.baseCategoryId != null) {
          var b = parseInt(String(payload.baseCategoryId), 10);
          baseId = Number.isNaN(b) ? nearestCategoryIdFromHex(colorHex || "#10B981") : b;
        } else {
          baseId = nearestCategoryIdFromHex(colorHex || "#10B981");
        }
      }
      if (!nom) {
        return;
      }
      var maxId = this.userCategories.reduce(function (m, c) {
        return Math.max(m, Number(c.id) || 0);
      }, 9000);
      var nextId = maxId + 1;
      var entry = { id: nextId, nom: nom, icona: icona, baseCategoryId: baseId };
      if (colorHex) {
        entry.color = colorHex;
      }
      this.userCategories = this.userCategories.concat([entry]);
      this.persistirCategoriesUsuari();
    },
    isDaySelected: function (index) {
      return this.formulari.dies_setmana[index];
    },
    toggleDay: function (index) {
      this.formulari.dies_setmana[index] = !this.formulari.dies_setmana[index];
    },
    cercarRecursosExteriors: async function () {
      var provider = this.proveidorExternActiu;
      var endpoint = getEndpointByProvider(provider);
      if (!provider || !endpoint) {
        this.cercaExterna.error = "No hi ha cercador per aquesta categoria.";
        return;
      }
      if (!this.cercaExterna.query || this.cercaExterna.query.trim().length < 2) {
        this.cercaExterna.error = "Escriu almenys 2 caràcters per cercar.";
        return;
      }
      this.cercaExterna.carregant = true;
      this.cercaExterna.error = "";
      this.cercaExterna.resultats = [];
      try {
        var resposta = await authFetch(endpoint + "?q=" + encodeURIComponent(this.cercaExterna.query.trim()), {});
        var dades = await resposta.json();
        if (!resposta.ok || !dades.ok) {
          this.cercaExterna.error = (dades && dades.error) ? dades.error : "Error cercant recursos externs.";
          return;
        }
        this.cercaExterna.resultats = Array.isArray(dades.items) ? dades.items : [];
        if (provider === "api_ninjas" && this.cercaExterna.resultats.length === 0) {
          var respostaVideos = await authFetch("/api/external/videos?q=" + encodeURIComponent(this.cercaExterna.query.trim() + " receta"), {});
          var dadesVideos = await respostaVideos.json();
          if (respostaVideos.ok && dadesVideos && dadesVideos.ok && Array.isArray(dadesVideos.items)) {
            this.cercaExterna.resultats = dadesVideos.items;
          }
        }
      } catch (e) {
        this.cercaExterna.error = "No s'ha pogut contactar amb el proxy extern.";
      } finally {
        this.cercaExterna.carregant = false;
      }
    },
    itemSeleccionatEs: function (item) {
      if (!this.recursExternSeleccionat) return false;
      return this.recursExternSeleccionat.api_id === item.api_id && this.recursExternSeleccionat.tipus_api === item.tipus_api;
    },
    onResultatClick: function (item) {
      if (item.tipus_api === "wger") this.veureDeTallExercici(item);
      else this.seleccionarRecursExtern(item);
    },
    veureDeTallExercici: async function (item) {
      this.detallExercici.carregant = true;
      this.detallExercici.data = null;
      this.detallExercici.error = "";
      try {
        var resposta = await authFetch("/api/external/exercise/" + item.api_id, {});
        var dades = await resposta.json();
        if (!resposta.ok || !dades.ok) {
          this.detallExercici.error = (dades && dades.error) ? dades.error : "Error carregant el detall.";
          return;
        }
        this.detallExercici.data = dades.exercise;
      } catch (e) {
        this.detallExercici.error = "No s'ha pogut carregar el detall de l'exercici.";
      } finally {
        this.detallExercici.carregant = false;
      }
    },
    confirmarSeleccioExercici: function () {
      if (!this.detallExercici.data) return;
      this.recursExternSeleccionat = {
        api_id: this.detallExercici.data.api_id,
        titol: this.detallExercici.data.titol,
        url_imatge: this.detallExercici.data.url_imatge,
        tipus_api: "wger"
      };
      this.detallExercici.data = null;
      this.detallExercici.error = "";
    },
    tancarDetallExercici: function () {
      this.detallExercici.data = null;
      this.detallExercici.error = "";
    },
    seleccionarRecursExtern: function (item) {
      this.recursExternSeleccionat = item;
    },
    activarModeManual: function () {
      this.recursExternSeleccionat = null;
    },
    construirMetadataHabit: function () {
      var meta = null;
      if (this.recursExternSeleccionat) {
        meta = {
          api_id: this.recursExternSeleccionat.api_id || "",
          titol: this.recursExternSeleccionat.titol || "",
          url_imatge: this.recursExternSeleccionat.url_imatge || "",
          tipus_api: this.recursExternSeleccionat.tipus_api || ""
        };
      } else if (this.manualExtern.titol || this.manualExtern.url_imatge) {
        meta = {
          api_id: "",
          titol: this.manualExtern.titol || "",
          url_imatge: this.manualExtern.url_imatge || "",
          tipus_api: "manual"
        };
      }

      var ucNom = this.formulari.userCategoriaEtiqueta && String(this.formulari.userCategoriaEtiqueta).trim();
      if (ucNom) {
        var extra = {
          user_categoria_nom: ucNom,
          user_categoria_icona: this.formulari.icona || "✨"
        };
        if (this.formulari.userCategoriaId != null && this.formulari.userCategoriaId !== "") {
          extra.user_categoria_id = Number(this.formulari.userCategoriaId);
        }
        return meta ? Object.assign({}, meta, extra) : extra;
      }

      return meta;
    },
    guardarHabit: function () {
      if (!this.formulari.nom || !String(this.formulari.nom).trim()) {
        this.$swal.fire({
          icon: "warning",
          title: this.$t("habits.habit_name"),
          text: this.$t("habits.habit_name_required_text")
        });
        return;
      }
      if (!this.formulari.categoria) {
        this.$swal.fire({
          icon: "warning",
          title: this.$t("habits.category"),
          text: this.$t("habits.select_category_notice")
        });
        return;
      }
      if (!this.socket || !this.socket.connected) {
        this.$swal.fire({
          icon: "error",
          title: this.$t("habits.socket_offline_title"),
          text: this.$t("habits.socket_offline_text")
        });
        return;
      }
      var categoria = this.categories.find(function (cat) {
        return Number(cat.id) === Number(this.formulari.categoria);
      }, this);
      var metadata = this.construirMetadataHabit();
      var ucNom = this.formulari.userCategoriaEtiqueta && String(this.formulari.userCategoriaEtiqueta).trim();
      var iconaPayload = ucNom
        ? (this.formulari.icona || "✨")
        : (categoria ? categoria.icona : this.formulari.icona || "💧");
      var esEdicio = this.editantHabitId !== null && this.editantHabitId !== undefined;
      this.estaCarregant = true;
      this.socket.emit("habit_action", {
        action: esEdicio ? "UPDATE" : "CREATE",
        habit_id: esEdicio ? this.editantHabitId : null,
        habit_data: {
          titol: this.formulari.nom,
          dificultat: this.formulari.dificultat,
          frequencia_tipus: this.formulari.frequencia,
          categoria_id: Number(this.formulari.categoria),
          icona: iconaPayload,
          color: this.formulari.color,
          objectiu_vegades: Number(this.formulari.objectiuVegades) || 1,
          unitat: this.formulari.unitat,
          recordatori: this.formulari.recordatori,
          moment_dia: this.formulari.momentDia || "tot_dia",
          dies_setmana: this.formulari.dies_setmana,
          metadata: metadata
        }
      });
    },
    eliminarHabit: function () {
      if (!this.editantHabitId) return;
      this.showDeleteConfirm = true;
    },
    confirmDeleteHabit: function () {
      this.showDeleteConfirm = false;
      if (this.socket && this.socket.connected) {
        this.estaCarregant = true;
        this.socket.emit("habit_action", {
          action: "DELETE",
          habit_id: this.editantHabitId
        });
      }
    },
    onHabitActionConfirmedSocket: function (payload) {
      if (!payload) {
        return;
      }
      var act = String(payload.action || "").toUpperCase();
      if (act !== "CREATE" && act !== "UPDATE" && act !== "DELETE") {
        return;
      }
      this.estaCarregant = false;
      if (!payload.success) {
        this.$swal.fire({
          icon: "error",
          title: this.$t("habits.habit_save_failed_title"),
          text: payload.message || this.$t("habits.habit_save_failed_text")
        });
        return;
      }
      this.tancarFormulari();
      this.$emit("habit-creat");
    }
  }
};
</script>

<style scoped>
.create-habit-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: none;
  min-height: 64px;
  margin: 0;
  padding: 0;
  border-radius: 10px;
  background: rgba(250, 249, 249, 0.5);
  border: 2px dashed #FFFFFF;
  box-shadow: none;
}

/* SweetAlert Custom Loopy Styles */
:deep(.loopy-swal-popup) {
  border-radius: 32px !important;
  padding: 2.5rem !important;
  font-family: 'Outfit', sans-serif !important;
  border: 4px solid #F3F4F6 !important;
}

:deep(.loopy-swal-title) {
  font-weight: 900 !important;
  color: #1F2937 !important;
  font-size: 24px !important;
  margin-bottom: 1rem !important;
}

:deep(.loopy-swal-confirm) {
  background-color: #FF6B8A !important;
  color: white !important;
  border-radius: 16px !important;
  padding: 12px 24px !important;
  font-weight: 800 !important;
  margin: 8px !important;
  font-size: 16px !important;
  transition: transform 0.2s !important;
  border: none !important;
  box-shadow: 0 4px 0 #D14D6B !important;
}

:deep(.loopy-swal-confirm:active) {
  transform: translateY(2px) !important;
  box-shadow: 0 2px 0 #D14D6B !important;
}

:deep(.loopy-swal-cancel) {
  background-color: #F3F4F6 !important;
  color: #6B7280 !important;
  border-radius: 16px !important;
  padding: 12px 24px !important;
  font-weight: 800 !important;
  margin: 8px !important;
  font-size: 16px !important;
  border: none !important;
}

.create-habit-trigger__icon {
  width: 33px;
  height: 33px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.sheet-backdrop-enter-active,
.sheet-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.sheet-backdrop-enter-from,
.sheet-backdrop-leave-to {
  opacity: 0;
}

.sheet-panel-enter-active,
.sheet-panel-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.sheet-panel-enter-from,
.sheet-panel-leave-to {
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

/* En el sheet de Home no queremos tarjetas con borde/sombra */
:deep(.sheet-form-plain .bento-card) {
  border: 0 !important;
  box-shadow: none !important;
  background: transparent !important;
}
</style>
