<template>
  <div class="w-full">
    <!-- Capçalera dies de la setmana -->
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div
        v-for="dia in diesSetmana"
        :key="dia"
        class="text-center text-xs font-bold text-gray-500 uppercase tracking-wider py-2"
      >
        {{ dia }}
      </div>
    </div>

    <!-- Graella de dies -->
    <div class="grid grid-cols-7 gap-1">
      <!-- Cel·les buides abans del primer dia -->
      <div v-for="n in cellesBuides" :key="'empty-' + n"></div>

      <!-- Cel·les dels dies -->
      <CalendarDayCell
        v-for="dia in diesDelMes"
        :key="dia.day"
        :day="dia.day"
        :has-snapshot="dia.hasSnapshot"
        :category-colors="dia.categoryColors"
        :date-str="dia.dateStr"
        @click="onSelectDay"
      />
    </div>
  </div>
</template>

<script>
import { useCalendar } from "~/composables/useCalendar.js";
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
      return cal.DIES_SETMANA_CAT;
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
        resultat.push({
          day: i,
          hasSnapshot: !!info.has_snapshot,
          categoryColors: info.category_colors || [],
          dateStr: cal.formatDate(this.year, this.month, i)
        });
      }
      return resultat;
    }
  },
  methods: {
    onSelectDay: function (dateStr) {
      this.$emit("select-day", dateStr);
    }
  }
};
</script>
