<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancar"></div>

    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl relative">
      <button
        @click="tancar"
        class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600"
        title="Tancar"
      >
        <span class="text-2xl">×</span>
      </button>

      <div class="flex flex-col items-center gap-4 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center text-2xl">
          💔
        </div>
        <h2 class="text-lg font-bold text-gray-800">Racha rota</h2>
        <p class="text-sm text-gray-600">
          Tu racha de {{ ratxaAnterior }} días se ha roto.
        </p>
        <button
          class="w-full py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all"
          @click="tancar"
        >
          Entendido
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isOpen: { type: Boolean, required: true },
    ratxaAnterior: { type: Number, required: true, default: 0 }
  },
  methods: {
    tancar: function () {
      this.$emit("close");
    }
  }
};
</script>
