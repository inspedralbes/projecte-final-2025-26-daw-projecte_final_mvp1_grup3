<!--
  Component o pagina Nuxt: HomeMonsterPanel.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div
    class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-white/50 flex flex-col items-center relative min-h-[500px]"
  >
    <div class="flex items-center justify-between w-full mb-6 relative z-10">
      <div>
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">
          {{ $t("home.monster_title") }}
        </h2>
        <div class="flex items-center gap-2 mt-1">
          <span
            class="bg-green-100 text-green-700 px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider"
          >
            {{ $t("home.level") }} {{ nivellMostrat }}
          </span>
          <button
            v-if="!readonly"
            type="button"
            class="w-8 h-8 rounded-full bg-indigo-50 shadow-[3px_3px_8px_rgba(0,0,0,0.1)] border border-white/60 flex items-center justify-center text-indigo-500 hover:bg-indigo-100 hover:scale-105 transition-all duration-200"
            title="Calendari"
            @click="onCalendari"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </button>
        </div>
      </div>
      <UserHomeHomeStreakSection
        v-if="!readonly"
        :ratxa="ratxa"
        :ratxa-maxima="ratxaMaxima"
        :xp-total="xpTotal"
        :monedes="monedes"
      />
    </div>

    <div class="flex-1 w-full flex items-center justify-center relative">
      <div
        class="w-full h-full rounded-2xl overflow-hidden shadow-inner relative transition-all duration-300"
        :class="classeMarcEmocional"
        :style="estilFons"
      >
        <div class="absolute inset-0 bg-black/5"></div>
        <div class="relative w-full h-full flex items-center justify-center p-8">
          <img
            v-if="imatgeMascotaActual"
            :src="imatgeMascotaActual"
            alt="El teu monstre"
            class="w-48 h-48 lg:w-64 lg:h-64 object-contain drop-shadow-[0_20px_20px_rgba(0,0,0,0.3)] animate-float"
            :class="{ 'animate-equipped': animacioEquipat }"
            @error="onMascotaError"
          />
        </div>
      </div>
    </div>

    <p class="text-center text-gray-500 font-medium text-sm mt-6 max-w-sm">
      {{ $t("home.monster_subtitle") }}
    </p>
    <p
      v-if="readonly && percentatgeHabitsCompletats !== null && percentatgeHabitsCompletats !== undefined"
      class="text-center text-xs font-bold mt-2"
      :class="emocioContenta ? 'text-green-600' : 'text-orange-600'"
    >
      {{ Math.round(percentatgeHabitsCompletats) }}% hàbits completats
    </p>
    <div v-if="readonly" class="mt-2 text-center text-sm text-gray-500">
      <p>XP total: {{ xpTotalMostrat }}</p>
      <p>XP nivell: {{ xpActualMostrat }} / {{ xpObjectiuMostrat }}</p>
    </div>
  </div>
</template>

<script>
import UserHomeHomeStreakSection from "~/components/user/home/HomeStreakSection.vue";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import mascotaImg from "~/assets/img/Monstres/Mascota_Defecte.png";
import { useGameStore } from "~/stores/gameStore.js";
import { useShopStore } from "~/stores/useShopStore.js";
import { getMonsterGorraImage } from "~/utils/monsterImage.js";
import { useAuthStore } from "~/stores/useAuthStore.js";

