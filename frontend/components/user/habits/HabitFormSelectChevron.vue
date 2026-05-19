<!--
  Component o pagina Nuxt: HabitFormSelectChevron.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>

  <svg

    width="25"

    height="16"

    viewBox="0 0 25 16"

    fill="none"

    xmlns="http://www.w3.org/2000/svg"

    class="habit-form-select-chevron shrink-0"

    :style="svgStyle"

    aria-hidden="true"

  >

    <path

      d="M2.00002 2L12.2237 13L23 2"

      stroke="currentColor"

      stroke-width="4"

      stroke-linecap="round"

    />

  </svg>

</template>



<script>

export default {

  name: 'HabitFormSelectChevron',

  props: {

    /** Color del traç (p.ex. contrast sobre fons de categoria). Buit = gris per defecte. */

    color: { type: String, default: '' }

  },

  computed: {

    svgStyle: function () {

      return { color: this.color && String(this.color).trim() ? this.color : '#D8D8D8' }

    }

  }

}

</script>


