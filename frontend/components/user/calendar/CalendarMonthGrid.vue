<!--
  Component o pagina Nuxt: CalendarMonthGrid.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="calendar-month-grid">
    <div class="calendar-month-grid__weekdays" role="row">
      <div
        v-for="dia in diesSetmana"
        :key="dia"
        class="calendar-month-grid__wd"
      >
        {{ dia }}
      </div>
    </div>

    <div class="calendar-month-grid__days" role="grid" :aria-label="ariaMes">
      <div
        v-for="n in cellesBuides"
        :key="'empty-start-' + n"
        class="calendar-month-grid__pad"
        aria-hidden="true"
      />

      <CalendarDayCell
        v-for="dia in diesDelMes"
        :key="dia.day"
        :day="dia.day"
        :has-snapshot="dia.hasSnapshot"
        :category-colors="dia.categoryColors"
        :date-str="dia.dateStr"
        :te-gorra="dia.teGorra"
        :te-fons="dia.teFons"
        :fons-key="dia.fonsKey"
        @click="onSelectDay"
      />

      <div
        v-for="n in cellesFi"
        :key="'empty-end-' + n"
        class="calendar-month-grid__pad"
        aria-hidden="true"
      />
    </div>
  </div>
</template>

<script>
import { useCalendar } from "~/composables/useCalendar.js";
import { cosmeticsFromDaySummary } from "~/utils/snapshotCosmetics.js";
import CalendarDayCell from "./CalendarDayCell.vue";

export default {
  name: "CalendarMonthGrid",
  components: { CalendarDayCell: CalendarDayCell },
  props: {
    days: { type: Array, default: function () { return []; } },
    year: { type: Number, required: true },
    month: { type: Number, required: true }
  },
  emits: ["select-day"],
  computed: {
    diesSetmana: function () {
      var cal = useCalendar();
      return cal.DIES_SETMANA_GRID_CA;
    },
    cellesBuides: function () {
      var cal = useCalendar();
      return cal.getFirstDayOfMonth(this.year, this.month) - 1;
    },
    diesDelMes: function () {
      var cal = useCalendar();
      var totalDies = cal.getDaysInMonth(this.year, this.month);
      var resultat = [];
      var daysMap = {};
      var i;

      for (i = 0; i < this.days.length; i++) {
        var item = this.days[i];
        daysMap[item.day] = item;
      }

      for (i = 1; i <= totalDies; i++) {
        var info = daysMap[i] || {};
        var cosmetics = cosmeticsFromDaySummary(info);
        resultat.push({
          day: i,
          hasSnapshot: !!info.has_snapshot,
          categoryColors: info.category_colors || [],
          dateStr: cal.formatDate(this.year, this.month, i),
          teGorra: cosmetics.te_gorra,
          teFons: cosmetics.te_fons,
          fonsKey: cosmetics.fons_key,
        });
      }
      return resultat;
    },
    ocupades: function () {
      return this.cellesBuides + this.diesDelMes.length;
    },
    cellesFi: function () {
      var r = this.ocupades % 7;
      if (r === 0) {
        return 0;
      }
      return 7 - r;
    },
    ariaMes: function () {
      var cal = useCalendar();
      return cal.formatMonthHeader(this.year, this.month);
    }
  },
  methods: {
    onSelectDay: function (dateStr) {
      this.$emit("select-day", dateStr);
    }
  }
};
</script>

<style scoped>
.calendar-month-grid {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.calendar-month-grid__weekdays {
  display: grid;
  grid-template-columns: repeat(7, 40px);
  column-gap: 15px;
  justify-content: center;
}

.calendar-month-grid__days {
  display: grid;
  grid-template-columns: repeat(7, 40px);
  column-gap: 15px;
  row-gap: 50px;
  justify-content: center;
  margin-top: 68px;
}

.calendar-month-grid__wd {
  box-sizing: border-box;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #faf9f9;
  box-shadow: 0 0 0 1px rgba(31, 41, 55, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 700;
  font-size: 10px;
  line-height: 1.1;
  text-align: center;
  color: #4b5563;
  padding: 2px;
}

.calendar-month-grid__pad {
  width: 40px;
  height: 40px;
}
</style>
