<!--
  Component o pagina Nuxt: CalendarDayCell.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <button
    type="button"
    class="calendar-day-cell"
    :disabled="!day || (day && !hasSnapshot)"
    :class="{
      'calendar-day-cell--disabled': !day,
      'calendar-day-cell--no-snapshot': day && !hasSnapshot,
      'calendar-day-cell--has-activity': hasSnapshot,
      'calendar-day-cell--today': isToday,
    }"
    :title="ariaTitle"
    @click="handleClick"
  >
    <span v-if="day" class="calendar-day-cell__num">{{ day }}</span>
    <span
      v-if="day && hasSnapshot && (teGorra || teFons)"
      class="calendar-day-cell__cosmetics"
      aria-hidden="true"
    >
      <span
        v-if="teGorra"
        class="calendar-day-cell__gorra"
        title="Gorra equipada"
      >🧢</span>
      <span
        v-if="teFons"
        class="calendar-day-cell__fons-dot"
        :class="fonsDotClass"
        :title="fonsTitle"
      />
    </span>
  </button>
</template>

<script>
import { fonsDotClassFromKey } from "~/utils/snapshotCosmetics.js";

export default {
  name: "CalendarDayCell",
  props: {
    day: { type: Number, default: null },
    hasSnapshot: { type: Boolean, default: false },
    categoryColors: { type: Array, default: function () { return []; } },
    dateStr: { type: String, default: "" },
    teGorra: { type: Boolean, default: false },
    teFons: { type: Boolean, default: false },
    fonsKey: { type: String, default: null },
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
    },
    fonsDotClass: function () {
      return fonsDotClassFromKey(this.fonsKey);
    },
    fonsTitle: function () {
      if (!this.teFons || !this.fonsKey) {
        return "";
      }
      if (this.fonsKey === "fons_platja") {
        return "Fons platja";
      }
      if (this.fonsKey === "fons_casa") {
        return "Fons casa";
      }
      return "Fons equipat";
    },
    ariaTitle: function () {
      if (!this.day || !this.hasSnapshot) {
        return "";
      }
      var parts = [];
      if (this.teGorra) {
        parts.push("Gorra equipada");
      } else {
        parts.push("Sense gorra");
      }
      if (this.teFons && this.fonsKey) {
        parts.push(this.fonsTitle);
      } else {
        parts.push("Fons per defecte");
      }
      return parts.join(" · ");
    },
  },
  methods: {
    handleClick: function () {
      if (!this.day || !this.dateStr || !this.hasSnapshot) {
        return;
      }
      this.$emit("click", this.dateStr);
    },
  },
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
  position: relative;
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

.calendar-day-cell--no-snapshot {
  cursor: not-allowed;
  opacity: 0.55;
}

.calendar-day-cell--has-activity {
  background: #79D45D;
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

.calendar-day-cell__cosmetics {
  position: absolute;
  right: 2px;
  bottom: 2px;
  display: flex;
  align-items: center;
  gap: 1px;
  pointer-events: none;
}

.calendar-day-cell__gorra {
  font-size: 9px;
  line-height: 1;
  filter: drop-shadow(0 0 1px rgba(0, 0, 0, 0.35));
}

.calendar-day-cell__fons-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.9);
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.15);
}

.calendar-day-cell__fons-dot--platja {
  background: linear-gradient(135deg, #38bdf8 0%, #fde68a 100%);
}

.calendar-day-cell__fons-dot--casa {
  background: linear-gradient(135deg, #a78bfa 0%, #fcd34d 100%);
}
</style>
