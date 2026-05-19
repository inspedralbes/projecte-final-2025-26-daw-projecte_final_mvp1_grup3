<!--
  Component o pagina Nuxt: LevelUpCelebrationModal.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-[#2563eb] transition-all duration-500" @click="tancar">
    <div class="levelup-celebration-box relative w-full max-w-md p-8 flex flex-col items-center text-center select-none">
      
      <div class="absolute inset-0 rounded-full bg-white/20 blur-[80px] pointer-events-none animate-pulse"></div>

      <div class="z-10 mb-16 -mt-16 transform transition-transform duration-700" :class="isPopping ? 'scale-110' : 'scale-90 opacity-0'">
        <h2 class="text-3xl sm:text-4xl font-black text-white tracking-wider uppercase drop-shadow-[0_4px_12px_rgba(0,0,0,0.2)]">
          ¡NIVELL PUJAT!
        </h2>
        <p class="text-white/90 font-medium text-sm sm:text-base mt-2 max-w-xs mx-auto">
          Has arribat al nivell {{ nivell }}. Continua així!
        </p>
      </div>

      <div class="relative z-10 mt-8 mb-24 flex items-center justify-center">
        <div class="absolute w-56 h-56 rounded-full bg-[#93c5fd]/40 blur-3xl transition-all duration-700" :class="isPopping ? 'scale-150 opacity-100' : 'scale-50 opacity-50'"></div>

        <div
          class="levelup-number-container transition-all duration-700 cubic-pop"
          :class="isPopping ? 'number-epic-pop' : 'scale-75 opacity-80'"
        >
          <span class="levelup-number" :class="{ 'levelup-number--animate': isPopping }">
            {{ displayNumber }}
          </span>
        </div>
      </div>

      </div>
    </div>
  </Teleport>
</template>

<script>
export default {
  name: "LevelUpCelebrationModal",
  props: {
    isOpen: { type: Boolean, required: true },
    nivell: { type: Number, required: true, default: 1 }
  },
  emits: ["close"],
  data: function () {
    return {
      displayNumber: this.nivell > 1 ? this.nivell - 1 : 0,
      isPopping: false,
      timeoutId: null
    };
  },
  watch: {
    isOpen: function (newVal) {
      var self = this;
      if (newVal) {
        self.displayNumber = self.nivell > 1 ? self.nivell - 1 : 0;
        self.isPopping = false;
        
        if (self.timeoutId) clearTimeout(self.timeoutId);
        
        self.timeoutId = setTimeout(function () {
          self.displayNumber = self.nivell;
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
.levelup-celebration-box {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
}

.cubic-pop {
  transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.levelup-number-container {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.number-epic-pop {
  transform: scale(1);
  filter: drop-shadow(0 0 50px rgba(255, 255, 255, 0.5));
}

.levelup-number {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 10rem;
  font-weight: 900;
  color: #ffffff;
  line-height: 1;
  text-shadow:
    0 0 40px rgba(147, 197, 253, 0.8),
    0 0 80px rgba(59, 130, 246, 0.5),
    0 8px 20px rgba(0, 0, 0, 0.4);
  letter-spacing: -0.04em;
}

.levelup-number--animate {
  animation: numberPop 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.6) forwards,
             numberPulse 2s ease-in-out 0.7s infinite;
}

@keyframes numberPop {
  0% {
    transform: scale(0.5) rotate(-5deg);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.3) rotate(3deg);
    opacity: 1;
  }
  75% {
    transform: scale(0.95) rotate(-1deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

@keyframes numberPulse {
  0%, 100% {
    transform: scale(1);
    text-shadow:
      0 0 40px rgba(147, 197, 253, 0.8),
      0 0 80px rgba(59, 130, 246, 0.5),
      0 8px 20px rgba(0, 0, 0, 0.4);
  }
  50% {
    transform: scale(1.06);
    text-shadow:
      0 0 60px rgba(147, 197, 253, 1),
      0 0 100px rgba(59, 130, 246, 0.7),
      0 8px 20px rgba(0, 0, 0, 0.4);
  }
}

@media (max-width: 400px) {
  .levelup-number {
    font-size: 7rem;
  }
}
</style>
