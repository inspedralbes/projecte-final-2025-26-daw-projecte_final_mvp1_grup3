<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="tancar"></div>

    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[80vh] flex flex-col shadow-2xl relative">
      <button
        @click="tancar"
        class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600 z-10"
        title="Tancar"
      >
        <span class="text-2xl">×</span>
      </button>

      <div class="p-8 border-b border-gray-100">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">
          {{ $t('home.achievements_modal_title') }}
        </h2>
        <p class="text-sm text-gray-500 font-medium mt-1">
          {{ $t('home.achievements_modal_subtitle') }}
        </p>
      </div>

      <div class="flex-1 overflow-y-auto p-8 bg-gray-50/50">
        <div v-if="!logros || logros.length === 0" class="text-center py-12">
          <p class="text-gray-400 font-medium">{{ $t('home.no_achievements') }}</p>
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
          <div
            v-for="logro in logros"
            :key="logro.id"
            class="bg-white rounded-2xl p-4 flex flex-col items-center gap-3 shadow-sm border border-gray-100 hover:shadow-md transition-all group"
          >
            <div
              class="w-16 h-16 rounded-full flex items-center justify-center text-3xl shadow-inner transition-transform group-hover:scale-110"
              :class="logro.obtingut ? 'bg-orange-100' : 'bg-gray-100 grayscale opacity-40'"
            >
              {{ logro.obtingut ? (logro.icona || '🏅') : '🔒' }}
            </div>
            <div class="text-center">
              <p class="text-xs font-black text-gray-800 uppercase tracking-tighter leading-tight">
                {{ logro.nom }}
              </p>
              <p class="text-[10px] text-gray-400 font-medium mt-1 leading-tight">
                {{ logro.descripcio }}
              </p>
            </div>
            <div v-if="logro.obtingut" class="mt-auto">
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-wider">
                {{ $t('home.unlocked') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="p-6 border-t border-gray-100 flex justify-end">
        <button
          class="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-black transition-all shadow-lg"
          @click="tancar"
        >
          {{ $t('home.confirm') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LogrosModal',
  props: {
    isOpen: { type: Boolean, required: true },
    logros: { type: Array, default: function () { return []; } }
  },
  methods: {
    tancar: function () {
      this.$emit("close");
    }
  }
};
</script>
