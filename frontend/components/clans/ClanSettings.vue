<!--
  Component o pagina Nuxt: ClanSettings.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="clan-settings-panel habit-form">
    <h2 class="habit-form-label text-center" style="font-size: 24px; margin-bottom: 1.25rem;">{{ isEditing ? 'Editar Clan' : 'Crear Nou Clan' }}</h2>

    <form @submit.prevent="submitForm" class="space-y-5">
      <div>
        <label class="habit-form-label" for="clan-nom">Nom del Clan</label>
        <input
          id="clan-nom"
          v-model="form.nom"
          type="text"
          required
          maxlength="50"
          placeholder="Nom del clan..."
          class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
        />
      </div>

      <div>
        <label class="habit-form-label" for="clan-categoria">Categoria (Opcional)</label>
        <select
          id="clan-categoria"
          v-model="form.categoria_id"
          class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
        >
          <option :value="null">Sense Categoria</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.icona }} {{ $t('habits.categories.' + cat.key) }}</option>
        </select>
      </div>

      <div>
        <label class="habit-form-label" for="clan-public-toggle">Clan Públic</label>
        <SharedTemplatePublicSwitch
          input-id="clan-public-toggle"
          :model-value="form.es_public"
          @update:model-value="form.es_public = $event"
        />
      </div>

      <div>
        <label class="habit-form-label" for="clan-max-membres">Límit de membres</label>
        <select
          id="clan-max-membres"
          v-model.number="form.max_membres"
          required
          class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
        >
          <option :value="10">10 membres</option>
          <option :value="15">15 membres</option>
          <option :value="20">20 membres</option>
        </select>
      </div>

      <div class="flex gap-3 pt-2">
        <button
          type="button"
          @click="$emit('cancel')"
          class="clan-settings-btn clan-settings-btn--secondary flex-1"
        >
          Cancel·lar
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="clan-settings-btn clan-settings-btn--primary flex-1"
        >
          {{ loading ? 'Desant...' : 'Desar' }}
        </button>
      </div>
    </form>
    <p v-if="error" class="clan-settings-error">{{ error }}</p>
  </div>
</template>

<script>
import { useClanStore } from "~/stores/useClanStore.js";
import SharedTemplatePublicSwitch from "~/components/shared/TemplatePublicSwitch.vue";

export default {
  name: "ClanSettings",
  components: {
    SharedTemplatePublicSwitch
  },
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

<style scoped>
.clan-settings-panel {
  background: #ffffff;
  border-radius: 24px;
  padding: 24px 20px;
  border: 2px solid #f3f4f6;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  max-width: 480px;
  margin: 0 auto;
}

.clan-settings-btn {
  padding: 12px 20px;
  border: 0;
  border-radius: 14px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.15s, transform 0.1s;
}

.clan-settings-btn:active {
  transform: scale(0.97);
}

.clan-settings-btn--primary {
  background: #79D45D;
  border: 2px solid #6FBC58;
  color: #ffffff;
}

.clan-settings-btn--primary:hover {
  filter: brightness(0.97);
}

.clan-settings-btn--primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.clan-settings-btn--secondary {
  background: transparent;
  border: 2px solid #d1d5db;
  color: #6b7280;
}

.clan-settings-btn--secondary:hover {
  background: #f9fafb;
}

.clan-settings-error {
  margin-top: 12px;
  color: #ef4444;
  font-size: 13px;
  font-weight: 500;
  text-align: center;
}
</style>
