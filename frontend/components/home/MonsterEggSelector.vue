<template>
  <div class="monster-egg-selector">
    <div class="egg-grid">
      <button
        v-for="egg in eggs"
        :key="egg.type"
        type="button"
        class="egg-item"
        :class="{ 'egg-item--selected': selectedType === egg.type }"
        @click="selectEgg(egg.type)"
      >
        <div class="egg-wrapper">
          <img
            :src="egg.image"
            :alt="egg.label"
            class="egg-image"
            decoding="async"
          />
          <div class="egg-preview" v-if="selectedType === egg.type">
            <img
              :src="egg.previewSprite"
              :alt="egg.label + ' sprite'"
              class="egg-preview-sprite"
              decoding="async"
            />
          </div>
        </div>
        <span class="egg-label">{{ egg.label }}</span>
      </button>
    </div>

    <button
      v-if="selectedType"
      type="button"
      class="confirm-btn"
      :disabled="isConfirming"
      @click="confirmSelection"
    >
      <span v-if="isConfirming" class="loading-spinner-small"></span>
      <span v-else>{{ $t('monster.confirm_selection') || 'Triar ' + selectedLabel }}</span>
    </button>
  </div>
</template>

<script>
import { authFetch } from '~/composables/useApi.js';
import { getBaseUrl } from '~/composables/useApi.js';
import { getEggImage, getMonsterImage } from '~/utils/monsterImage.js';

export default {
  name: 'MonsterEggSelector',
  emits: ['selected', 'confirmed'],
  data: function () {
    return {
      selectedType: null,
      isConfirming: false,
      eggs: [
        {
          type: 'VV',
          label: 'Verd',
          image: getEggImage('V'),
          previewSprite: getMonsterImage('VV', 1),
        },
        {
          type: 'VR',
          label: 'Rosa',
          image: getEggImage('R'),
          previewSprite: getMonsterImage('VR', 1),
        },
        {
          type: 'VL',
          label: 'Lila',
          image: getEggImage('L'),
          previewSprite: getMonsterImage('VL', 1),
        },
        {
          type: 'VA',
          label: 'Amarillo',
          image: getEggImage('A'),
          previewSprite: getMonsterImage('VA', 1),
        },
      ],
    };
  },
  computed: {
    selectedLabel: function () {
      var egg = this.eggs.find(function (e) { return e.type === this.selectedType; }.bind(this));
      return egg ? egg.label : '';
    },
  },
  methods: {
    selectEgg: function (type) {
      this.selectedType = type;
      this.$emit('selected', type);
    },
    confirmSelection: async function () {
      var self = this;
      if (!this.selectedType || this.isConfirming) {
        return;
      }
      this.isConfirming = true;
      try {
        var response = await authFetch(getBaseUrl() + '/api/user/monster-choice', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ monstre_tipus: this.selectedType }),
        });
        var data = await response.json();
        if (response.ok && data.success) {
          this.$emit('confirmed', data.monstre);
        } else {
          var errMsg = data.error || 'Error al triar el monstre';
          if (typeof window !== 'undefined' && window.alert) {
            alert(errMsg);
          }
        }
      } catch (err) {
        console.error('Error confirming monster choice:', err);
        if (typeof window !== 'undefined' && window.alert) {
          alert('Error de connexió en triar el monstre');
        }
      } finally {
        this.isConfirming = false;
      }
    },
  },
};
</script>

<style scoped>
.monster-egg-selector {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
}

.egg-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  width: 100%;
}

.egg-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.8);
  border: 3px solid transparent;
  border-radius: 1.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.egg-item:hover {
  transform: scale(1.05) rotate(-3deg);
  filter: brightness(1.05);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.egg-item--selected {
  border-color: #79d45d;
  background: rgba(121, 212, 93, 0.15);
  animation: egg-wobble 0.5s ease-in-out infinite;
}

@keyframes egg-wobble {
  0%, 100% { transform: rotate(-2deg); }
  50% { transform: rotate(2deg); }
}

.egg-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.egg-image {
  width: 80px;
  height: 80px;
  object-fit: contain;
  transition: transform 0.2s ease;
}

.egg-item--selected .egg-image {
  transform: scale(1.1);
}

.egg-preview {
  position: absolute;
  bottom: -10px;
  right: -10px;
  width: 50px;
  height: 50px;
  animation: preview-bounce 0.6s ease-out;
}

@keyframes preview-bounce {
  0% { transform: scale(0); opacity: 0; }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); opacity: 1; }
}

.egg-preview-sprite {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.egg-label {
  font-family: 'Comfortaa', sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: #2b2d42;
}

.confirm-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  min-height: 52px;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  background: #79d45d;
  color: #faf9f9;
  font-family: 'Comfortaa', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.1s ease;
}

.confirm-btn:hover:not(:disabled) {
  background: #6bc24d;
}

.confirm-btn:active:not(:disabled) {
  transform: scale(0.98);
}

.confirm-btn:disabled {
  background: #a8d5a2;
  cursor: not-allowed;
}

.loading-spinner-small {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>