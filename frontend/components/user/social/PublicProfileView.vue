<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="$emit('close')">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 overflow-hidden">
      <div class="p-6 relative">
        <button
          @click="$emit('close')"
          class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>

        <div v-if="loading" class="py-12 text-center text-gray-500">
          <div class="animate-spin inline-block rounded-full h-8 w-8 border-b-2 border-blue-500 mb-2"></div>
          <p>{{ $t('home.loading') }}</p>
        </div>

        <div v-else-if="profile" class="space-y-4">
          <!-- Avatar + Name -->
          <div class="text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mb-3">
              <span class="text-white font-bold text-3xl">{{ profile.nom.charAt(0).toUpperCase() }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">{{ profile.nom }}</h2>
            <p class="text-purple-500 font-semibold">{{ $t('home.level') || 'Nivell' }} {{ profile.nivell }}</p>
          </div>

          <!-- Stats: streak + max streak -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-orange-50 rounded-xl p-3 text-center border border-orange-100">
              <p class="text-2xl font-bold text-orange-500">🔥 {{ profile.streak }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $t('home.streak') || 'Ratxa actual' }}</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-3 text-center border border-amber-100">
              <p class="text-2xl font-bold text-amber-500">⭐ {{ profile.streak_maxima }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $t('home.streak_max') || 'Màxima ratxa' }}</p>
            </div>
          </div>

          <!-- Monster -->
          <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-purple-50 overflow-hidden flex flex-col items-center justify-center border border-gray-100 p-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">{{ $t('home.monster_title') || 'Monstre' }}</p>
            <img :src="imatgeMascota" alt="Monstre" class="w-32 h-32 object-contain drop-shadow-md" />
          </div>

          <!-- Logros showcase -->
          <div v-if="profile.logros_showcase && profile.logros_showcase.length > 0">
            <p class="text-xs text-gray-500 uppercase tracking-widest mb-2 text-center">Logros</p>
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

        <div v-else class="py-8 text-center text-red-500">
          {{ $t('profile.error') || 'Error carregant el perfil' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authFetch } from "~/utils/authFetch.js";
import mascotaImg from "~/assets/img/Mascota.png";

export default {
  name: "PublicProfileView",
  props: {
    userId: {
      type: [Number, String],
      required: true,
    },
  },
  emits: ["close"],
  data: function() {
    return {
      profile: null,
      loading: true,
      imatgeMascota: mascotaImg,
    };
  },
  mounted: async function() {
    await this.fetchProfile();
  },
  methods: {
    fetchProfile: async function() {
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