<template>
  <div
    class="min-h-screen pb-12"
    @touchstart.passive="onTouchStart"
    @touchend.passive="onTouchEnd"
    @wheel.passive="onWheel"
  >
    <div class="max-w-3xl mx-auto px-4">

      <!-- Capçalera -->
      <div class="flex items-center justify-between mb-6">
        <button
          class="px-4 py-2 rounded-xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 text-sm font-bold text-gray-700 hover:bg-gray-50 transition"
          @click="tornar"
        >
          ← Tornar
        </button>
        <h1 class="text-xl font-black text-gray-800">
          {{ titolDia }}
        </h1>
        <div class="w-[88px]"></div>
      </div>

      <!-- Carregant -->
      <div v-if="calendarStore.loading" class="text-center py-12 text-gray-400 font-bold">
        Carregant...
      </div>

      <!-- Sense dades -->
      <div
        v-else-if="!snapshot"
        class="rounded-2xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 p-12 text-center"
      >
        <p class="text-gray-400 font-bold text-lg">Encara no hi havia dades aquest dia</p>
      </div>

      <!-- Contingut del snapshot -->
      <template v-else>
        <!-- Secció Mascota (mateix panell que la Home, mode només lectura + dades del snapshot) -->
        <div class="mb-6">
          <UserHomeHomeMonsterPanel
            :readonly="true"
            :snapshot-data="snapshot.mascota_json"
            :emocio-contenta="estaContent"
            :percentatge-habits-completats="percentatgeCompletats"
          />
        </div>

        <!-- Economia del dia -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div class="rounded-2xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 p-4 text-center">
            <p class="text-2xl font-black text-green-600">+{{ xpDia }}</p>
            <p class="text-xs text-gray-500 font-bold">XP guanyada</p>
          </div>
          <div class="rounded-2xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 p-4 text-center">
            <p class="text-2xl font-black text-amber-600">+{{ monedesDia }}</p>
            <p class="text-xs text-gray-500 font-bold">Monedes guanyades</p>
          </div>
        </div>

        <!-- Filtre per categoria -->
        <UserCalendarCalendarCategoryFilter
          :habits="habitsJson"
          :model-value="calendarStore.categoryFilter"
          @update:model-value="onCategoriaChange"
        />

        <!-- Llista d'hàbits -->
        <div class="space-y-3">
          <transition-group name="habit-list" tag="div" class="space-y-3">
            <UserCalendarHabitHistoryCard
              v-for="habit in habitsFiltrats"
              :key="habit.id"
              :habit="habit"
              :date="dateParam"
              @show-details="obrirDetalls"
            />
          </transition-group>

          <div
            v-if="habitsFiltrats.length === 0"
            class="rounded-2xl bg-white/80 shadow-[4px_4px_12px_rgba(0,0,0,0.1)] border border-white/60 p-8 text-center"
          >
            <p class="text-gray-400 font-bold">Cap hàbit d'aquesta categoria aquell dia</p>
          </div>
        </div>
      </template>

    </div>
  </div>
</template>

<script>
import { useCalendarStore } from "~/stores/calendar.js";
import { useCalendar } from "~/composables/useCalendar.js";
import { useHabitHistoryModal } from "~/composables/useHabitHistoryModal.js";
import UserCalendarHabitHistoryCard from "~/components/user/calendar/HabitHistoryCard.vue";
import UserCalendarCalendarCategoryFilter from "~/components/user/calendar/CalendarCategoryFilter.vue";
import UserHomeHomeMonsterPanel from "~/components/user/home/HomeMonsterPanel.vue";

