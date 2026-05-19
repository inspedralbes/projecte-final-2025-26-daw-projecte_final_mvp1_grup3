<template>
  <div class="monster-display" :class="{ 'monster-display--animate': animate }">
    <img
      v-if="spriteUrl"
      :src="spriteUrl"
      :alt="monsterName"
      class="monster-sprite"
      :class="{ 'monster-sprite--float': animateFloat }"
      :title="tooltipText"
      decoding="async"
      draggable="false"
      @error="onImageError"
    />
    <div v-if="showStage" class="monster-stage-badge">
      {{ stageLabel }}
    </div>
  </div>
</template>

<script>
import { getMonsterImage, getEtapa } from '~/utils/monsterImage.js';

export default {
  name: 'MonsterDisplay',
  props: {
    tipus: {
      type: String,
      default: null,
    },
    nivell: {
      type: Number,
      default: 1,
    },
    animate: {
      type: Boolean,
      default: false,
    },
    animateFloat: {
      type: Boolean,
      default: false,
    },
    showStage: {
      type: Boolean,
      default: true,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
  },
  data: function () {
    return {
      imageError: false,
    };
  },
  computed: {
    colorCode: function () {
      if (!this.tipus || this.tipus.length < 2) return 'V';
      return this.tipus.charAt(1);
    },
    etapa: function () {
      return getEtapa(this.nivell);
    },
    spriteUrl: function () {
      if (!this.tipus) return null;
      if (this.imageError) return null;
      return getMonsterImage(this.tipus, this.nivell);
    },
    monsterName: function () {
      var colorNames = { V: 'Verde', R: 'Rosa', L: 'Lila', A: 'Amarillo' };
      return (colorNames[this.colorCode] || 'Monster') + ' ' + this.stageLabel;
    },
    tooltipText: function () {
      return 'Nivell ' + this.nivell + ' - ' + this.stageLabel;
    },
    stageLabel: function () {
      var labels = { B: 'Bebè', N: 'Nen', A: 'Adolescent', M: 'Mamat' };
      return labels[this.etapa] || this.etapa;
    },
  },
  methods: {
    onImageError: function () {
      this.imageError = true;
    },
  },
};
</script>

<style scoped>
.monster-display {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.monster-sprite {
  width: 100%;
  height: auto;
  max-width: 200px;
  max-height: 200px;
  object-fit: contain;
  drop-shadow: 0 10px 15px rgba(0, 0, 0, 0.25);
  transition: transform 0.3s ease, filter 0.3s ease;
}

.monster-sprite--float {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

.monster-display--animate .monster-sprite {
  animation: evolve-glow 1s ease-out;
}

@keyframes evolve-glow {
  0% { transform: scale(1); filter: brightness(1) drop-shadow(0 0 0 transparent); }
  30% { transform: scale(1.3); filter: brightness(1.4) drop-shadow(0 0 20px gold); }
  60% { transform: scale(0.95); filter: brightness(1.1); }
  100% { transform: scale(1); filter: brightness(1) drop-shadow(0 0 8px rgba(0,0,0,0.2)); }
}

.monster-stage-badge {
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(121, 212, 93, 0.9);
  color: #fff;
  font-family: 'Comfortaa', sans-serif;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 999px;
  white-space: nowrap;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}
</style>