<!--
  Component o pagina Nuxt: WeatherWidget.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="bento-card bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-lg border border-white/50">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <span class="text-sm">🌍</span>
        <h3 class="text-xs font-black text-gray-500 uppercase tracking-wider">Clima actual</h3>
      </div>
      <button
        v-if="mode === 'geo'"
        type="button"
        title="Canviar a ciutat manual"
        class="text-xs text-gray-400 hover:text-gray-600 transition"
        @click="$emit('switch-manual')"
      >
        ✏️
      </button>
      <button
        v-else-if="mode === 'manual' || mode === 'denied'"
        type="button"
        title="Usar la meva ubicació"
        class="text-xs text-blue-400 hover:text-blue-600 transition"
        @click="$emit('use-geo')"
      >
        📍
      </button>
    </div>

    <div v-if="mode === 'requesting'" class="flex items-center gap-2 text-gray-400 text-sm py-2">
      <span class="animate-pulse text-lg">📍</span>
      <span>Obtenint ubicació...</span>
    </div>

    <div v-else-if="carregant" class="flex items-center gap-2 text-gray-400 text-sm py-1">
      <span class="animate-pulse">⏳</span>
      <span>Carregant clima...</span>
    </div>

    <div v-else-if="!dades || !dades.ok" class="text-xs text-gray-400 py-1">
      Clima no disponible
    </div>

    <div v-else class="space-y-2">
      <div class="flex items-center gap-3">
        <span class="text-4xl leading-none">{{ emojiClima }}</span>
        <div class="flex-1 min-w-0">
          <p class="text-2xl font-black text-gray-800 leading-none">
            {{ dades.temp !== null && dades.temp !== undefined ? Math.round(dades.temp) + '°C' : '—' }}
          </p>
          <p class="text-xs text-gray-500 capitalize mt-0.5 truncate">{{ dades.description || '' }}</p>
          <p class="text-xs font-semibold truncate" :class="mode === 'geo' ? 'text-blue-500' : 'text-gray-400'">
            <span v-if="mode === 'geo'">📍 </span>{{ dades.city || '' }}
          </p>
        </div>
        <div
          v-if="dades.suitable === false"
          class="flex-shrink-0 bg-orange-50 border border-orange-200 text-orange-600 text-xs font-bold px-2 py-1 rounded-xl text-center"
        >
          ⚠️<br/>No idoni
        </div>
        <div
          v-else-if="dades.suitable === true"
          class="flex-shrink-0 bg-green-50 border border-green-200 text-green-600 text-xs font-bold px-2 py-1 rounded-xl text-center"
        >
          ✓<br/>Ideal
        </div>
      </div>
    </div>

    <div v-if="mode === 'denied'" class="mt-2 flex items-center gap-1 text-xs text-amber-600 bg-amber-50 rounded-xl px-2 py-1.5">
      <span>⚠️</span>
      <span>Permís de ubicació denegat.</span>
    </div>

    <div v-if="mode !== 'requesting' && mode !== 'geo'" class="flex items-center gap-1 mt-3">
      <input
        v-model="ciutatInput"
        type="text"
        placeholder="Introdueix una ciutat..."
        class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-xl px-2 py-1.5 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-500/10"
        @keydown.enter="canviarCiutat"
      />
      <button
        type="button"
        @click="canviarCiutat"
        class="text-xs px-3 py-1.5 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition font-bold"
      >
        OK
      </button>
    </div>

    <button
      v-if="mode === 'geo'"
      type="button"
      class="mt-3 w-full text-xs text-gray-400 hover:text-gray-600 transition text-center"
      @click="$emit('switch-manual')"
    >
      Canviar a ciutat manual
    </button>
  </div>
</template>

<script>
var WEATHER_EMOJI = {
  Clear:        "☀️",
  Clouds:       "⛅",
  Rain:         "🌧️",
  Drizzle:      "🌦️",
  Thunderstorm: "⛈️",
  Snow:         "❄️",
  Mist:         "🌫️",
  Fog:          "🌫️",
  Haze:         "🌫️",
  Smoke:        "🌫️",
  Dust:         "💨",
  Sand:         "💨",
  Squall:       "💨",
  Tornado:      "🌪️"
};

export default {
  name: 'WeatherWidget',
  props: {
    dades:     { type: Object,  default: null },
    carregant: { type: Boolean, default: false },
    mode:      { type: String,  default: 'requesting' },
    ciutat:    { type: String,  default: '' }
  },
  emits: ['update:ciutat', 'refresh', 'use-geo', 'switch-manual'],
  data: function () {
    return {
      ciutatInput: this.ciutat
    };
  },
  computed: {
    emojiClima: function () {
      if (!this.dades || !this.dades.weather) {
        return '🌤️';
      }
      return WEATHER_EMOJI[this.dades.weather] || '🌤️';
    }
  },
  watch: {
    ciutat: function (val) {
      this.ciutatInput = val;
    }
  },
  methods: {
    canviarCiutat: function () {
      var nova = (this.ciutatInput || '').trim();
      if (!nova) {
        return;
      }
      this.$emit('update:ciutat', nova);
      this.$emit('refresh');
    }
  }
};
</script>
