<!--
  Component o pagina Nuxt: AdminPopups.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-gray-900/60 backdrop-blur-md"
      @click.self="$emit('close')"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-auto shadow-2xl p-8">
        <div class="flex flex-col items-center mb-6">
          <div class="w-10 h-1 bg-gray-300 rounded-full mb-3"></div>
          <h3 class="text-xl font-bold text-[#949494] text-center w-full">{{ title }}</h3>
        </div>
        <slot></slot>
      </div>
    </div>
  </Transition>
</template>

<script>
export default {
  name: 'AdminPopups',
  props: {
    isOpen: { type: Boolean, default: false },
    title: { type: String, default: '' }
  }
};
</script>
