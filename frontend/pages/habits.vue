<template>
  <div class="relative w-full min-h-screen pb-12 overflow-y-auto">
    <div class="max-w-4xl mx-auto px-6 pt-4 pb-4 space-y-6">
        <!-- Accés ràpid: llista per editar (modal) -->
        <div class="flex justify-end">
          <button
            type="button"
            data-testid="habit-open-edit-list-button"
            class="inline-flex items-center gap-2 rounded-2xl border-2 border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-800 shadow-sm transition hover:border-green-300 hover:bg-green-50/80"
            @click="obrirModalLlistaEditarHabits"
          >
            <span aria-hidden="true">✏️</span>
            {{ $t('habits.edit_list_open_button') }}
          </button>
        </div>

        <!-- 1. Detalls (inclou categoria) -->
        <HabitFormDetails
          v-model="formulari"
          :categories="categories"
          :colors="colors"
          @select-category="seleccionarCategoria"
        />

        <!-- 2. Planificació -->
        <HabitFormPlanning 
          v-model="formulari" 
          @toggle-day="toggleDay"
          :is-day-selected="isDaySelected"
        />

        <!-- 3. Context extern (opcional) -->
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-lg">🔎</div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Context extern (opcional)</h3>
              <p class="text-xs text-gray-500">Pots vincular llibre, rutina o vídeo. Si falla, introdueix dades manuals.</p>
            </div>
          </div>

          <div v-if="proveidorExternActiu" class="space-y-3">
            <div class="flex gap-2">
              <input
                data-testid="external-search-input"
                v-model="cercaExterna.query"
                type="text"
                :placeholder="placeholderCercaExterna"
                class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500"
              />
              <button
                data-testid="external-search-button"
                class="px-4 py-3 rounded-2xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 disabled:opacity-50"
                :disabled="cercaExterna.carregant"
                @click="cercarRecursosExteriors"
              >
                {{ cercaExterna.carregant ? "..." : "Cercar" }}
              </button>
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
                🔄 Reintentar
              </button>
            </div>

            <div v-if="cercaExterna.resultats.length > 0" class="space-y-2 max-h-56 overflow-y-auto pr-1">
              <button
                v-for="item in cercaExterna.resultats"
                :key="item.api_id + item.titol"
                data-testid="external-result-item"
                type="button"
                class="w-full flex items-center gap-3 p-3 border-2 rounded-2xl text-left hover:border-blue-300 transition"
                :class="itemSeleccionatEs(item) ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white'"
                @click="onResultatClick(item)"
              >
                <img v-if="item.url_imatge" :src="item.url_imatge" alt="" class="w-10 h-10 rounded-lg object-cover" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-gray-800 truncate">{{ item.titol }}</p>
                  <p class="text-xs text-gray-500">{{ item.tipus_api }}</p>
                </div>
                <span v-if="item.tipus_api === 'wger'" class="text-xs text-blue-500 font-bold flex-shrink-0">Veure detall →</span>
              </button>
            </div>

            <div v-if="detallExercici.carregant" class="text-center py-4 text-sm text-gray-400">
              Carregant detall de l'exercici...
            </div>

            <p v-if="detallExercici.error" class="text-sm text-red-600">{{ detallExercici.error }}</p>

            <div v-if="detallExercici.data" class="rounded-2xl border-2 border-blue-200 bg-blue-50 p-4 space-y-3">
              <div class="flex items-start gap-3">
                <img
                  v-if="detallExercici.data.url_imatge"
                  :src="detallExercici.data.url_imatge"
                  alt=""
                  class="w-16 h-16 rounded-xl object-cover flex-shrink-0"
                />
                <div class="min-w-0 flex-1">
                  <p class="font-black text-gray-800 text-base leading-tight">{{ detallExercici.data.titol }}</p>
                  <span v-if="detallExercici.data.categoria" class="inline-block mt-1 text-xs text-blue-700 font-bold uppercase bg-blue-100 px-2 py-0.5 rounded-full">
                    {{ detallExercici.data.categoria }}
                  </span>
                </div>
              </div>

              <div v-if="detallExercici.data.muscles.length > 0" class="text-sm">
                <span class="font-bold text-gray-600">Músculs principals: </span>
                <span class="text-gray-700">{{ detallExercici.data.muscles.join(", ") }}</span>
              </div>

              <div v-if="detallExercici.data.muscles_secundaris.length > 0" class="text-sm">
                <span class="font-bold text-gray-600">Músculs secundaris: </span>
                <span class="text-gray-700">{{ detallExercici.data.muscles_secundaris.join(", ") }}</span>
              </div>

              <div v-if="detallExercici.data.equipament.length > 0" class="text-sm">
                <span class="font-bold text-gray-600">Equipament: </span>
                <span class="text-gray-700">{{ detallExercici.data.equipament.join(", ") }}</span>
              </div>

              <p v-if="detallExercici.data.descripcio" class="text-sm text-gray-600 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                {{ detallExercici.data.descripcio }}
              </p>

              <div class="flex gap-2 pt-1">
                <button
                  type="button"
                  @click="confirmarSeleccioExercici"
                  class="flex-1 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition"
                >
                  Seleccionar exercici
                </button>
                <button
                  type="button"
                  @click="tancarDetallExercici"
                  class="py-2 px-4 rounded-xl bg-white border-2 border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition"
                >
                  Tancar
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-sm text-gray-500 mb-4">
            Aquesta categoria no té cercador extern; pots usar l'entrada manual.
          </div>

          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Entrada manual</h4>
              <button type="button" class="text-xs text-blue-600 font-semibold" @click="activarModeManual">
                Usar dades manuals
              </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <input
                data-testid="manual-title-input"
                v-model="manualExtern.titol"
                type="text"
                placeholder="Títol manual"
                class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-4 py-3 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500"
              />
              <input
                data-testid="manual-image-input"
                v-model="manualExtern.url_imatge"
                type="text"
                placeholder="URL imatge manual"
                class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-4 py-3 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500"
              />
            </div>
          </div>
        </div>

        <!-- Botó Enviar -->
        <button data-testid="habit-save-button" @click="guardarHabit" :disabled="estaCarregant" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-6 rounded-3xl shadow-2xl shadow-green-900/40 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-4 text-2xl uppercase tracking-widest disabled:opacity-50">
          <span class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">{{ editantHabitId ? "✎" : "＋" }}</span>
          {{ estaCarregant ? 'Processant...' : (editantHabitId ? 'Guardar canvis' : $t('habits.create_button')) }}
        </button>
        <button
          v-if="editantHabitId"
          class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-2xl transition"
          @click="cancelarEdicio"
        >
          Cancel·lar edició
        </button>
    </div>

    <!-- Modal: triar hàbit per editar -->
    <div
      v-if="modalLlistaEditarObert"
      data-testid="habit-edit-list-modal"
      class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4"
      role="dialog"
      aria-modal="true"
      :aria-label="$t('habits.my_habits')"
      @click.self="tancarModalLlistaEditarHabits"
    >
      <div class="w-full max-w-md max-h-[min(85vh,32rem)] flex flex-col rounded-3xl bg-white shadow-2xl border border-gray-100 overflow-hidden" @click.stop>
        <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0">
          <div class="min-w-0">
            <h3 class="text-lg font-black text-gray-800">{{ $t('habits.my_habits') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ $t('habits.pick_habit_modal_subtitle') }}</p>
          </div>
          <button
            type="button"
            class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 font-bold text-lg leading-none"
            :aria-label="$t('habits.cancel')"
            @click="tancarModalLlistaEditarHabits"
          >
            ×
          </button>
        </div>
        <div class="flex-1 min-h-0 overflow-y-auto p-4">
          <div v-if="habitStore.habits.length === 0" class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50">
            <p class="text-gray-500 font-bold">{{ $t('habits.no_habits_yet') }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ $t('habits.add_new') }}</p>
          </div>
          <div v-else class="space-y-2">
            <button
              v-for="hàbit in habitStore.habits"
              :key="hàbit.id"
              type="button"
              :data-testid="'habit-list-item-' + hàbit.id"
              class="w-full flex items-center gap-3 p-3 rounded-2xl border-2 text-left transition border-gray-100 bg-gray-50/50 hover:border-green-400 hover:bg-green-50/50"
              @click="triarHabitPerEditar(hàbit)"
            >
              <div
                :style="{ backgroundColor: hàbit.color || '#10B981' }"
                class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white shadow-md shrink-0"
              >
                {{ hàbit.icona }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-black text-gray-800 truncate">{{ hàbit.nom }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">{{ obtenerNomCategoria(hàbit.categoriaId) }}</p>
              </div>
              <span class="text-gray-400 shrink-0 text-sm">→</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHabitStore } from "../stores/useHabitStore";
