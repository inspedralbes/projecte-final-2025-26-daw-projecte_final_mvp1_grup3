<template>
  <div v-if="show" class="fixed inset-0 z-50 flex flex-col justify-end pointer-events-auto">
    <div class="fixed inset-0 bg-black/50 transition-opacity" @click="$emit('cancel')"></div>

    <div class="relative w-full rounded-t-[32px] overflow-hidden animate-slide-up shadow-[0_-8px_30px_rgba(0,0,0,0.12)] flex flex-col" style="background-color: #FF8DA6;">

      <!-- Handle -->
      <div class="w-full flex justify-center pt-4 pb-2">
        <div class="w-12 h-1.5 bg-white/40 rounded-full"></div>
      </div>

      <div class="px-6 pb-6 pt-2">
        <!-- Icon d'advertència -->
        <div class="flex justify-center mb-3">
          <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
          </div>
        </div>

        <h2 class="text-xl font-['Bricolage_Grotesque'] font-bold text-white mb-2 text-center">
          {{ title || 'Estàs segur?' }}
        </h2>
        <p class="text-white/80 text-center text-sm font-['Comfortaa'] mb-5">
          {{ message || 'Aquesta acció no es pot desfer.' }}
        </p>

        <div class="flex w-full gap-4 items-stretch">
          <button
            type="button"
            @click="$emit('cancel')"
            class="w-1/2 bg-white/20 hover:bg-white/30 text-white font-['Comfortaa'] font-bold py-3 rounded-xl transition-colors flex items-center justify-center shadow-[0_4px_0_rgba(255,255,255,0.15)] active:translate-y-[4px] active:shadow-none"
          >
            Enrere
          </button>
          <button
            type="button"
            @click="$emit('confirm')"
            class="w-1/2 bg-[#FFD166] hover:bg-[#ffc233] text-gray-900 font-['Comfortaa'] font-bold py-3 rounded-xl shadow-[0_4px_0_#d9a738] active:translate-y-[4px] active:shadow-none transition-all flex items-center justify-center"
          >
            {{ confirmText || 'Confirmar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ConfirmModal",
  props: {
    show: { type: Boolean, required: true },
    title: { type: String, default: null },
    message: { type: String, default: null },
    confirmText: { type: String, default: null }
  },
  emits: ["confirm", "cancel"]
};
</script>

<style scoped>
@keyframes slide-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
.animate-slide-up {
  animation: slide-up 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
