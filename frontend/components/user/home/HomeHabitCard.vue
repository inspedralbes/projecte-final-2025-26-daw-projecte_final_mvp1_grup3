<template>
  <button
    type="button"
    class="habit-card w-full text-left transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-lime-300"
    :data-testid="'home-habit-card-' + habit.id"
    :class="[
      climaAdvers ? 'ring-1 ring-orange-200' : '',
      completatAvui ? 'opacity-70' : ''
    ]"
    @click="$emit('obrir-detalls', habit)"
  >
    <div class="habit-card__shape" aria-hidden="true">
      <svg width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="colorIndicador" />
      </svg>
    </div>

    <p class="habit-card__title">
      {{ habit.nom }}
    </p>

    <span class="habit-card__dots" aria-hidden="true">
      <span></span>
      <span></span>
      <span></span>
    </span>
  </button>
</template>

<script>
var CATEGORY_COLORS = {
  1: '#4ade80', // verd (exercici / activitat física)
  2: '#60a5fa', // blau (salut / beure aigua)
  3: '#f97316', // taronja (productivitat)
  4: '#a78bfa', // lila (creativitat)
  5: '#f43f5e', // vermell (social)
  6: '#facc15', // groc (ment / meditació)
  7: '#34d399', // verd menta (exterior)
  8: '#fb923c'  // taronja clar (esport)
};

export default {
  name: 'HomeHabitCard',
  props: {
    habit:          { type: Object,  required: true },
    progress:       { type: Number,  default: 0 },
    completatAvui:  { type: Boolean, default: false },
    estaProcessant: { type: Boolean, default: false },
    climaAdvers:    { type: Boolean, default: false }
  },
  computed: {
    colorIndicador: function () {
      var catId = this.habit.categoriaId || this.habit.categoria_id;
      return CATEGORY_COLORS[catId] || '#94a3b8';
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
}

.habit-card__shape {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 40px;
}

.habit-card__title {
  margin: 0;
  color: #2B2D42;
  font-style: normal;
  font-weight: 600;
  font-size: 20px;
  line-height: 24px;
}

.habit-card__dots {
  position: absolute;
  top: 8px;
  right: 12px;
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