import HabitFormDetails from "~/components/user/habits/HabitFormDetails.vue";
import HabitFormPlanning from "~/components/user/habits/HabitFormPlanning.vue";
import { authFetch } from "~/composables/useApi.js";
import { getEndpointByProvider, getProviderByCategoryId } from "~/utils/habitExternal.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

export default {
  components: {
    HabitFormDetails,
    HabitFormPlanning
  },
  data: function () {
    return {
      socket: null,
      estaCarregant: false,
      habitGuardarTimeoutId: null,
      errorMissatge: "",
      formulari: {
        nom: "", 
        icona: "💧", 
        categoria: "", 
        frequencia: "diaria", 
        recordatori: "08:00", 
        color: "#10B981", 
        objectiuVegades: 1, 
        unitat: "vegades",
        dificultat: "facil",
        dies_setmana: [true, true, true, true, true, true, true],
        dataFinalitzacio: "",
        repeticio_interval: 1,
        dies_mes: []
      },
      editantHabitId: null,
      categoriaAnterior: null,
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
      colors: [
        "#10B981", "#3B82F6", "#F59E0B", "#EF4444", "#8B5CF6", "#EC4899", "#06B6D4", "#1F2937"
      ],
      modalLlistaEditarObert: false
    };
  },
  computed: {
    habitStore: function () { return useHabitStore(); },
    authStore: function () { return useAuthStore(); },
    proveidorExternActiu: function () {
      return getProviderByCategoryId(this.formulari.categoria);
    },
    placeholderCercaExterna: function () {
      if (this.proveidorExternActiu === "google_books") {
        return "Cerca llibre...";
      }
      if (this.proveidorExternActiu === "wger") {
        return "Cerca exercici...";
      }
      if (this.proveidorExternActiu === "api_ninjas") {
        return "Cerca aliment...";
      }
      if (this.proveidorExternActiu === "youtube") {
        return "Cerca vídeo...";
      }
      return "Cerca...";
    }
  },
  mounted: async function () {
    var self = this;
    await this.carregarHabits();
    this.aplicarEditDesDeQuery();
    this.socket = useNuxtApp().$socket;
    if (this.socket) {
      this._onHabitActionConfirmed = function (payload) {
        self.onHabitActionConfirmedSocket(payload);
      };
      this.socket.on("habit_action_confirmed", this._onHabitActionConfirmed);
    }
  },
  beforeUnmount: function () {
    this.clearHabitGuardarPending();
    if (this.socket && this._onHabitActionConfirmed) {
      this.socket.off("habit_action_confirmed", this._onHabitActionConfirmed);
    }
  },
  methods: {
    clearHabitGuardarPending: function () {
      if (this.habitGuardarTimeoutId !== null) {
        clearTimeout(this.habitGuardarTimeoutId);
        this.habitGuardarTimeoutId = null;
      }
    },
    onHabitActionConfirmedSocket: function (payload) {
      if (!payload || payload.action == null) {
        return;
      }
      var accio = String(payload.action).toUpperCase();
      if (accio !== "CREATE" && accio !== "UPDATE" && accio !== "DELETE") {
        return;
      }
      this.clearHabitGuardarPending();
      this.estaCarregant = false;
      this.carregarHabits();
      if (payload.success) {
        var editId = this.editantHabitId;
        var deletedId =
          payload.habit &&
          typeof payload.habit.id !== "undefined" &&
          payload.habit.id !== null
            ? Number(payload.habit.id)
            : null;
        if (accio === "CREATE" || accio === "UPDATE") {
          this.reiniciarFormulari();
        } else if (
          accio === "DELETE" &&
          editId !== null &&
          deletedId !== null &&
          Number(editId) === deletedId
        ) {
          this.reiniciarFormulari();
        }
        return;
      }
      if (this.$swal) {
        var text;
        if (payload.error === "SOCKET_AUTH") {
          text = this.$t("habits.habit_socket_auth_failed");
        } else if (payload.message) {
          text = payload.message;
        } else {
          text = this.$t("habits.habit_save_failed_text");
        }
        this.$swal.fire({
          icon: "error",
          title: this.$t("habits.habit_save_failed_title"),
          text: text
        });
      }
    },
    carregarHabits: async function () {
      await this.habitStore.obtenirHabitsDesDeApi();
    },
    obtenerNomCategoria: function (id) {
      if (id === null || id === undefined || id === "") {
        return "";
      }
      var nid = Number(id);
      var cat = this.categories.find(function (c) {
        return Number(c.id) === nid;
      });
      return cat ? this.$t("habits.categories." + cat.key) : "";
    },
    obrirModalLlistaEditarHabits: async function () {
      await this.carregarHabits();
      this.modalLlistaEditarObert = true;
    },
    tancarModalLlistaEditarHabits: function () {
      this.modalLlistaEditarObert = false;
    },
    triarHabitPerEditar: function (habit) {
      this.obrirModalEdicio(habit);
      this.modalLlistaEditarObert = false;
      if (typeof window !== "undefined") {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    },
    /**
     * Obre el formulari en mode edició si la URL inclou ?edit=<id> (p. ex. després d'enllaços o e2e).
     */
    aplicarEditDesDeQuery: function () {
      var q = this.$route.query.edit;
      if (q === undefined || q === null || q === "") {
        return;
      }
      var id = parseInt(String(q), 10);
      if (Number.isNaN(id)) {
        return;
      }
      var habit = this.habitStore.habits.find(function (h) {
        return Number(h.id) === id;
      });
      if (habit) {
        this.obrirModalEdicio(habit);
      }
    },
    seleccionarCategoria: function (id) {
      var self = this;
      var hiHaContextExtern = this.recursExternSeleccionat !== null || this.manualExtern.titol !== "" || this.manualExtern.url_imatge !== "";

      function aplicarCanviCategoria() {
        self.formulari.categoria = id;
        var cat = self.categories.find(function(c) { return c.id === id; });
        if (cat) {
          self.formulari.icona = cat.icona;
        }
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
        }).then(function(resultat) {
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
        if (Array.isArray(dades.items)) {
          this.cercaExterna.resultats = dades.items;
        } else {
          this.cercaExterna.resultats = [];
        }
      } catch (e) {
        this.cercaExterna.error = "No s'ha pogut contactar amb el proxy extern.";
      } finally {
        this.cercaExterna.carregant = false;
      }
    },
    itemSeleccionatEs: function (item) {
      if (!this.recursExternSeleccionat) {
        return false;
      }
      return this.recursExternSeleccionat.api_id === item.api_id && this.recursExternSeleccionat.tipus_api === item.tipus_api;
    },
    onResultatClick: function (item) {
      if (item.tipus_api === "wger") {
        this.veureDeTallExercici(item);
      } else {
        this.seleccionarRecursExtern(item);
      }
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
      if (!this.detallExercici.data) {
        return;
      }
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
      if (this.recursExternSeleccionat) {
        return {
          api_id: this.recursExternSeleccionat.api_id || "",
          titol: this.recursExternSeleccionat.titol || "",
          url_imatge: this.recursExternSeleccionat.url_imatge || "",
          tipus_api: this.recursExternSeleccionat.tipus_api || ""
        };
      }

      if (this.manualExtern.titol || this.manualExtern.url_imatge) {
        return {
          api_id: "",
          titol: this.manualExtern.titol || "",
          url_imatge: this.manualExtern.url_imatge || "",
          tipus_api: "manual"
        };
      }

      return null;
    },
    guardarHabit: function () {
      if (!this.formulari.nom || !String(this.formulari.nom).trim()) {
        if (this.$swal) {
          this.$swal.fire({
            icon: "warning",
            title: this.$t("habits.habit_name"),
            text: this.$t("habits.habit_name_required_text")
          });
        }
        return;
      }
      if (!this.formulari.categoria) {
        if (this.$swal) {
          this.$swal.fire({
            icon: "warning",
            title: this.$t("habits.category"),
            text: this.$t("habits.select_category_notice")
          });
        }
        return;
      }
      var socket = this.socket || (typeof useNuxtApp === "function" ? useNuxtApp().$socket : null);
      if (!socket || !socket.connected) {
        if (this.$swal) {
          this.$swal.fire({
            icon: "error",
            title: this.$t("habits.socket_offline_title"),
            text: this.$t("habits.socket_offline_text")
          });
        }
        return;
      }
      var metadata = this.construirMetadataHabit();
      var esEdicio = this.editantHabitId !== null;
      this.estaCarregant = true;
      this.clearHabitGuardarPending();
      var self = this;
      this.habitGuardarTimeoutId = setTimeout(function () {
        self.habitGuardarTimeoutId = null;
        if (!self.estaCarregant) {
          return;
        }
        self.estaCarregant = false;
        self.carregarHabits();
        if (self.$swal) {
          self.$swal.fire({
            icon: "warning",
            title: self.$t("habits.habit_save_timeout_title"),
            text: self.$t("habits.habit_save_timeout_text")
          });
        }
      }, 15000);
      socket.emit("habit_action", {
        action: esEdicio ? "UPDATE" : "CREATE",
        habit_id: esEdicio ? this.editantHabitId : null,
        habit_data: {
          titol: this.formulari.nom,
          dificultat: this.formulari.dificultat,
          frequencia_tipus: this.formulari.frequencia,
          categoria_id: this.formulari.categoria,
          icona: this.formulari.icona,
          color: this.formulari.color,
          objectiu_vegades: this.formulari.objectiuVegades,
          unitat: this.formulari.unitat,
          recordatori: this.formulari.recordatori,
          dies_setmana: this.formulari.dies_setmana,
          metadata: metadata
        }
      });
    },
    obrirModalEdicio: function (habit) {
      this.editantHabitId = habit.id;
      this.formulari.nom = habit.nom || "";
      this.formulari.icona = habit.icona || "💧";
      this.formulari.categoria = habit.categoriaId || "";
      this.formulari.frequencia = habit.frequenciaTipus || "diaria";
      this.formulari.recordatori = habit.recordatori || "08:00";
      this.formulari.color = habit.color || "#10B981";
      this.formulari.objectiuVegades = habit.objectiuVegades || 1;
      this.formulari.unitat = habit.unitat || "vegades";
      this.formulari.dificultat = habit.dificultat || "facil";
      this.formulari.dies_setmana = Array.isArray(habit.diesSetmana) && habit.diesSetmana.length > 0
        ? habit.diesSetmana
        : [true, true, true, true, true, true, true];
      this.formulari.dataFinalitzacio = habit.dataFinalitzacio || "";
      this.formulari.repeticio_interval = habit.repeticioInterval || 1;

      this.categoriaAnterior = this.formulari.categoria;
      this.cercaExterna.query = "";
      this.cercaExterna.resultats = [];
      this.cercaExterna.error = "";
      this.recursExternSeleccionat = null;
      this.manualExtern.titol = "";
      this.manualExtern.url_imatge = "";

      if (habit.metadata && typeof habit.metadata === "object") {
        if (habit.metadata.tipus_api === "manual") {
          this.manualExtern.titol = habit.metadata.titol || "";
          this.manualExtern.url_imatge = habit.metadata.url_imatge || "";
        } else {
          this.recursExternSeleccionat = {
            api_id: habit.metadata.api_id || "",
            titol: habit.metadata.titol || "",
            url_imatge: habit.metadata.url_imatge || "",
            tipus_api: habit.metadata.tipus_api || ""
          };
        }
      }
    },
    cancelarEdicio: function () {
      this.reiniciarFormulari();
    },
    reiniciarFormulari: function () {
      this.editantHabitId = null;
      this.formulari.nom = "";
      this.formulari.icona = "💧";
      this.formulari.categoria = "";
      this.formulari.frequencia = "diaria";
      this.formulari.recordatori = "08:00";
      this.formulari.color = "#10B981";
      this.formulari.objectiuVegades = 1;
      this.formulari.unitat = "vegades";
      this.formulari.dificultat = "facil";
      this.formulari.dies_setmana = [true, true, true, true, true, true, true];
      this.formulari.dataFinalitzacio = "";
      this.formulari.repeticio_interval = 1;
      this.formulari.dies_mes = [];

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
    }
  }
};
</script>
