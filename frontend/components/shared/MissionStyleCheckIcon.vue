<!--
  Component o pagina Nuxt: MissionStyleCheckIcon.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <svg
    :width="numericSize"
    :height="numericSize"
    viewBox="0 0 43 43"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="mission-style-check-icon"
    :class="{ 'mission-style-check-icon--selected': selected }"
    aria-hidden="true"
  >
    <template v-if="selected">
      <circle cx="21.5" cy="21.5" r="21" fill="#79D45D" />
      <path d="M12 24L17.7895 29L29.5 13" stroke="#FAF9F9" stroke-width="4" stroke-linecap="round" />
    </template>
    <template v-else>
      <circle opacity="0.2" cx="21.5" cy="21.5" r="21" fill="#FAF9F9" stroke="#9D9B9B" />
      <path d="M12 24L17.7895 29L29.5 13" stroke="#D8D8D8" stroke-width="4" stroke-linecap="round" />
    </template>
  </svg>
</template>

<script>
export default {
  name: 'MissionStyleCheckIcon',
  props: {
    selected: { type: Boolean, default: false },
    /** Mida en px (amplada i alçada del SVG) */
    size: { type: [Number, String], default: 43 }
  },
  computed: {
    numericSize: function () {
      var n = parseInt(String(this.size), 10);
      if (Number.isNaN(n) || n < 1) {
        return 43;
      }
      return n;
    }
  }
};
</script>

<style scoped>
.mission-style-check-icon {
  display: block;
  flex-shrink: 0;
}

.mission-style-check-icon--selected {
  filter: drop-shadow(0 2px 4px rgba(17, 120, 40, 0.22));
}
</style>
