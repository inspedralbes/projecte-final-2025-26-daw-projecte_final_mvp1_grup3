<!--
  Component o pagina Nuxt: HomeMissionCard.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="mission-card" :class="{ 'mission-card--completada': estaCompletada }">
    <template v-if="!estaCompletada">
      <div class="mission-card__icon" aria-hidden="true">
        <MissionStyleCheckIcon :selected="false" :size="43" />
      </div>

      <div class="mission-card__text">
        <p class="mission-card__title">Missió Diaria</p>
        <p class="mission-card__subtitle">
          <template v-if="missioDiaria && missioDiaria.titol">{{ missioDiaria.titol }}</template>
          <template v-else>{{ $t('home.loading') }}</template>
        </p>
      </div>

      <span class="mission-card__dots" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
      </span>
    </template>

    <template v-else>
      <div class="mission-card__completada-inner">
        <div class="mission-card__check" aria-hidden="true">
          <MissionStyleCheckIcon :selected="true" :size="43" />
        </div>
        <div class="mission-card__text mission-card__text--completada">
          <p class="mission-card__title">Missió Diaria</p>
          <p class="mission-card__subtitle">
            <template v-if="missioDiaria && missioDiaria.titol">{{ missioDiaria.titol }}</template>
            <template v-else>{{ $t('home.loading') }}</template>
          </p>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import MissionStyleCheckIcon from '~/components/shared/MissionStyleCheckIcon.vue';

export default {
  name: 'HomeMissionCard',
  components: {
    MissionStyleCheckIcon
  },
  props: {
    missioDiaria: { type: Object, default: null },
    missioCompletada: { type: Boolean, default: false },
    missioProgres: { type: Number, default: 0 },
    missioObjectiu: { type: Number, default: 1 }
  },
  computed: {
    estaCompletada: function () {
      if (this.missioCompletada) return true;
      if (this.missioDiaria && this.missioDiaria.completada === true) return true;
      return false;
    }
  }
};
</script>

<style scoped>
.mission-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 70px;
  padding: 6px 36px 6px 66px;
  background-color: #faf9f9;
  border-radius: 10px;
  overflow: hidden;
  box-sizing: border-box;
  border: 2px solid transparent;
}

.mission-card--completada {
  padding: 12px 16px 12px 16px;
  min-height: 64px;
  border-color: #79d45d;
  background-color: #ecfdf3;
  border-radius: 14px;
}

.mission-card__completada-inner {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-width: 0;
}

.mission-card__check {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mission-card__text--completada {
  flex: 1;
  min-width: 0;
}

.mission-card__icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 43px;
  height: 43px;
  flex-shrink: 0;
}

.mission-card__icon :deep(svg) {
  display: block;
  width: 43px;
  height: 43px;
}

.mission-card__text {
  min-width: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1px;
  justify-content: center;
  min-height: 0;
  overflow: hidden;
}

.mission-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
  flex-shrink: 0;
}

.mission-card__subtitle {
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

.mission-card__dots {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  align-items: center;
  gap: 3px;
}

.mission-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #d9d9d9;
}
</style>
