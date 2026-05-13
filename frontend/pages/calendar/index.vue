<template>
  <div class="calendar-page">
    <header class="calendar-page__topbar">
      <button
        type="button"
        class="calendar-page__icon-btn"
        aria-label="Tornar enrere"
        @click="tornar"
      >
        <svg
          class="calendar-page__chevron calendar-page__chevron--back"
          width="73"
          height="73"
          viewBox="0 0 73 73"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <path
            d="M42.5834 54.75L24.3334 36.5L42.5834 18.25L46.8417 22.5083L32.85 36.5L46.8417 50.4917L42.5834 54.75Z"
            fill="#FAF9F9"
          />
        </svg>
      </button>

      <p class="calendar-page__clock" aria-live="polite">{{ clockLabel }}</p>

      <div class="calendar-page__topbar-spacer" aria-hidden="true" />
    </header>

    <div class="calendar-page__month-row">
      <button
        type="button"
        class="calendar-page__icon-btn calendar-page__icon-btn--month"
        aria-label="Mes anterior"
        @click="mesAnterior"
      >
        <svg
          class="calendar-page__month-step-svg"
          width="50"
          height="50"
          viewBox="0 0 50 50"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <path
            d="M15.625 37.5V12.5H11.4584V37.5H15.625ZM38.5417 37.5V12.5L19.7917 25L38.5417 37.5Z"
            fill="#FAF9F9"
          />
        </svg>
      </button>

      <h1 class="calendar-page__month-title">
        {{ titolMes }}
      </h1>

      <button
        type="button"
        class="calendar-page__icon-btn calendar-page__icon-btn--month"
        aria-label="Mes següent"
        @click="mesSeguent"
      >
        <svg
          class="calendar-page__month-step-svg calendar-page__month-step-svg--next"
          width="50"
          height="50"
          viewBox="0 0 50 50"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <path
            d="M15.625 37.5V12.5H11.4584V37.5H15.625ZM38.5417 37.5V12.5L19.7917 25L38.5417 37.5Z"
            fill="#FAF9F9"
          />
        </svg>
      </button>
    </div>

    <div class="calendar-page__grid-area">
      <div v-if="calendarStore.loading" class="calendar-page__loading">
        Carregant…
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
</template>

<script setup>
useHead({
  link: [
    {
      rel: "stylesheet",
      href: "https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600&family=Comfortaa:wght@400;700&display=swap",
    },
  ],
});
</script>

<script>
import { useCalendarStore } from "~/stores/calendar.js";
import { useCalendar } from "~/composables/useCalendar.js";
import UserCalendarCalendarMonthGrid from "~/components/user/calendar/CalendarMonthGrid.vue";

export default {
  name: "CalendarIndexPage",
  components: {
    UserCalendarCalendarMonthGrid: UserCalendarCalendarMonthGrid,
  },
  data: function () {
    return {
      diesMes: [],
      clockLabel: "",
      clockTimer: null,
    };
  },
  computed: {
    calendarStore: function () {
      return useCalendarStore();
    },
    titolMes: function () {
      var cal = useCalendar();
      return cal.formatMonthHeader(
        this.calendarStore.selectedYear,
        this.calendarStore.selectedMonth
      );
    },
  },
  mounted: function () {
    this.carregarMes();
    this.tickClock();
    this.clockTimer = setInterval(this.tickClock, 1000);
  },
  beforeUnmount: function () {
    if (this.clockTimer != null) {
      clearInterval(this.clockTimer);
      this.clockTimer = null;
    }
  },
  methods: {
    tickClock: function () {
      var now = new Date();
      this.clockLabel = now.toLocaleTimeString("ca-ES", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },
    carregarMes: async function () {
      var store = this.calendarStore;
      var dades = await store.fetchMonthSummary(
        store.selectedYear,
        store.selectedMonth
      );
      this.diesMes = dades || [];
    },
    mesAnterior: function () {
      var cal = useCalendar();
      var resultat = cal.prevMonth(
        this.calendarStore.selectedYear,
        this.calendarStore.selectedMonth
      );
      this.calendarStore.selectedYear = resultat.year;
      this.calendarStore.selectedMonth = resultat.month;
      this.carregarMes();
    },
    mesSeguent: function () {
      var cal = useCalendar();
      var resultat = cal.nextMonth(
        this.calendarStore.selectedYear,
        this.calendarStore.selectedMonth
      );
      this.calendarStore.selectedYear = resultat.year;
      this.calendarStore.selectedMonth = resultat.month;
      this.carregarMes();
    },
    onSelectDay: function (dateStr) {
      navigateTo("/home?date=" + encodeURIComponent(dateStr));
    },
    tornar: function () {
      navigateTo("/home");
    },
  },
};
</script>

<style scoped>
.calendar-page {
  min-height: 100vh;
  box-sizing: border-box;
  padding: 18px 16px calc(32px + env(safe-area-inset-bottom, 0px));
  background: transparent;
  color: #1f2937;
  max-width: 430px;
  margin: 0 auto;
}

.calendar-page__topbar {
  display: grid;
  grid-template-columns: 44px 1fr 44px;
  align-items: center;
  width: 100%;
  margin-bottom: 28px;
}

.calendar-page__icon-btn {
  width: 44px;
  height: 44px;
  padding: 0;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: #faf9f9;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.calendar-page__icon-btn--month {
  width: 50px;
  height: 50px;
}

.calendar-page__icon-btn:focus-visible {
  outline: 2px solid #79d45d;
  outline-offset: 2px;
}

.calendar-page__chevron {
  width: 32px;
  height: 32px;
  display: block;
}

.calendar-page__chevron--back {
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
}

.calendar-page__month-step-svg {
  width: 50px;
  height: 50px;
  display: block;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
}

.calendar-page__month-step-svg--next {
  transform: scaleX(-1);
}

.calendar-page__clock {
  margin: 0;
  text-align: center;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 600;
  font-size: 48px;
  line-height: 1.05;
  letter-spacing: -0.02em;
  color: #faf9f9;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
}

.calendar-page__topbar-spacer {
  width: 44px;
  height: 44px;
}

.calendar-page__month-row {
  display: grid;
  grid-template-columns: 50px 1fr 50px;
  align-items: center;
  gap: 8px 12px;
  width: 100%;
}

.calendar-page__month-title {
  margin: 0;
  min-width: 0;
  text-align: center;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 600;
  font-size: 48px;
  line-height: 1.05;
  letter-spacing: -0.02em;
  color: #faf9f9;
  word-break: break-word;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
}

.calendar-page__grid-area {
  margin-top: 71px;
}

.calendar-page__loading {
  text-align: center;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 600;
  font-size: 15px;
  color: #faf9f9;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
  padding: 2rem 0;
}

@media (max-width: 380px) {
  .calendar-page__clock,
  .calendar-page__month-title {
    font-size: clamp(28px, 11vw, 48px);
  }
}
</style>
