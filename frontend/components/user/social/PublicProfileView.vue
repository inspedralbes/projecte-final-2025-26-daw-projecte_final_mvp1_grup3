<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="$emit('close')">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
      <div class="p-6 text-center relative">
        <button
          @click="$emit('close')"
          class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>

        <div v-if="loading" class="py-8 text-gray-500">{{ $t('home.loading') }}</div>
        <div v-else-if="profile" class="space-y-4">
          <div class="w-20 h-20 mx-auto rounded-full bg-blue-100 flex items-center justify-center">
            <span class="text-blue-600 font-bold text-3xl">{{ profile.nom.charAt(0) }}</span>
          </div>

          <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ profile.nom }}</h2>
            <p class="text-gray-500">Nivell {{ profile.nivell }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4 py-4 border-t border-b border-gray-100">
            <div class="text-center col-span-2">
              <p class="text-2xl font-bold text-purple-600">{{ profile.nivell }}</p>
              <p class="text-xs text-gray-500 uppercase">Nivel</p>
            </div>
          </div>

          <div class="mt-4">
            <div class="rounded-2xl bg-gray-50 overflow-hidden flex items-center justify-center border border-gray-100">
              <img :src="imatgeMascota" alt="Monstruo" class="w-40 h-40 object-contain" />
            </div>
          </div>

          <div v-if="profile.logros_showcase && profile.logros_showcase.length > 0" class="mt-4">
            <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Logros en Exposición</p>
            <div class="flex flex-wrap justify-center gap-2">
              <div 
                v-for="logro in profile.logros_showcase" 
                :key="logro.id" 
                class="px-3 py-2 rounded-xl bg-purple-50 border border-purple-100 text-center"
              >
                <p class="text-sm font-bold text-purple-700">🏆 {{ logro.nom }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-red-500">
          {{ $t('profile.error') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authFetch } from "~/composables/useApi.js";
import mascotaImg from "~/assets/img/Mascota.png";

export default {
  name: "PublicProfileView",
  props: {
    userId: {
      type: Number,
      required: true,
    },
  },
  emits: ["close"],
  data() {
    return {
      profile: null,
      loading: true,
      imatgeMascota: mascotaImg,
    };
  },
  async mounted() {
    await this.fetchProfile();
  },
  methods: {
    async fetchProfile() {
      this.loading = true;
      try {
        var resposta = await authFetch("/api/users/" + this.userId + "/profile", {});
        if (resposta.ok) {
          this.profile = await resposta.json();
        }
      } catch (e) {
        console.error("Error carregant perfil:", e);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>