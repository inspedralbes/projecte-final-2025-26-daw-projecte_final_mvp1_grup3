<template>
  <section class="lg:hidden px-2">
    <div class="rounded-2xl border border-white/40 bg-white/20 backdrop-blur-sm p-2">
      <button
        type="button"
        class="w-full h-11 rounded-xl border-2 border-dashed border-white/60 text-white text-2xl font-black flex items-center justify-center transition hover:bg-white/15"
        :aria-expanded="formulariObert ? 'true' : 'false'"
        @click="toggleFormulari"
      >
        <span aria-hidden="true">{{ formulariObert ? "−" : "+" }}</span>
      </button>
    </div>

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
          class="fixed left-0 right-0 bottom-0 z-[81] bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 max-h-[85vh] flex flex-col pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
          <div class="sticky top-0 bg-white rounded-t-3xl px-4 pt-3 pb-2 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-black text-gray-800">
              {{ $t("habits.title") || "Crea un nou hàbit" }}
            </h3>
            <button
              type="button"
              class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 text-xl font-bold"
              @click="tancarFormulari"
            >
              ×
            </button>
          </div>

          <div class="sheet-form-plain flex-1 min-h-0 overflow-y-auto px-4 py-3 space-y-3">
            <HabitFormDetails
              v-model="formulari"
              :categories="categories"
              :colors="colors"
              @select-category="seleccionarCategoria"
            />
            <HabitFormPlanning
              v-model="formulari"
              :is-day-selected="isDaySelected"
              @toggle-day="toggleDay"
            />

          <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-5 shadow-xl border border-white/50">
            <button
              type="button"
              class="w-full flex items-center justify-between gap-3 rounded-2xl border-2 border-gray-100 bg-gray-50/60 px-4 py-3"
              @click="obrirApiSheet"
            >
              <span class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-lg shrink-0">🔎</span>
                <span class="min-w-0 text-left">
                  <span class="block text-sm font-bold text-gray-800">Context extern (opcional)</span>
                  <span class="block text-xs text-gray-500 truncate">Pots vincular llibre, rutina o vídeo</span>
                </span>
              </span>
              <span class="text-gray-400 text-lg leading-none">⌃</span>
            </button>
          </div>
          </div>
          <div class="shrink-0 border-t border-gray-100 bg-white px-4 pt-3 pb-[max(0.9rem,env(safe-area-inset-bottom))]">
            <button
              type="button"
              class="w-full bg-green-500 hover:bg-green-600 text-white font-black py-2.5 rounded-xl disabled:opacity-50"
              :disabled="estaCarregant"
              @click="guardarHabit"
            >
              {{ estaCarregant ? "..." : ($t("habits.create_button") || "Guardar") }}
            </button>
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
          class="fixed left-0 right-0 bottom-0 z-[87] bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 max-h-[82vh] flex flex-col pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
          <div class="sticky top-0 bg-white rounded-t-3xl px-4 pt-3 pb-2 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-black text-gray-800">Context extern (opcional)</h3>
            <button type="button" class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 text-xl font-bold" @click="tancarApiSheet">×</button>
          </div>
          <div class="flex-1 min-h-0 overflow-y-auto p-4 space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))]">
            <div v-if="proveidorExternActiu" class="space-y-3">
              <div class="flex gap-2">
                <input
                  v-model="cercaExterna.query"
                  type="text"
                  :placeholder="placeholderCercaExterna"
                  class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-4 py-3 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500"
                  @keydown.enter.prevent="cercarRecursosExteriors"
                />
                <button
                  type="button"
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

              <div v-if="detallExercici.carregant" class="text-center py-4 text-sm text-gray-400">Carregant detall de l'exercici...</div>
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
                  </div>
                </div>
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
              <div class="grid grid-cols-1 gap-3">
                <input
                  v-model="manualExtern.titol"
                  type="text"
                  placeholder="Títol manual"
                  class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-4 py-3 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500"
                />
                <input
                  v-model="manualExtern.url_imatge"
                  type="text"
                  placeholder="URL imatge manual"
                  class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-4 py-3 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500"
                />
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>

