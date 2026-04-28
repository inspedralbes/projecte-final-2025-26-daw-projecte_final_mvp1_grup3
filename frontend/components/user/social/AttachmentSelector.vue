<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">{{ $t('social.select_attachment') || 'Seleccionar Adjunt' }}</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="flex border-b mb-4">
        <button
          @click="tab = 'habit'"
          :class="['flex-1 py-2 text-sm font-medium border-b-2 transition-colors', tab === 'habit' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
        >
          {{ $t('social.habits') || 'Hàbits' }}
        </button>
        <button
          @click="tab = 'plantilla'"
          :class="['flex-1 py-2 text-sm font-medium border-b-2 transition-colors', tab === 'plantilla' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
        >
          {{ $t('social.templates') || 'Plantilles' }}
        </button>
      </div>

      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      </div>

      <div v-else-if="items.length === 0" class="text-center py-8 text-gray-500">
        {{ tab === 'habit' ? ($t('habits.no_habits') || 'No tens hàbits actius') : ($t('social.no_templates') || 'No tens plantilles') }}
      </div>

      <div v-else class="max-h-64 overflow-y-auto space-y-2 pr-1">
        <button
          v-for="item in items"
          :key="item.id"
          @click="selectItem(item)"
          class="w-full p-3 rounded-lg text-left border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all flex items-center justify-between group"
        >
          <div>
            <div class="font-medium text-gray-800">{{ item.titol || item.nom }}</div>
            <div class="text-xs text-gray-500" v-if="item.categoria?.nom">{{ item.categoria.nom }}</div>
          </div>
          <svg class="w-5 h-5 text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </button>
      </div>

      <div class="mt-6 flex justify-end">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800"
        >
          {{ $t('social.cancel') || 'Cancel·lar' }}
        </button>
      </div>
    </div>
  </div>
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
      tab: "habit",
      loading: false,
      habits: [],
      plantilles: []
    };
  },
  computed: {
    items: function () {
      return this.tab === "habit" ? this.habits : this.plantilles;
    }
  },
  watch: {
    show: function (newVal) {
      if (newVal) {
        this.loadData();
      }
    },
    tab: function () {
      this.loadData();
    }
  },
  methods: {
    loadData: async function () {
      this.loading = true;
      try {
        if (this.tab === "habit") {
          var habitStore = useHabitStore();
          await habitStore.obtenirHabitsDesDeApi();
          this.habits = habitStore.habits;
        } else {
          var plantillaStore = usePlantillaStore();
          await plantillaStore.obtenirPlantillesDesDeApi('my');
          this.plantilles = plantillaStore.plantilles;
        }
      } catch (e) {
        console.error("Error loading attachments:", e);
      } finally {
        this.loading = false;
      }
    },
    selectItem: function (item) {
      this.$emit("selected", {
        type: this.tab,
        id: item.id,
        titol: item.titol || item.nom
      });
      this.$emit("close");
    }
  }
};
</script>
