<template>
  <button
    class="relative flex flex-col items-center justify-center min-h-[56px] rounded-xl transition-all duration-200 text-sm"
    :class="day
      ? 'hover:bg-indigo-50 hover:shadow-md cursor-pointer'
      : 'cursor-default'"
    @click="handleClick"
  >
    <span v-if="day" class="font-bold" :class="hasSnapshot ? 'text-gray-800' : 'text-gray-400'">
      {{ day }}
    </span>
    <div v-if="hasSnapshot && categoryColors && categoryColors.length" class="flex gap-0.5 mt-1 flex-wrap justify-center max-w-[40px]">
      <span
        v-for="(color, idx) in categoryColors.slice(0, 4)"
        :key="idx"
        class="w-2 h-2 rounded-full"
        :style="{ backgroundColor: color }"
      ></span>
    </div>
  </button>
</template>

<script>
export default {
  name: "CalendarDayCell",
  props: {
    day: { type: Number, default: null },
    hasSnapshot: { type: Boolean, default: false },
    categoryColors: { type: Array, default: function () { return []; } },
    dateStr: { type: String, default: "" }
  },
  emits: ["click"],
  methods: {
    handleClick: function () {
      if (this.day && this.dateStr) {
        this.$emit("click", this.dateStr);
      }
    }
  }
};
</script>