<script>
import HabitFormDetails from "~/components/user/habits/HabitFormDetails.vue";
import HabitFormPlanning from "~/components/user/habits/HabitFormPlanning.vue";
import { authFetch } from "~/composables/useApi.js";
import { getEndpointByProvider, getProviderByCategoryId } from "~/utils/habitExternal.js";

export default {
  name: "HomeCreateHabitDropdown",
  components: {
    HabitFormDetails,
    HabitFormPlanning
  },
  emits: ["habit-creat"],
  data: function () {
    return {
      formulariObert: false,
      estaCarregant: false,
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
      colors: [
        "#10B981", "#3B82F6", "#F59E0B", "#EF4444", "#8B5CF6", "#EC4899", "#06B6D4", "#1F2937"
      ],
      formulari: {
        nom: "",
        categoria: "",
        frequencia: "diaria",
        recordatori: "08:00",
        objectiuVegades: 1,
        dificultat: "facil",
        unitat: "vegades",
        color: "#10B981",
        dies_setmana: [true, true, true, true, true, true, true]
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
    this.socket = useNuxtApp().$socket;
    if (this.socket) {
      this._onHabitActionConfirmed = this.onHabitActionConfirmedSocket.bind(this);
      this.socket.on("habit_action_confirmed", this._onHabitActionConfirmed);
    }
  },
  beforeUnmount: function () {
    if (this.socket && this._onHabitActionConfirmed) {
      this.socket.off("habit_action_confirmed", this._onHabitActionConfirmed);
    }
  },
  methods: {
    toggleFormulari: function () {
      this.formulariObert = !this.formulariObert;
    },
    tancarFormulari: function () {
      this.formulariObert = false;
      this.apiSectionOberta = false;
    },
    obrirApiSheet: function () {
      this.apiSectionOberta = true;
    },
    tancarApiSheet: function () {
      this.apiSectionOberta = false;
    },
    reiniciarFormulari: function () {
      this.formulari.nom = "";
      this.formulari.categoria = "";
      this.formulari.frequencia = "diaria";
      this.formulari.recordatori = "08:00";
      this.formulari.objectiuVegades = 1;
      this.formulari.dificultat = "facil";
      this.formulari.unitat = "vegades";
      this.formulari.color = "#10B981";
      this.formulari.dies_setmana = [true, true, true, true, true, true, true];
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
      this.formulari.categoria = id;
      var cat = this.categories.find(function (c) { return Number(c.id) === Number(id); });
      if (cat) {
        this.formulari.icona = cat.icona;
      }
      this.cercaExterna.query = "";
      this.cercaExterna.resultats = [];
      this.cercaExterna.error = "";
      this.recursExternSeleccionat = null;
      this.detallExercici.data = null;
      this.detallExercici.error = "";
      this.detallExercici.carregant = false;
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
      this.estaCarregant = true;
      this.socket.emit("habit_action", {
        action: "CREATE",
        habit_data: {
          titol: this.formulari.nom,
          dificultat: this.formulari.dificultat,
          frequencia_tipus: this.formulari.frequencia,
          categoria_id: Number(this.formulari.categoria),
          icona: categoria ? categoria.icona : "💧",
          color: this.formulari.color,
          objectiu_vegades: Number(this.formulari.objectiuVegades) || 1,
          unitat: this.formulari.unitat,
          recordatori: this.formulari.recordatori,
          dies_setmana: this.formulari.dies_setmana,
          metadata: metadata
        }
      });
    },
    onHabitActionConfirmedSocket: function (payload) {
      if (!payload || String(payload.action || "").toUpperCase() !== "CREATE") {
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
      this.reiniciarFormulari();
      this.tancarFormulari();
      this.$emit("habit-creat");
    }
  }
};
</script>

<style scoped>
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

/* En el sheet de Home no queremos tarjetas con borde/sombra */
:deep(.sheet-form-plain .bento-card) {
  border: 0 !important;
  box-shadow: none !important;
  background: transparent !important;
}
</style>
