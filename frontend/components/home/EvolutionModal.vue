<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="isOpen" class="evolution-overlay" @click.self="closeModal">
        <div class="evolution-modal">
          <div class="evolution-particles">
            <span v-for="i in 20" :key="i" class="particle" :style="getParticleStyle(i)">✦</span>
          </div>

          <div class="evolution-content">
            <h2 class="evolution-title">{{ title }}</h2>

            <div class="evolution-sprites">
              <div class="evolution-sprite-container">
                <img
                  v-if="spriteAnterior"
                  :src="spriteAnterior"
                  alt="Etapa anterior"
                  class="evolution-sprite evolution-sprite--anterior"
                  decoding="async"
                />
                <span class="evolution-sprite-label">{{ etapaLabelAnterior }}</span>
              </div>

              <div class="evolution-arrow">
                <svg width="48" height="24" viewBox="0 0 48 24" fill="none">
                  <path d="M0 12H44M44 12L32 0M44 12L32 24" stroke="#79D45D" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>

              <div class="evolution-sprite-container">
                <img
                  v-if="spriteActual"
                  :src="spriteActual"
                  alt="Nova etapa"
                  class="evolution-sprite evolution-sprite--actual"
                  decoding="async"
                />
                <span class="evolution-sprite-label evolution-sprite-label--new">{{ etapaLabelActual }}</span>
              </div>
            </div>

            <p class="evolution-message">{{ message }}</p>

            <button type="button" class="evolution-btn" @click="closeModal">
              {{ $t('monster.evolution_ok') || 'Genial!' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'EvolutionModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false,
    },
    monstreTipus: {
      type: String,
      default: null,
    },
    etapaAnterior: {
      type: String,
      default: 'B',
    },
    etapaActual: {
      type: String,
      default: 'N',
    },
    nivell: {
      type: Number,
      default: 1,
    },
  },
  emits: ['close'],
  computed: {
    colorCode: function () {
      if (!this.monstreTipus) return 'V';
      return this.monstreTipus.charAt(0);
    },
    spriteAnterior: function () {
      if (!this.monstreTipus) return null;
      return '/img/monsters/' + this.colorCode + this.etapaAnterior + '.png';
    },
    spriteActual: function () {
      if (!this.monstreTipus) return null;
      return '/img/monsters/' + this.colorCode + this.etapaActual + '.png';
    },
    title: function () {
      return this.$t('monster.evolution_title') || 'Evolució!';
    },
    message: function () {
      return this.$t('monster.evolution_message', { etapa: this.etapaLabelActual }) || 'El teu monstre ara és ' + this.etapaLabelActual + '!';
    },
    etapaLabelAnterior: function () {
      var labels = { B: 'Bebè', N: 'Nen', A: 'Adolescent', M: 'Mamat' };
      return labels[this.etapaAnterior] || this.etapaAnterior;
    },
    etapaLabelActual: function () {
      var labels = { B: 'Bebè', N: 'Nen', A: 'Adolescent', M: 'Mamat' };
      return labels[this.etapaActual] || this.etapaActual;
    },
  },
  methods: {
    closeModal: function () {
      this.$emit('close');
    },
    getParticleStyle: function (index) {
      var angle = (index / 20) * 360;
      var radius = 100 + Math.random() * 50;
      var x = Math.cos((angle * Math.PI) / 180) * radius;
      var y = Math.sin((angle * Math.PI) / 180) * radius;
      var size = 10 + Math.random() * 15;
      var delay = Math.random() * 0.5;
      return {
        '--x': x + 'px',
        '--y': y + 'px',
        fontSize: size + 'px',
        animationDelay: delay + 's',
      };
    },
  },
};
</script>

<style scoped>
.evolution-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.evolution-modal {
  position: relative;
  background: linear-gradient(145deg, #faf9f9, #f0f4e8);
  border-radius: 1.5rem;
  padding: 2rem;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  overflow: hidden;
}

.evolution-particles {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
}

.particle {
  position: absolute;
  color: #ffd700;
  animation: particle-burst 1.5s ease-out forwards;
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.8);
}

@keyframes particle-burst {
  0% {
    transform: translate(0, 0) scale(0);
    opacity: 0;
  }
  20% {
    transform: translate(var(--x), var(--y)) scale(1.2);
    opacity: 1;
  }
  100% {
    transform: translate(calc(var(--x) * 2), calc(var(--y) * 2 - 50px)) scale(0);
    opacity: 0;
  }
}

.evolution-content {
  position: relative;
  z-index: 1;
}

.evolution-title {
  font-family: 'Bricolage Grotesque', sans-serif;
  font-size: 28px;
  font-weight: 700;
  color: #2b2d42;
  margin: 0 0 1.5rem;
}

.evolution-sprites {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.evolution-sprite-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.evolution-sprite {
  width: 100px;
  height: 100px;
  object-fit: contain;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
}

.evolution-sprite--anterior {
  animation: sprite-enter-left 0.5s ease-out;
}

.evolution-sprite--actual {
  animation: sprite-enter-right 0.5s ease-out, sprite-glow 0.8s ease-in-out infinite alternate;
}

@keyframes sprite-enter-left {
  from { transform: translateX(-50px) scale(0.5); opacity: 0; }
  to { transform: translateX(0) scale(1); opacity: 1; }
}

@keyframes sprite-enter-right {
  from { transform: translateX(50px) scale(0.5); opacity: 0; }
  to { transform: translateX(0) scale(1); opacity: 1; }
}

@keyframes sprite-glow {
  from { filter: drop-shadow(0 0 5px rgba(121, 212, 93, 0.5)); }
  to { filter: drop-shadow(0 0 15px rgba(121, 212, 93, 0.9)); }
}

.evolution-sprite-label {
  font-family: 'Comfortaa', sans-serif;
  font-size: 12px;
  font-weight: 700;
  color: #707070;
  background: rgba(255, 255, 255, 0.8);
  padding: 2px 10px;
  border-radius: 999px;
}

.evolution-sprite-label--new {
  color: #79d45d;
  background: rgba(121, 212, 93, 0.2);
}

.evolution-arrow {
  flex-shrink: 0;
}

.evolution-message {
  font-family: 'Comfortaa', sans-serif;
  font-size: 16px;
  color: #4b5563;
  margin: 0 0 1.5rem;
  line-height: 1.4;
}

.evolution-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 140px;
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 0.75rem;
  background: #79d45d;
  color: #faf9f9;
  font-family: 'Comfortaa', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.1s ease;
}

.evolution-btn:hover {
  background: #6bc24d;
}

.evolution-btn:active {
  transform: scale(0.98);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .evolution-modal,
.modal-fade-leave-active .evolution-modal {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-fade-enter-from .evolution-modal,
.modal-fade-leave-to .evolution-modal {
  transform: scale(0.9);
  opacity: 0;
}
</style>