export default {
  name: "CalendarDayPage",
  components: {
    UserCalendarHabitHistoryCard: UserCalendarHabitHistoryCard,
    UserCalendarCalendarCategoryFilter: UserCalendarCalendarCategoryFilter,
    UserHomeHomeMonsterPanel: UserHomeHomeMonsterPanel,
  },
  data: function () {
    return {
      snapshot: null,
      dateParam: "",
      touchStartX: null,
      touchStartY: null,
      ultimGestMs: 0,
      navegantDia: false
    };
  },
  watch: {
    "$route.query.date": function (novaData) {
      if (!novaData || novaData === this.dateParam) return;
      this.dateParam = novaData;
      this.carregarSnapshot();
    }
  },
  computed: {
    calendarStore: function () {
      return useCalendarStore();
    },
    titolDia: function () {
      if (!this.dateParam) return "";
      var cal = useCalendar();
      var localeActual = "ca";
      if (this.$i18n && this.$i18n.locale) {
        localeActual = this.$i18n.locale;
      }
      return cal.formatRelativeDayLabel(this.dateParam, localeActual);
    },
    percentatgeCompletats: function () {
      var cal = useCalendar();
      if (this.snapshot && this.snapshot.habits_json) {
        return cal.getCompletionRate(this.snapshot.habits_json);
      }
      return 0;
    },
    estaContent: function () {
      return this.percentatgeCompletats >= 50;
    },
    xpDia: function () {
      if (this.snapshot && this.snapshot.economia_json) {
        return this.snapshot.economia_json.xp_guanyada_avui || 0;
      }
      return 0;
    },
    monedesDia: function () {
      if (this.snapshot && this.snapshot.economia_json) {
        return this.snapshot.economia_json.monedes_guanyades_avui || 0;
      }
      return 0;
    },
    habitsJson: function () {
      if (this.snapshot && this.snapshot.habits_json) {
        return this.snapshot.habits_json;
      }
      return [];
    },
    habitsFiltrats: function () {
      var filter = this.calendarStore.categoryFilter;
      if (!filter) return this.habitsJson;
      var resultat = [];
      for (var i = 0; i < this.habitsJson.length; i++) {
        if (String(this.habitsJson[i].categoria_id) === String(filter)) {
          resultat.push(this.habitsJson[i]);
        }
      }
      return resultat;
    }
  },
  mounted: function () {
    var route = useRoute();
    this.dateParam = route.query.date || "";
    if (this.dateParam) {
      this.carregarSnapshot();
    }
  },
  methods: {
    carregarSnapshot: async function () {
      var store = this.calendarStore;
      this.snapshot = await store.fetchDaySnapshot(this.dateParam);
    },
    onCategoriaChange: function (valor) {
      if (valor !== null && valor !== undefined) {
        this.calendarStore.setFilter(valor);
      } else {
        this.calendarStore.clearFilter();
      }
    },
    obrirDetalls: function (habit) {
      var modal = useHabitHistoryModal();
      modal.openHabitHistoryModal(habit, this.dateParam);
    },
    potProcessarGest: function () {
      if (this.navegantDia) return false;
      var ara = Date.now();
      if ((ara - this.ultimGestMs) < 250) return false;
      this.ultimGestMs = ara;
      return true;
    },
    navegarRelatiu: async function (diesOffset) {
      if (!this.dateParam || !diesOffset) return;
      var cal = useCalendar();
      var novaData = cal.addDays(this.dateParam, diesOffset);
      if (!novaData) return;
      if (cal.isAfterToday(novaData)) return;

      this.navegantDia = true;
      try {
        this.dateParam = novaData;
        await this.carregarSnapshot();
        await navigateTo("/calendar/day?date=" + novaData);
      } finally {
        this.navegantDia = false;
      }
    },
    onTouchStart: function (event) {
      if (!event || !event.changedTouches || !event.changedTouches[0]) return;
      this.touchStartX = event.changedTouches[0].clientX;
      this.touchStartY = event.changedTouches[0].clientY;
    },
    onTouchEnd: function (event) {
      if (!this.potProcessarGest()) return;
      if (this.touchStartX === null || this.touchStartY === null) return;
      if (!event || !event.changedTouches || !event.changedTouches[0]) return;

      var endX = event.changedTouches[0].clientX;
      var endY = event.changedTouches[0].clientY;
      var deltaX = endX - this.touchStartX;
      var deltaY = endY - this.touchStartY;

      this.touchStartX = null;
      this.touchStartY = null;

      if (Math.abs(deltaX) < 40) return;
      if (Math.abs(deltaX) <= Math.abs(deltaY)) return;

      if (deltaX < 0) {
        this.navegarRelatiu(1);
      } else {
        this.navegarRelatiu(-1);
      }
    },
    onWheel: function (event) {
      if (!this.potProcessarGest()) return;
      if (!event) return;
      if (Math.abs(event.deltaX) < 25) return;
      if (Math.abs(event.deltaX) <= Math.abs(event.deltaY)) return;

      if (event.deltaX < 0) {
        this.navegarRelatiu(1);
      } else {
        this.navegarRelatiu(-1);
      }
    },
    tornar: function () {
      if (this.dateParam) {
        var parts = this.dateParam.split("-");
        if (parts.length >= 2) {
          this.calendarStore.selectedYear = parseInt(parts[0], 10);
          this.calendarStore.selectedMonth = parseInt(parts[1], 10);
        }
      }
      navigateTo("/calendar");
    }
  }
};
</script>

<style scoped>
.habit-list-enter-active,
.habit-list-leave-active {
  transition: all 0.3s ease;
}
.habit-list-enter-from,
.habit-list-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
