<template>
  <div class="relative w-full min-h-screen pb-12 overflow-y-auto">
    <!-- Navbar / Header Base -->
    <div class="w-full p-6 flex justify-between items-center z-20">
      <div class="flex items-center gap-4">
        <NuxtLink to="/home" class="bg-white/90 backdrop-blur-sm text-green-700 w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm hover:shadow-md hover:bg-white transition-all hover:-translate-x-1">
          ←
        </NuxtLink>
        <h1 class="text-3xl font-extrabold text-white drop-shadow-md">{{ $t('habits.title') }}</h1>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Esquerra: Seccions del formulari -->
      <div class="lg:col-span-2 space-y-8">
        <!-- 1. Detalls -->
        <HabitFormDetails v-model="formulari" />

        <!-- 2. Planificació -->
        <HabitFormPlanning 
          v-model="formulari" 
          @toggle-day="toggleDay"
          :is-day-selected="isDaySelected"
        />

        <!-- 3. Categoria -->
        <HabitFormCategory 
          :categories="categories" 
          :selected-id="formulari.categoria" 
          @select="seleccionarCategoria" 
        />

        <!-- 4. Context extern (opcional) -->
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

        <!-- 4. Estil -->
        <HabitFormStyle 
          :colors="colors" 
          :selected-color="formulari.color" 
          @update:color="formulari.color = $event" 
        />

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

      <!-- Dreta: Llista dels meus hàbits -->
      <div class="lg:col-span-1">
        <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50 h-full">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-xl">✨</div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ $t('habits.my_habits') }}</h2>
          </div>

          <div v-if="habitStore.habits.length === 0" class="text-center py-20 bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-200">
            <p class="text-gray-400 font-bold">{{ $t('habits.no_habits_yet') }}</p>
            <p class="text-xs text-gray-300 mt-2 uppercase tracking-widest">{{ $t('habits.add_new') }}</p>
          </div>

          <div v-else class="space-y-4">
            <div v-for="hàbit in habitStore.habits" :key="hàbit.id" :data-testid="'habit-list-item-' + hàbit.id" class="flex items-center gap-4 p-4 rounded-2xl bg-white border-2 border-gray-50 shadow-sm hover:shadow-lg hover:border-green-100 transition-all cursor-pointer group" @click="obrirModalEdicio(hàbit)">
              <div :style="{ backgroundColor: hàbit.color || '#10B981' }" class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl text-white shadow-lg shadow-inner transform group-hover:rotate-6 transition-transform">
                {{ hàbit.icona }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-black text-gray-800 truncate text-lg tracking-tight">{{ hàbit.nom }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase">{{ obtenerNomCategoria(hàbit.categoriaId) }}</p>
              </div>
              <button @click.stop="eliminarHabit(hàbit.id)" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white transition-all transform hover:scale-110">
                ×
              </button>
            </div>
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
import HabitFormCategory from "~/components/user/habits/HabitFormCategory.vue";
import HabitFormStyle from "~/components/user/habits/HabitFormStyle.vue";
import { authFetch } from "~/composables/useApi.js";
import { getEndpointByProvider, getProviderByCategoryId } from "~/utils/habitExternal.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

export default {
  components: {
    HabitFormDetails,
    HabitFormPlanning,
    HabitFormCategory,
    HabitFormStyle
  },
  data: function () {
    return {
      socket: null,
      estaCarregant: false,
      errorMissatge: "",
      formulari: {
        nom: "", 
        motivacio: "", 
        icona: "💧", 
        categoria: "", 
        frequencia: "diaria", 
        recordatori: "08:00", 
        color: "#10B981", 
        objectiuVegades: 1, 
        unitat: "vegades",
        dificultat: "facil",
        dies_setmana: [true, true, true, true, true, true, true]
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
      ]
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
  mounted: function () {
    this.carregarHabits();
    this.socket = useNuxtApp().$socket;
  },
  methods: {
    carregarHabits: async function () {
      await this.habitStore.obtenirHabitsDesDeApi();
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
    obtenerNomCategoria: function (id) {
      var cat = this.categories.find(function(c) { return c.id === id; });
      return cat ? this.$t('habits.categories.' + cat.key) : "";
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
      if (!this.formulari.nom || !this.formulari.categoria) return;
      var metadata = this.construirMetadataHabit();
      var esEdicio = this.editantHabitId !== null;
      this.estaCarregant = true;
      this.socket.emit("habit_action", {
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
      setTimeout(function() { 
        this.estaCarregant = false; 
        this.carregarHabits(); 
        this.reiniciarFormulari();
      }.bind(this), 1000);
    },
    obrirModalEdicio: function (habit) {
      this.editantHabitId = habit.id;
      this.formulari.nom = habit.nom || "";
      this.formulari.motivacio = "";
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
      this.formulari.motivacio = "";
      this.formulari.icona = "💧";
      this.formulari.categoria = "";
      this.formulari.frequencia = "diaria";
      this.formulari.recordatori = "08:00";
      this.formulari.color = "#10B981";
      this.formulari.objectiuVegades = 1;
      this.formulari.unitat = "vegades";
      this.formulari.dificultat = "facil";
      this.formulari.dies_setmana = [true, true, true, true, true, true, true];

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
    eliminarHabit: function (id) {
      this.socket.emit("habit_action", {
        action: "DELETE",
        habit_id: id
      });
      setTimeout(function() { this.carregarHabits(); }.bind(this), 500);
    }
  }
};
</script>
