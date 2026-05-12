<template>
  <button
    type="button"
    class="calendar-day-cell"
    :disabled="!day"
    :class="{
      'calendar-day-cell--disabled': !day,
      'calendar-day-cell--has-activity': hasSnapshot,
      'calendar-day-cell--today': isToday,
    }"
    @click="handleClick"
  >
    <span v-if="day" class="calendar-day-cell__num">{{ day }}</span>
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
  computed: {
    isToday: function () {
      if (!this.dateStr) return false;
      var parts = String(this.dateStr).split("-");
      if (parts.length !== 3) return false;
      var y = parseInt(parts[0], 10);
      var m = parseInt(parts[1], 10) - 1;
      var d = parseInt(parts[2], 10);
      if (isNaN(y) || isNaN(m) || isNaN(d)) return false;

      var now = new Date();
      return now.getFullYear() === y && now.getMonth() === m && now.getDate() === d;
    }
  },
  methods: {
    handleClick: function () {
      if (this.day && this.dateStr) {
        this.$emit("click", this.dateStr);
      }
    }
  }
};
</script>

<style scoped>
.calendar-day-cell {
  box-sizing: border-box;
  width: 40px;
  height: 40px;
  padding: 0;
  margin: 0;
  border: none;
  border-radius: 10px;
  background: #faf9f9;
  box-shadow: 0 0 0 1px rgba(31, 41, 55, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.15s ease, box-shadow 0.15s ease;
}

.calendar-day-cell:not(.calendar-day-cell--disabled):hover,
.calendar-day-cell:not(.calendar-day-cell--disabled):focus-visible {
  background: #faf9f9;
  box-shadow: 0 0 0 1px rgba(31, 41, 55, 0.2);
  outline: none;
}

.calendar-day-cell--disabled {
  cursor: default;
  pointer-events: none;
  background: transparent;
  box-shadow: none;
}

.calendar-day-cell--has-activity {
  background: #79D45D; /* verd onboarding */
  box-shadow: 0 10px 26px rgba(121, 212, 93, 0.28), 0 0 0 1px rgba(121, 212, 93, 0.65);
}

.calendar-day-cell--has-activity:hover,
.calendar-day-cell--has-activity:focus-visible {
  background: #79D45D;
  box-shadow: 0 10px 26px rgba(121, 212, 93, 0.28), 0 0 0 1px rgba(121, 212, 93, 0.75);
}

.calendar-day-cell--today {
  animation: calendar-day-today-pulse 1.2s ease-in-out infinite;
  outline: 2px solid rgba(121, 212, 93, 0.95);
  outline-offset: 3px;
}

.calendar-day-cell--has-activity.calendar-day-cell--today {
  outline-color: rgba(250, 249, 249, 0.95);
}

@keyframes calendar-day-today-pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.calendar-day-cell__num {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 600;
  font-size: 16px;
  line-height: 1;
  color: #1f2937;
}

.calendar-day-cell--has-activity .calendar-day-cell__num {
  color: #FAF9F9;
}
</style>
