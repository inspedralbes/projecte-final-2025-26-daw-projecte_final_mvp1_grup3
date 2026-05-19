<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-[#ff4b4b] transition-all duration-500" @click="tancar">
    <div class="streak-celebration-box relative w-full max-w-md p-8 flex flex-col items-center text-center select-none">
      
      <!-- Resplandor de fondo animado -->
      <div class="absolute inset-0 rounded-full bg-white/20 blur-[80px] pointer-events-none animate-pulse"></div>

      <!-- Títol superior -->
      <div class="z-10 mb-16 -mt-16 transform transition-transform duration-700" :class="isPopping ? 'scale-110' : 'scale-90 opacity-0'">
        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-wider uppercase drop-shadow-[0_4px_12px_rgba(0,0,0,0.2)]">
          ¡RATXA AUGMENTADA!
        </h2>
        <p class="text-white/90 font-medium text-sm sm:text-base mt-2 max-w-xs mx-auto">
          ¡Has completat el teu primer hàbit d'avui i la flama continua viva!
        </p>
      </div>

      <!-- Contenidor de la Flama i el Número -->
      <div class="relative z-10 mt-8 mb-24 flex items-center justify-center">
        <!-- Cercle de resplendor darrere la flama -->
        <div class="absolute w-56 h-56 rounded-full bg-[#ffea00]/30 blur-3xl transition-all duration-700" :class="isPopping ? 'scale-150 opacity-100' : 'scale-50 opacity-50'"></div>

        <!-- Flama animada -->
        <div class="relative flex items-center justify-center transition-all duration-700 cubic-pop" :class="isPopping ? 'flame-epic-scale' : 'scale-75 opacity-80'">
          <img
            :src="ratxaIcon"
            alt="Foc de ratxa"
            class="w-56 h-56 sm:w-64 sm:h-64 object-contain drop-shadow-[0_10px_30px_rgba(0,0,0,0.4)] animate-flame-wobble"
            decoding="async"
            draggable="false"
          />

          <!-- Badge del Número dins la flama (més avall) -->
          <div class="absolute inset-0 flex items-center justify-center pt-16 sm:pt-20">
            <div
              class="number-badge flex items-center justify-center transition-all duration-700 cubic-pop"
              :class="isPopping ? 'number-epic-scale' : 'scale-50 opacity-50'"
            >
              <span class="text-5xl sm:text-6xl font-black text-white drop-shadow-[0_4px_10px_rgba(0,0,0,0.6)] tracking-tight">
                {{ displayNumber }}
              </span>
            </div>
          </div>
        </div>
      </div>

      </div>
    </div>
  </Teleport>
</template>

<script>
import ratxaIcon from "~/assets/img/Icones/Icona_Ratxa.png";

export default {
  name: "StreakCelebrationModal",
  props: {
    isOpen: { type: Boolean, required: true },
    ratxa: { type: Number, required: true, default: 1 }
  },
  emits: ["close"],
  data: function () {
    return {
      ratxaIcon: ratxaIcon,
      displayNumber: this.ratxa > 1 ? this.ratxa - 1 : 0,
      isPopping: false,
      timeoutId: null
    };
  },
  watch: {
    isOpen: function (newVal) {
      var self = this;
      if (newVal) {
        self.displayNumber = self.ratxa > 1 ? self.ratxa - 1 : 0;
        self.isPopping = false;
        
        if (self.timeoutId) clearTimeout(self.timeoutId);
        
        self.timeoutId = setTimeout(function () {
          self.displayNumber = self.ratxa;
          self.isPopping = true;
        }, 400);
      } else {
        if (self.timeoutId) clearTimeout(self.timeoutId);
        self.isPopping = false;
      }
    }
  },
  methods: {
    tancar: function () {
      this.$emit("close");
    }
  }
};
</script>

<style scoped>
.streak-celebration-box {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
}

/* Transició elàstica èpica */
.cubic-pop {
  transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Escala èpica de la flama */
.flame-epic-scale {
  transform: scale(1.4);
  filter: drop-shadow(0 0 35px #ff4c4c);
}

/* Escala èpica del número */
.number-epic-scale {
  transform: scale(1.6);
}

/* Oscil·lació suau de la flama per donar vida */
@keyframes flameWobble {
  0%, 100% { transform: rotate(-2deg) scale(1.4); }
  50% { transform: rotate(2deg) scale(1.45); }
}

.flame-epic-scale .animate-flame-wobble {
  animation: flameWobble 3s ease-in-out infinite;
}
</style>
