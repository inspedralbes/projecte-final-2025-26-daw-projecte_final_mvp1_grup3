<template>
  <component
    :is="readOnly ? 'div' : 'button'"
    :type="readOnly ? undefined : 'button'"
    class="habit-card w-full text-left transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-300"
    :class="[
      readOnly ? 'habit-card--readonly cursor-default' : 'hover:-translate-y-0.5 hover:shadow-md cursor-pointer',
      climaAdvers ? 'ring-1 ring-orange-200' : '',
      completatAvui ? 'habit-card--completed' : ''
    ]"
    :data-testid="'home-habit-card-' + habit.id"
    role="group"
    @click="onCardActivate"
  >
    <div class="habit-card__mark" aria-hidden="true">
      <template v-if="completatAvui">
        <div class="flex items-center justify-center w-full h-full">
          <MissionStyleCheckIcon :selected="true" :size="43" />
        </div>
      </template>
      <template v-else>
        <svg class="habit-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="colorIndicador" />
        </svg>
        <span class="habit-card__icona">{{ iconaMostrada }}</span>
      </template>
    </div>

    <p class="habit-card__title">
      {{ habit.nom }}
    </p>

    <span v-if="!readOnly" class="habit-card__dots" aria-hidden="true">
      <span></span>
      <span></span>
      <span></span>
    </span>
  </component>
</template>

<script>
import MissionStyleCheckIcon from '~/components/shared/MissionStyleCheckIcon.vue';
import { getDefaultColorForCategoryId } from '~/utils/habitCategoryColor.js';
import { normalizeHex } from '~/utils/colorSpace.js';

export default {
  name: 'HomeHabitCard',
  components: {
    MissionStyleCheckIcon
  },
  props: {
    habit:          { type: Object,  required: true },
    progress:       { type: Number,  default: 0 },
    completatAvui:  { type: Boolean, default: false },
    estaProcessant: { type: Boolean, default: false },
    climaAdvers:    { type: Boolean, default: false },
    readOnly:       { type: Boolean, default: false }
  },
  methods: {
    onCardActivate: function () {
      if (this.readOnly) return;
      this.$emit('obrir-detalls', this.habit);
    }
  },
  computed: {
    colorIndicador: function () {
      var c = this.habit && this.habit.color;
      if (c && String(c).trim()) {
        return normalizeHex(String(c).trim());
      }
      var catId = this.habit.categoriaId != null ? this.habit.categoriaId : this.habit.categoria_id;
      return getDefaultColorForCategoryId(Number(catId) || 1);
    },
    iconaMostrada: function () {
      var ic = this.habit && this.habit.icona;
      if (ic && String(ic).trim()) {
        return String(ic).trim();
      }
      return '✨';
    }
  }
};
</script>

<style scoped>
.habit-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 86px;
  padding: 18px 18px 18px 88px;
  background-color: #FAF9F9;
  border-radius: 10px;
  border: 2px solid transparent;
  font: inherit;
}

.habit-card--completed {
  background-color: #ecfdf3;
  border-color: #79d45d;
  border-radius: 14px;
}

.habit-card--readonly {
  user-select: none;
}

.habit-card__mark {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 40px;
}

.habit-card__blob {
  display: block;
  width: 56px;
  height: 40px;
}

.habit-card__icona {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  z-index: 1;
  width: 2rem;
  text-align: center;
  font-size: 1.35rem;
  line-height: 1;
  pointer-events: none;
  text-shadow: 0 0 2px rgba(255, 255, 255, 0.85), 0 1px 2px rgba(0, 0, 0, 0.12);
}

.habit-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.habit-card__dots {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  align-items: center;
  gap: 3px;
}

.habit-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #D9D9D9;
}
</style>
