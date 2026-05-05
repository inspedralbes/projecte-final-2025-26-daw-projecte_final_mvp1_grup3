<template>
  <div class="rounded-2xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 p-4 transition-all duration-200 hover:shadow-lg">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <!-- Indicador de color -->
        <div
          class="w-3 h-10 rounded-full flex-shrink-0"
          :style="{ backgroundColor: habit.color || '#a78bfa' }"
        ></div>

        <div class="flex-1 min-w-0">
          <p class="font-bold text-gray-800 truncate">{{ habit.titol }}</p>
          <p class="text-xs text-gray-500">{{ habit.dificultat || "mitjà" }}</p>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-shrink-0">
        <!-- Badge acabat/pendent -->
        <span
          v-if="habit.acabado"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          </svg>
          Completat
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-red-50 text-red-500 text-xs font-bold"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Pendent
        </span>

        <!-- Botó detalls -->
        <button
          class="px-3 py-1.5 bg-white text-indigo-600 border border-indigo-200 rounded-full hover:bg-indigo-50 transition text-xs font-bold"
          @click="$emit('show-details', habit)"
        >
          detalls
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "HabitHistoryCard",
  props: {
    habit: { type: Object, required: true },
    date: { type: String, default: "" }
  },
  emits: ["show-details"]
};
</script>
