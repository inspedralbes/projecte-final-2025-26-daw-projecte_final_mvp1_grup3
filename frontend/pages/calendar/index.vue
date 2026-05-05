<template>
  <div class="min-h-screen pb-12">
    <div class="max-w-3xl mx-auto px-4">

      <!-- Capçalera -->
      <div class="flex items-center justify-between mb-6">
        <button
          class="px-4 py-2 rounded-xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 text-sm font-bold text-gray-700 hover:bg-gray-50 transition"
          @click="tornar"
        >
          ← Tornar
        </button>

        <div class="flex items-center gap-3">
          <button
            class="w-9 h-9 rounded-full bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition font-bold text-lg"
            @click="mesAnterior"
          >
            &lt;
          </button>
          <h1 class="text-xl font-black text-gray-800 min-w-[180px] text-center">
            {{ titolMes }}
          </h1>
          <button
            class="w-9 h-9 rounded-full bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition font-bold text-lg"
            @click="mesSeguent"
          >
            &gt;
          </button>
        </div>

        <div class="w-[88px]"></div>
      </div>

      <!-- Graella del mes -->
      <div class="rounded-2xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 p-6">
        <div v-if="calendarStore.loading" class="text-center py-12 text-gray-400 font-bold">
          Carregant...
        </div>
        <UserCalendarCalendarMonthGrid
          v-else
          :days="diesMes"
          :year="calendarStore.selectedYear"
          :month="calendarStore.selectedMonth"
          @select-day="onSelectDay"
        />
      </div>

    </div>
  </div>
</template>

<script>
import { useCalendarStore } from "~/stores/calendar.js";
import { useCalendar } from "~/composables/useCalendar.js";
import UserCalendarCalendarMonthGrid from "~/components/user/calendar/CalendarMonthGrid.vue";

export default {
  name: "CalendarIndexPage",
  components: {
    UserCalendarCalendarMonthGrid: UserCalendarCalendarMonthGrid
  },
  data: function () {
    return {
      diesMes: []
    };
  },
  computed: {
    calendarStore: function () {
      return useCalendarStore();
    },
    titolMes: function () {
      var cal = useCalendar();
      return cal.formatMonthHeader(this.calendarStore.selectedYear, this.calendarStore.selectedMonth);
    }
  },
  mounted: function () {
    this.carregarMes();
  },
  methods: {
    carregarMes: async function () {
      var store = this.calendarStore;
      var dades = await store.fetchMonthSummary(store.selectedYear, store.selectedMonth);
      this.diesMes = dades || [];
    },
    mesAnterior: function () {
      var cal = useCalendar();
      var resultat = cal.prevMonth(this.calendarStore.selectedYear, this.calendarStore.selectedMonth);
      this.calendarStore.selectedYear = resultat.year;
      this.calendarStore.selectedMonth = resultat.month;
      this.carregarMes();
    },
    mesSeguent: function () {
      var cal = useCalendar();
      var resultat = cal.nextMonth(this.calendarStore.selectedYear, this.calendarStore.selectedMonth);
      this.calendarStore.selectedYear = resultat.year;
      this.calendarStore.selectedMonth = resultat.month;
      this.carregarMes();
    },
    onSelectDay: function (dateStr) {
      navigateTo("/calendar/day?date=" + dateStr);
    },
    tornar: function () {
      navigateTo("/home");
    }
  }
};
</script>
