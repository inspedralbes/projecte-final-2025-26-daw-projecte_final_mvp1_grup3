<template>
  <div class="bg-white rounded-xl shadow p-6 border max-w-2xl mx-auto">
    <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Editar Clan' : 'Crear Nou Clan' }}</h2>
    
    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom del Clan</label>
        <input v-model="form.nom" type="text" required class="w-full px-4 py-2 border rounded-lg" maxlength="50" />
      </div>
      
      <div>
         <label class="block text-sm font-medium text-gray-700 mb-1">Categoria (Opcional)</label>
         <select v-model="form.categoria_id" class="w-full px-4 py-2 border rounded-lg">
            <option :value="null">Sense Categoria</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.icona }} {{ $t('habits.categories.' + cat.key) }}</option>
         </select>
      </div>

      <div class="flex items-center gap-2">
        <input v-model="form.es_public" type="checkbox" id="es_public" class="w-4 h-4 text-blue-600" />
        <label for="es_public" class="text-sm font-medium text-gray-700">Clan Públic (qualsevol pot entrar sense sol·licitud)</label>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Límit de membres (màxim 20)</label>
        <input v-model.number="form.max_membres" type="number" min="2" max="20" required class="w-full px-4 py-2 border rounded-lg" />
      </div>

      <div class="pt-4 flex justify-end gap-2">
         <button type="button" @click="$emit('cancel')" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">Cancel·lar</button>
         <button type="submit" :disabled="loading" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50">
           {{ loading ? 'Desant...' : 'Desar' }}
         </button>
      </div>
    </form>
    <p v-if="error" class="text-red-500 mt-2 text-sm">{{ error }}</p>
  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";
import { authFetch } from "~/utils/authFetch.js";

export default {
  name: "ClanSettings",
  props: {
    clan: {
      type: Object,
      default: null
    }
  },
  data: function() {
    return {
      form: {
        nom: "",
        categoria_id: null,
        es_public: true,
        max_membres: 10
      },
      categories: [],
      loading: false,
      error: null
    }
  },
  computed: {
    isEditing: function() {
      return !!this.clan;
    }
  },
  mounted: function() {
    if (this.clan) {
      this.form.nom = this.clan.nom;
      this.form.categoria_id = this.clan.categoria_id;
      this.form.es_public = this.clan.es_public;
      this.form.max_membres = this.clan.max_membres;
    }
    this.loadCategories();
  },
  methods: {
    loadCategories: function() {
       this.categories = [
        { id: 1, key: "physical", icona: "🏃" },
        { id: 2, key: "food", icona: "🥗" },
        { id: 3, key: "study", icona: "📚" },
        { id: 4, key: "reading", icona: "📖" },
        { id: 5, key: "wellness", icona: "🧘" },
        { id: 6, key: "improvement", icona: "✨" },
        { id: 7, key: "home", icona: "🏠" },
        { id: 8, key: "hobby", icona: "🎨" }
      ];
    },
    submitForm: async function() {
      this.loading = true;
      this.error = null;
      var store = useClanStore();
      
      try {
        if (this.isEditing) {
          var res = await store.updateClan(this.clan.id, this.form);
          if (res) this.$emit("saved", res);
          else this.error = store.error;
        } else {
          var createRes = await store.createClan(this.form);
          if (createRes) {
            this.$emit("saved", createRes);
            this.$router.push('/clans/' + (createRes.clan ? createRes.clan.id : createRes.data.id));
          }
          else this.error = store.error;
        }
      } catch(e) {
        this.error = e.message;
      } finally {
         this.loading = false;
      }
    }
  }
}
</script>
