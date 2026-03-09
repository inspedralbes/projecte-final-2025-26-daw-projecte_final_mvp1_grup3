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
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-black text-gray-900">{{ title }}</h3>
          <button
            type="button"
            @click="$emit('close')"
            class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600"
          >
            <span class="text-2xl">×</span>
          </button>
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
