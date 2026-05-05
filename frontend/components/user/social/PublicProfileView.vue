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
            <div class="w-24 h-24 mx-auto rounded-full overflow-hidden mb-3 shadow-inner" :style="avatarBackgroundStyle">
              <div class="w-full h-full rounded-full bg-white/20 border border-gray-200 p-1 flex items-center justify-center">
                <img
                  :src="imatgeMascota"
                  alt="Monstre del perfil"
                  class="w-full h-full object-contain"
                  decoding="async"
                  draggable="false"
                />
              </div>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">{{ profile.nom }}</h2>
            <p class="text-purple-500 font-semibold">{{ $t('home.level') || 'Nivell' }} {{ profile.nivell }}</p>
          </div>

          <!-- Stats: streak + logros -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-orange-50 rounded-xl p-3 text-center border border-orange-100 min-h-[8rem] flex flex-col justify-center gap-2">
              <p class="text-2xl font-bold text-orange-500">🔥 {{ profile.streak }}</p>
              <p class="text-xs text-gray-500">{{ $t('home.streak') || 'Ratxa actual' }}</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-3 text-center border border-amber-100 min-h-[8rem] flex flex-col justify-center gap-2">
              <p class="text-2xl font-bold text-amber-500">🏅</p>
              <p class="text-xs text-gray-500">Medallas</p>
              <div v-if="profile.logros_showcase && profile.logros_showcase.length > 0" class="mt-2 space-y-1 max-h-20 overflow-y-auto px-2">
                <p
                  v-for="logro in profile.logros_showcase.slice(0,4)"
                  :key="logro.id"
                  class="text-sm font-bold text-amber-700"
                >
                  {{ logro.nom }}
                </p>
                <p v-if="profile.logros_showcase.length > 4" class="text-[10px] text-gray-500">+{{ profile.logros_showcase.length - 4 }} más</p>
              </div>
              <p v-else class="text-sm font-bold text-amber-500 mt-2">Sin logros</p>
            </div>
          </div>

          <!-- Monster -->
          <div class="rounded-2xl overflow-hidden flex flex-col items-center justify-center p-5" :style="avatarBackgroundStyle">
            <div class="w-44 h-44 rounded-full p-2 flex items-center justify-center">
              <img :src="imatgeMascota" alt="Monstre" class="w-full h-full object-contain drop-shadow-md" />
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
import bosqueImg from "~/assets/img/Bosque.png";

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
  computed: {
    avatarBackgroundStyle: function() {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    }
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