export default {
  name: "UserHomeHomeMonsterPanel",
  components: {
    UserHomeHomeStreakSection: UserHomeHomeStreakSection,
  },
  props: {
    readonly: {
      type: Boolean,
      default: false,
    },
    snapshotData: {
      type: Object,
      default: null,
    },
    nivell: {
      type: Number,
      default: 1,
    },
    xpTotal: {
      type: Number,
      default: 0,
    },
    xpActualNivel: {
      type: Number,
      default: 0,
    },
    xpObjetivoNivel: {
      type: Number,
      default: 1000,
    },
    ratxa: {
      type: Number,
      default: 0,
    },
    ratxaMaxima: {
      type: Number,
      default: 0,
    },
    monedes: {
      type: Number,
      default: 0,
    },
    emocioContenta: {
      type: Boolean,
      default: true,
    },
    percentatgeHabitsCompletats: {
      type: Number,
      default: null,
    },
  },
  data: function () {
    return {
      imatgeMascota: mascotaImg,
      mascotaGorraDisponible: true,
      animacioEquipat: false,
      estilFons: {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      },
    };
  },
  computed: {
    nivellMostrat: function () {
      if (this.readonly && this.snapshotData) {
        var n = this.snapshotData.nivell;
        if (n !== undefined && n !== null) {
          return n;
        }
        return 1;
      }
      return this.nivell || 1;
    },
    xpTotalMostrat: function () {
      if (this.readonly && this.snapshotData) {
        var x = this.snapshotData.xp_total;
        if (x !== undefined && x !== null) {
          return x;
        }
        return 0;
      }
      return this.xpTotal || 0;
    },
    xpActualMostrat: function () {
      if (this.readonly && this.snapshotData) {
        var x = this.snapshotData.xp_actual_nivel;
        if (x !== undefined && x !== null) {
          return x;
        }
        return 0;
      }
      return this.xpActualNivel || 0;
    },
    xpObjectiuMostrat: function () {
      if (this.readonly && this.snapshotData) {
        var x = this.snapshotData.xp_objetivo_nivel;
        if (x !== undefined && x !== null) {
          return x;
        }
        return 1000;
      }
      return this.xpObjetivoNivel || 1000;
    },
    classeMarcEmocional: function () {
      if (!this.readonly) {
        return "";
      }
      if (this.emocioContenta) {
        return "ring-2 ring-green-200/80 ring-inset";
      }
      return "ring-2 ring-orange-200/80 ring-inset";
    },
    /**
     * Imatge de la mascota a mostrar tenint en compte la skin equipada
     * a través del shopStore. Per a la vista readonly (calendari) sempre
     * usem la imatge base perquè la skin actual no aplica al passat.
     */
    imatgeMascotaActual: function () {
      if (this.readonly) {
        return this.imatgeMascota;
      }
      var shopStore;
      try {
        shopStore = useShopStore();
      } catch (_) {
        return this.imatgeMascota;
      }
      var skinKey = null;
      try {
        var gameStore = useGameStore();
        if (gameStore && gameStore.skinKey) {
          skinKey = gameStore.skinKey;
        }
      } catch (_) {}
      if (!skinKey && shopStore && shopStore.skinEquipat) {
        skinKey = shopStore.skinEquipat;
      }
      if (skinKey === "gorra_monster") {
        try {
          var authStore = useAuthStore();
          if (authStore && authStore.user && authStore.user.monstre_tipus) {
            return getMonsterGorraImage(authStore.user.monstre_tipus, authStore.user.nivell);
          }
        } catch (_) {}
      }
      return this.imatgeMascota;
    },
  },
  mounted: function () {
    var self = this;
    if (!this.readonly) {
      try {
        var shopStore = useShopStore();
        if (shopStore && shopStore.inventari.length === 0 && !shopStore.loading) {
          shopStore.carregarBotiga();
        }
      } catch (_) {
        // Silent fallback en SSR o si el store encara no està disponible.
      }
    }
    var nuxtApp;
    try {
      nuxtApp = useNuxtApp();
    } catch (_) {
      nuxtApp = null;
    }
    var socket = nuxtApp && nuxtApp.$socket ? nuxtApp.$socket : null;
    if (!socket) {
      return;
    }
    this._shopHandler = function (data) {
      if (!data) return;
      if (data.kind === "equipped" || data.kind === "unequipped") {
        self.animacioEquipat = true;
        setTimeout(function () { self.animacioEquipat = false; }, 800);
      }
    };
    socket.on("shop_event", this._shopHandler);
  },
  beforeUnmount: function () {
    var nuxtApp;
    try {
      nuxtApp = useNuxtApp();
    } catch (_) {
      nuxtApp = null;
    }
    var socket = nuxtApp && nuxtApp.$socket ? nuxtApp.$socket : null;
    if (socket && this._shopHandler) {
      socket.off("shop_event", this._shopHandler);
    }
  },
  methods: {
    onCalendari: function () {
      this.$emit("calendari");
    },
    onMascotaError: function () {
      // Fallback si /img/Mascota_Gorra.png no existeix encara.
      if (this.mascotaGorraDisponible) {
        this.mascotaGorraDisponible = false;
      }
    },
  },
};
</script>

<style scoped>
.animate-float {
  animation: float 6s ease-in-out infinite;
}
@keyframes float {
  0% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
  100% {
    transform: translateY(0px);
  }
}

.animate-equipped {
  animation: equipped-bounce 0.8s ease-out;
}
@keyframes equipped-bounce {
  0% {
    transform: scale(1);
  }
  30% {
    transform: scale(1.15) rotate(-5deg);
  }
  60% {
    transform: scale(0.95) rotate(3deg);
  }
  100% {
    transform: scale(1);
  }
}
</style>
