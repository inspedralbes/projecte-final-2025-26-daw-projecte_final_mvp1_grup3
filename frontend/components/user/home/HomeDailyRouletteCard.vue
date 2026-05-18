<template>
  <button
    type="button"
    class="tirada-card"
    :class="potTirar ? '' : 'tirada-card--disabled tirada-card--completed'"
    @click="$emit('obrir-ruleta')"
  >
    <div class="tirada-card__icon" aria-hidden="true">
      <img
        :src="rouletteIcon"
        alt=""
        class="tirada-card__img"
        :class="potTirar ? '' : 'tirada-card__img--spent'"
        width="43"
        height="43"
        decoding="async"
        draggable="false"
      />
    </div>

    <div class="tirada-card__text">
      <p class="tirada-card__title">{{ $t('home.daily_spin_title') }}</p>
      <p class="tirada-card__subtitle">
        {{ potTirar ? $t('home.roulette_spin_text') : $t('home.roulette_not_available') }}
      </p>
    </div>

    <span v-if="potTirar" class="tirada-card__dots" aria-hidden="true">
      <span></span>
      <span></span>
      <span></span>
    </span>
  </button>
</template>

<script>
import rouletteIcon from '~/assets/img/Icones/Icona_Ruleta.png';

export default {
  name: 'HomeDailyRouletteCard',
  props: {
    potTirar: { type: Boolean, default: true }
  },
  emits: ['obrir-ruleta'],
  data: function () {
    return {
      rouletteIcon: rouletteIcon
    };
  }
};
</script>

<style scoped>
.tirada-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 70px;
  padding: 6px 36px 6px 66px;
  margin: 0;
  border: 2px solid transparent;
  text-align: left;
  cursor: pointer;
  background-color: #faf9f9;
  border-radius: 10px;
  overflow: hidden;
  box-sizing: border-box;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.2s ease, background-color 0.2s ease;
}

.tirada-card:not(.tirada-card--disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.tirada-card:focus {
  outline: none;
}

.tirada-card:focus-visible {
  box-shadow: 0 0 0 2px rgba(121, 212, 93, 0.45);
}

.tirada-card--disabled {
  cursor: default;
}

.tirada-card--completed {
  border-color: #79d45d;
  background-color: #ecfdf3;
}

.tirada-card__icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 43px;
  height: 43px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tirada-card__img {
  display: block;
  width: 43px;
  height: 43px;
  object-fit: contain;
}

.tirada-card__img--spent {
  filter: grayscale(1);
  opacity: 0.55;
}

.tirada-card__text {
  min-width: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1px;
  justify-content: center;
  min-height: 0;
  overflow: hidden;
}

.tirada-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
  flex-shrink: 0;
}

.tirada-card__subtitle {
  margin: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 500;
  line-height: 1.25;
  color: #707070;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}

.tirada-card__dots {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  align-items: center;
  gap: 3px;
  pointer-events: none;
}

.tirada-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #d9d9d9;
}
</style>
