<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-xl border border-white/50">
    <div class="space-y-5">
      <div class="grid grid-cols-1 gap-5">
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 px-1">{{ $t('habits.repetition') }}</label>
          <button
            type="button"
            class="w-full bg-gray-50/50 border-2 border-gray-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all cursor-pointer font-bold text-gray-800 flex items-center justify-between"
            @click="obrirSelectorRepeticio"
          >
            <span>{{ etiquetaRepeticio }}</span>
            <span class="text-gray-400 text-lg leading-none">⌄</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <Teleport to="body">
    <!-- Sheet principal: repetició -->
    <Transition name="rep-backdrop">
      <div
        v-if="selectorObert"
        class="fixed inset-0 z-[84] bg-black/40"
        @click="tancarSelectorRepeticio"
      ></div>
    </Transition>

    <Transition name="rep-sheet">
      <div
        v-if="selectorObert"
        class="fixed left-0 right-0 bottom-0 z-[85] bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 max-h-[80vh] flex flex-col"
      >
        <div class="px-5 pt-4 pb-3 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-base font-black text-gray-800">{{ $t('habits.define_repetition') }}</h3>
          <button
            type="button"
            class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 text-xl font-bold"
            @click="tancarSelectorRepeticio"
          >
            ×
          </button>
        </div>

        <div class="overflow-y-auto p-5 space-y-6 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
          <div>
            <label class="block text-sm font-black text-gray-700 mb-3">{{ $t('habits.end_date') }}</label>
            <button
              type="button"
              class="w-full flex items-center justify-between rounded-2xl border-2 px-5 py-3.5 transition"
              :class="mostrarInputData ? 'border-green-400 bg-green-50/50' : 'border-gray-100 bg-gray-50/50'"
              @click="mostrarInputData = !mostrarInputData"
            >
              <span class="font-semibold text-gray-700">{{ modelValue.dataFinalitzacio ? modelValue.dataFinalitzacio : $t('habits.end_date_never') }}</span>
              <span class="text-gray-400 text-lg leading-none">⌄</span>
            </button>
            <div v-if="mostrarInputData" class="mt-3 space-y-2">
              <button
                type="button"
                class="w-full text-left rounded-xl border-2 px-4 py-2.5 text-sm font-semibold transition"
                :class="!modelValue.dataFinalitzacio ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-100 bg-white text-gray-600 hover:border-green-200'"
                @click="seleccionarDataFi('')"
              >
                {{ $t('habits.end_date_never') }}
              </button>
              <input
                type="date"
                :value="modelValue.dataFinalitzacio || ''"
                class="w-full bg-white border-2 border-gray-100 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-400"
                :min="minDate"
                @input="seleccionarDataFi($event.target.value)"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-black text-gray-700 mb-3">{{ $t('habits.repetition') }}</label>
            <div class="space-y-2">
              <button
                v-for="opcio in opcionsRepeticio"
                :key="opcio.valor"
                type="button"
                class="w-full flex items-center justify-between rounded-2xl border-2 px-5 py-3.5 text-left transition"
                :class="modeRepeticio === opcio.valor ? 'bg-green-50/60 border-green-400' : 'bg-gray-50/50 border-gray-100 hover:border-green-200'"
                @click="seleccionarModeRepeticio(opcio.valor)"
              >
                <span class="font-semibold text-gray-800">{{ opcio.etiqueta }}</span>
                <span
                  class="w-7 h-7 rounded-full flex items-center justify-center text-sm transition-all"
                  :class="modeRepeticio === opcio.valor ? 'bg-green-500 text-white shadow-md' : 'bg-gray-200 text-gray-400'"
                >
                  ✓
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Sheet secundari: Repetició personalitzada -->
    <Transition name="rep-backdrop">
      <div
        v-if="customSheetObert"
        class="fixed inset-0 z-[88] bg-black/40"
        @click="tancarCustomSheet"
      ></div>
    </Transition>

    <Transition name="rep-sheet">
      <div
        v-if="customSheetObert"
        class="fixed left-0 right-0 bottom-0 z-[89] bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 max-h-[82vh] flex flex-col"
      >
        <div class="px-5 pt-4 pb-3 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-base font-black text-gray-800">{{ $t('habits.custom_repetition_title') }}</h3>
          <button
            type="button"
            class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 text-xl font-bold"
            @click="tancarCustomSheet"
          >
            ×
          </button>
        </div>

        <div class="overflow-y-auto p-5 space-y-5 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
          <div>
            <label class="block text-sm font-black text-gray-700 mb-3">{{ $t('habits.custom_options') }}</label>
            <div class="flex rounded-2xl border-2 border-gray-100 overflow-hidden">
              <button
                v-for="tab in customTabs"
                :key="tab.valor"
                type="button"
                class="flex-1 py-3 text-sm font-bold text-center transition-all"
                :class="customTab === tab.valor ? 'bg-white text-gray-800 shadow-sm border-b-2 border-green-500' : 'bg-gray-50 text-gray-400 hover:text-gray-600'"
                @click="seleccionarCustomTab(tab.valor)"
              >
                {{ tab.etiqueta }}
              </button>
            </div>
          </div>

          <div v-if="customTab === 'setmanalment'" class="space-y-4">
            <div class="flex items-center gap-3">
              <span class="text-sm font-bold text-gray-600 shrink-0">{{ $t('habits.custom_every') }}</span>
              <button
                type="button"
                class="flex-1 flex items-center justify-between rounded-2xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 transition hover:border-green-200"
                @click="mostrarIntervalSelect = !mostrarIntervalSelect"
              >
                <span class="text-sm font-semibold text-gray-700">{{ etiquetaIntervalSetmanal }}</span>
                <span class="text-gray-400 text-lg leading-none">⌄</span>
              </button>
            </div>
            <div v-if="mostrarIntervalSelect" class="space-y-1 rounded-2xl border-2 border-gray-100 bg-white p-2 max-h-40 overflow-y-auto">
              <button
                v-for="n in 4"
                :key="n"
                type="button"
                class="w-full text-left rounded-xl px-4 py-2 text-sm font-semibold transition"
                :class="customInterval === n ? 'bg-green-50 text-green-700 border border-green-300' : 'text-gray-600 hover:bg-gray-50'"
                @click="customInterval = n; mostrarIntervalSelect = false"
              >
                {{ etiquetaIntervalSetmanalN(n) }}
              </button>
            </div>

            <div class="flex flex-wrap gap-2">
              <button
                v-for="(day, index) in diesLlargs"
                :key="day.key"
                type="button"
                @click="toggleDayLocal(index)"
                :class="['px-3 py-2.5 rounded-xl font-bold transition-all text-xs', isDaySelected(index) ? 'bg-green-500 text-white shadow-md' : 'bg-gray-100 text-gray-400 hover:bg-gray-200']"
              >
                {{ day.label }}
              </button>
            </div>
          </div>

          <div v-else-if="customTab === 'diariament'" class="space-y-4">
            <div class="flex items-center gap-3">
              <span class="text-sm font-bold text-gray-600 shrink-0">{{ $t('habits.custom_every') }}</span>
              <button
                type="button"
                class="flex-1 flex items-center justify-between rounded-2xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 transition hover:border-green-200"
                @click="mostrarIntervalSelectDiari = !mostrarIntervalSelectDiari"
              >
                <span class="text-sm font-semibold text-gray-700">{{ etiquetaIntervalDiari }}</span>
                <span class="text-gray-400 text-lg leading-none">⌄</span>
              </button>
            </div>
            <div v-if="mostrarIntervalSelectDiari" class="space-y-1 rounded-2xl border-2 border-gray-100 bg-white p-2 max-h-40 overflow-y-auto">
              <button
                v-for="n in 7"
                :key="n"
                type="button"
                class="w-full text-left rounded-xl px-4 py-2 text-sm font-semibold transition"
                :class="customIntervalDiari === n ? 'bg-green-50 text-green-700 border border-green-300' : 'text-gray-600 hover:bg-gray-50'"
                @click="customIntervalDiari = n; mostrarIntervalSelectDiari = false"
              >
                {{ etiquetaIntervalDiariN(n) }}
              </button>
            </div>
          </div>

          <div v-else-if="customTab === 'mensualment'" class="space-y-4">
            <div class="flex items-center gap-3">
              <span class="text-sm font-bold text-gray-600 shrink-0">{{ $t('habits.custom_every') }}</span>
              <button
                type="button"
                class="flex-1 flex items-center justify-between rounded-2xl border-2 border-gray-100 bg-gray-50/50 px-4 py-3 transition hover:border-green-200"
                @click="mostrarIntervalSelectMensual = !mostrarIntervalSelectMensual"
              >
                <span class="text-sm font-semibold text-gray-700">{{ etiquetaIntervalMensual }}</span>
                <span class="text-gray-400 text-lg leading-none">⌄</span>
              </button>
            </div>
            <div v-if="mostrarIntervalSelectMensual" class="space-y-1 rounded-2xl border-2 border-gray-100 bg-white p-2 max-h-40 overflow-y-auto">
              <button
                v-for="n in 6"
                :key="n"
                type="button"
                class="w-full text-left rounded-xl px-4 py-2 text-sm font-semibold transition"
                :class="customIntervalMensual === n ? 'bg-green-50 text-green-700 border border-green-300' : 'text-gray-600 hover:bg-gray-50'"
                @click="customIntervalMensual = n; mostrarIntervalSelectMensual = false"
              >
                {{ etiquetaIntervalMensualN(n) }}
              </button>
            </div>

            <div class="space-y-3">
              <label class="block text-sm font-black text-gray-700">{{ $t('habits.month_days_label') }}</label>
              <div class="grid grid-cols-7 gap-2">
                <button
                  v-for="d in 31"
                  :key="d"
                  type="button"
                  class="h-10 rounded-xl font-bold text-sm transition-all border-2"
                  :class="isMonthDaySelected(d) ? 'bg-green-500 text-white border-green-400 shadow-sm' : 'bg-gray-50 text-gray-500 border-gray-100 hover:border-green-200'"
                  @click="toggleMonthDay(d)"
                >
                  {{ d }}
                </button>
              </div>
              <p class="text-xs text-gray-400 font-semibold">
                {{ $t('habits.month_days_hint') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'HabitFormPlanning',
  props: {
    modelValue: { type: Object, required: true },
    isDaySelected: { type: Function, required: true }
  },
  emits: ['update:modelValue', 'toggle-day'],
  data: function () {
    return {
      selectorObert: false,
      mostrarInputData: false,
      customSheetObert: false,
      customTab: 'setmanalment',
      customInterval: 1,
      customIntervalDiari: 1,
      customIntervalMensual: 1,
      mostrarIntervalSelect: false,
      mostrarIntervalSelectDiari: false,
      mostrarIntervalSelectMensual: false
    };
  },
  computed: {
    minDate: function () {
      var d = new Date();
      d.setDate(d.getDate() + 1);
      return d.toISOString().slice(0, 10);
    },
    diesLlargs: function () {
      return [
        { key: 'dll', label: this.$t('habits.days_long.dll') },
        { key: 'dm', label: this.$t('habits.days_long.dm') },
        { key: 'dmc', label: this.$t('habits.days_long.dmc') },
        { key: 'dj', label: this.$t('habits.days_long.dj') },
        { key: 'dv', label: this.$t('habits.days_long.dv') },
        { key: 'ds', label: this.$t('habits.days_long.ds') },
        { key: 'dg', label: this.$t('habits.days_long.dg') }
      ];
    },
    customTabs: function () {
      return [
        { valor: 'diariament', etiqueta: this.$t('habits.custom_tab_daily') },
        { valor: 'setmanalment', etiqueta: this.$t('habits.custom_tab_weekly') },
        { valor: 'mensualment', etiqueta: this.$t('habits.custom_tab_monthly') }
      ];
    },
    modeRepeticio: function () {
      var freq = this.modelValue.frequencia;
      var dies = this.modelValue.dies_setmana;
      if (freq === 'diaria') return 'cada_dia';
      if (freq === 'especifics' && dies) {
        var diesLab = dies[0] && dies[1] && dies[2] && dies[3] && dies[4] && !dies[5] && !dies[6];
        if (diesLab) return 'entre_setmana';
        var diesCap = !dies[0] && !dies[1] && !dies[2] && !dies[3] && !dies[4] && dies[5] && dies[6];
        if (diesCap) return 'caps_setmana';
      }
      if (freq === 'especifics' || freq === 'setmanal' || freq === 'mensual') return 'personalitzada';
      return 'cada_dia';
    },
    opcionsRepeticio: function () {
      return [
        { valor: 'cada_dia', etiqueta: this.$t('habits.rep_every_day') },
        { valor: 'entre_setmana', etiqueta: this.$t('habits.rep_weekdays') },
        { valor: 'caps_setmana', etiqueta: this.$t('habits.rep_weekends') },
        { valor: 'personalitzada', etiqueta: this.$t('habits.rep_custom') }
      ];
    },
    etiquetaRepeticio: function () {
      var self = this;
      var opcio = this.opcionsRepeticio.find(function (o) { return o.valor === self.modeRepeticio; });
      return opcio ? opcio.etiqueta : this.$t('habits.rep_every_day');
    },
    etiquetaIntervalSetmanal: function () {
      return this.etiquetaIntervalSetmanalN(this.customInterval);
    },
    etiquetaIntervalDiari: function () {
      return this.etiquetaIntervalDiariN(this.customIntervalDiari);
    },
    etiquetaIntervalMensual: function () {
      return this.etiquetaIntervalMensualN(this.customIntervalMensual);
    }
  },
  methods: {
    obrirSelectorRepeticio: function () {
      this.selectorObert = true;
    },
    tancarSelectorRepeticio: function () {
      this.selectorObert = false;
      this.mostrarInputData = false;
      this.customSheetObert = false;
    },
    tancarCustomSheet: function () {
      this.customSheetObert = false;
      this.mostrarIntervalSelect = false;
      this.mostrarIntervalSelectDiari = false;
      this.mostrarIntervalSelectMensual = false;
    },
    seleccionarCustomTab: function (tabValor) {
      this.customTab = tabValor;
      this.mostrarIntervalSelect = false;
      this.mostrarIntervalSelectDiari = false;
      this.mostrarIntervalSelectMensual = false;

      var updated = { ...this.modelValue };
      if (tabValor === 'diariament') {
        updated.frequencia = 'diaria';
        updated.dies_setmana = [true, true, true, true, true, true, true];
      } else if (tabValor === 'setmanalment') {
        updated.frequencia = 'especifics';
        if (!Array.isArray(updated.dies_setmana) || updated.dies_setmana.length !== 7) {
          updated.dies_setmana = [true, true, true, true, true, true, true];
        }
      } else if (tabValor === 'mensualment') {
        updated.frequencia = 'mensual';
        if (!Array.isArray(updated.dies_mes)) {
          updated.dies_mes = [];
        }
      }
      this.$emit('update:modelValue', updated);
    },
    seleccionarDataFi: function (val) {
      this.$emit('update:modelValue', { ...this.modelValue, dataFinalitzacio: val || '' });
      if (!val) this.mostrarInputData = false;
    },
    seleccionarModeRepeticio: function (mode) {
      var updated = { ...this.modelValue };
      if (mode === 'cada_dia') {
        updated.frequencia = 'diaria';
        updated.dies_setmana = [true, true, true, true, true, true, true];
      } else if (mode === 'entre_setmana') {
        updated.frequencia = 'especifics';
        updated.dies_setmana = [true, true, true, true, true, false, false];
      } else if (mode === 'caps_setmana') {
        updated.frequencia = 'especifics';
        updated.dies_setmana = [false, false, false, false, false, true, true];
      } else if (mode === 'personalitzada') {
        updated.frequencia = 'especifics';
        this.$emit('update:modelValue', updated);
        this.customSheetObert = true;
        return;
      }
      this.$emit('update:modelValue', updated);
    },
    toggleDayLocal: function (index) {
      this.$emit('toggle-day', index);
    },
    isMonthDaySelected: function (d) {
      var list = Array.isArray(this.modelValue.dies_mes) ? this.modelValue.dies_mes : [];
      return list.indexOf(d) >= 0;
    },
    toggleMonthDay: function (d) {
      var current = Array.isArray(this.modelValue.dies_mes) ? this.modelValue.dies_mes.slice() : [];
      var idx = current.indexOf(d);
      if (idx >= 0) current.splice(idx, 1);
      else current.push(d);
      current.sort(function (a, b) { return a - b; });
      this.$emit('update:modelValue', { ...this.modelValue, dies_mes: current });
    },
    etiquetaIntervalSetmanalN: function (n) {
      if (n === 1) return this.$t('habits.interval_one_week');
      return n + ' ' + this.$t('habits.interval_weeks');
    },
    etiquetaIntervalDiariN: function (n) {
      if (n === 1) return this.$t('habits.interval_one_day');
      return n + ' ' + this.$t('habits.interval_days');
    },
    etiquetaIntervalMensualN: function (n) {
      if (n === 1) return this.$t('habits.interval_one_month');
      return n + ' ' + this.$t('habits.interval_months');
    }
  }
};
</script>

<style scoped>
.rep-backdrop-enter-active,
.rep-backdrop-leave-active {
  transition: opacity 0.2s ease;
}
.rep-backdrop-enter-from,
.rep-backdrop-leave-to {
  opacity: 0;
}
.rep-sheet-enter-active,
.rep-sheet-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.rep-sheet-enter-from,
.rep-sheet-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}
</style>
