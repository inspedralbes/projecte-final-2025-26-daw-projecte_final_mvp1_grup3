<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancar"></div>

    <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl relative overflow-hidden">
      <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-full -translate-y-16 translate-x-16 pointer-events-none"></div>
      
      <button
        @click="tancar"
        class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600 z-10"
        title="Tancar"
      >
        <span class="text-2xl">×</span>
      </button>

      <div class="flex flex-col items-center text-center gap-6 relative z-10">
        <div class="bg-orange-100 w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shadow-sm">
          🎡
        </div>
        
        <div>
          <h2 class="text-2xl font-black text-gray-800 tracking-tight">
            {{ $t('home.roulette') }}
          </h2>
          <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1">
            {{ $t('home.roulette_daily') }}
          </p>
        </div>

        <!-- Ruleta Visual (Simplificada con animación) -->
        <div class="relative w-48 h-48 flex items-center justify-center">
          <div 
            class="w-full h-full rounded-full border-8 border-gray-100 relative transition-transform duration-[3000ms] ease-out shadow-inner bg-gradient-to-br from-orange-50 to-white"
            :style="estilRuleta"
          >
            <!-- Sectores decorativos -->
            <div v-for="i in 8" :key="i" class="absolute inset-0 flex items-start justify-center" :style="'transform: rotate(' + (i*45) + 'deg)'">
              <div class="w-1 h-6 bg-gray-100 rounded-full mt-2"></div>
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
               <span class="text-4xl animate-pulse" v-if="!activeSpin">🎁</span>
               <span class="text-4xl" v-else>✨</span>
            </div>
          </div>
          <!-- Indicador superior -->
          <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-8 bg-red-500 rounded-b-full shadow-md z-10"></div>
        </div>

        <div class="w-full space-y-3">
          <button
            v-if="canSpin"
            class="w-full py-4 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-orange-100 transition-all active:scale-95 disabled:opacity-50"
            @click="tirar"
            :disabled="activeSpin"
          >
            {{ $t('home.roulette_spin_text') }}
          </button>
          <div v-else class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-tight">
              {{ $t('home.roulette_not_available') }}
            </p>
          </div>
          
          <button
            class="w-full py-4 text-gray-400 font-bold text-xs uppercase tracking-widest hover:text-gray-600 transition-colors"
            @click="tancar"
          >
            {{ $t('home.back') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RouletteModal',
  props: {
    isOpen: { type: Boolean, required: true },
    canSpin: { type: Boolean, default: false }
  },
  data: function () {
    return {
      activeSpin: false,
      rotation: 0
    };
  },
  computed: {
    estilRuleta: function () {
      return {
        transform: 'rotate(' + this.rotation + 'deg)'
      };
    }
  },
  methods: {
    tancar: function () {
      if (this.activeSpin) return;
      this.$emit("close");
    },
    tirar: function () {
      if (!this.canSpin || this.activeSpin) return;
      
      this.activeSpin = true;
      this.rotation += 1800 + Math.random() * 360; // 5 vueltas + aleatorio
      
      this.$emit("spin");
      
      var self = this;
      setTimeout(function () {
        self.activeSpin = false;
      }, 3000);
    }
  }
};
</script>
