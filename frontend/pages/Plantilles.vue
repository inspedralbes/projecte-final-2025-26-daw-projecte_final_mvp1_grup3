<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Gestió de Plantilles</h1>

      <button
        @click="obrirModalCrearPlantilla"
        class="mb-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2"
      >
        <span
          class="bg-white text-green-600 rounded-full w-5 h-5 flex items-center justify-center text-xs"
          >+</span>
        Crear Nova Plantilla
      </button>

      <div v-if="plantillaStore.loading" class="text-center py-10">
        <p class="text-gray-500">Carregant plantilles...</p>
      </div>

      <div v-else-if="plantillaStore.error" class="text-center py-10 text-red-500">
        <p>Error: {{ plantillaStore.error }}</p>
      </div>

      <div v-else-if="plantillaStore.plantilles.length === 0" class="text-center py-10 text-gray-400">
        <p>No hi ha plantilles disponibles.</p>
        <p class="text-sm">Crea la primera plantilla!</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="plantilla in plantillaStore.plantilles"
          :key="plantilla.id"
          class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between"
        >
          <div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">{{ plantilla.titol }}</h2>
            <p class="text-sm text-gray-600 mb-4">Categoria: {{ plantilla.categoria }}</p>
            <span
              :class="{
                'bg-green-100 text-green-800': plantilla.esPublica,
                'bg-blue-100 text-blue-800': !plantilla.esPublica
              }"
              class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium"
            >
              {{ plantilla.esPublica ? 'Pública' : 'Privada' }}
            </span>
          </div>
          <div class="mt-4 flex justify-end gap-3">
            <button
              @click="editarPlantilla(plantilla.id)"
              class="text-sm text-blue-600 hover:text-blue-700 font-semibold px-3 py-1 rounded border border-blue-200 hover:border-blue-300 transition-colors"
            >
              Editar
            </button>
            <button
              @click="eliminarPlantilla(plantilla.id)"
              class="text-sm text-red-600 hover:text-red-700 font-semibold px-3 py-1 rounded border border-red-200 hover:border-red-300 transition-colors"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal para crear/editar plantilla -->
      <PlantillaCreateModal
        v-if="modalVisible"
        :modoEdicio="modoEdicio"
        :plantillaAEditar="plantillaSeleccionada"
        @tancar="tancarModal"
        @plantillaCreada="plantillaCreada"
        @plantillaActualitzada="plantillaActualitzada"
      />
    </div>
  </div>
</template>

<script>
import { usePlantillaStore } from "../stores/usePlantillaStore";
import PlantillaCreateModal from "../components/PlantillaCreateModal.vue"; // Aquest component encara no existeix

export default {
  components: {
    PlantillaCreateModal: PlantillaCreateModal,
  },
  setup: function () {
    var plantillaStore = usePlantillaStore();
    return { plantillaStore: plantillaStore };
  },
  data: function () {
    return {
      modalVisible: false,
      modoEdicio: false,
      plantillaSeleccionada: null, // Per a l'edició
    };
  },
  mounted: function () {
    this.carregarPlantilles();
  },
  methods: {
    carregarPlantilles: async function () {
      await this.plantillaStore.obtenirPlantillesDesDeApi();
    },
    obrirModalCrearPlantilla: function () {
      this.modoEdicio = false;
      this.plantillaSeleccionada = null;
      this.modalVisible = true;
    },
    editarPlantilla: function (id) {
      // Implementar lògica per obtenir la plantilla per ID i passar-la al modal
      // Per ara, només obrim el modal en mode edició
      var self = this;
      var plantillaTrobada = null;
      var i;

      for (i = 0; i < self.plantillaStore.plantilles.length; i++) {
        if (self.plantillaStore.plantilles[i].id === id) {
          plantillaTrobada = self.plantillaStore.plantilles[i];
          break;
        }
      }

      this.plantillaSeleccionada = plantillaTrobadal;
      this.modoEdicio = true;
      this.modalVisible = true;
    },
    eliminarPlantilla: function (id) {
      // Implementar lògica d'eliminació via socket
      alert("Eliminar plantilla amb ID: " + id);
      // Després d'eliminar, recarregar plantilles o actualitzar l'store
    },
    tancarModal: function () {
      this.modalVisible = false;
    },
    plantillaCreada: function () {
      this.tancarModal();
      this.carregarPlantilles(); // Recarregar les plantilles per veure la nova
    },
    plantillaActualitzada: function () {
      this.tancarModal();
      this.carregarPlantilles(); // Recarregar les plantilles per veure els canvis
    },
  },
};
</script>

<style scoped>
/* Estils específics de la pàgina si calen */
</style>
