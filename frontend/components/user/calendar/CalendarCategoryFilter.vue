<!--
  Component o pagina Nuxt: CalendarCategoryFilter.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="calendar-category-filter">
    <label for="category-filter" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
      Filtre per categoria
    </label>
    <div class="relative">
      <select
        id="category-filter"
        :value="modelValue"
        @change="onCanvi"
        class="w-full appearance-none bg-white/95 backdrop-blur-md rounded-2xl px-4 py-3 pr-10 text-sm font-semibold text-gray-700 border border-white/50 shadow-lg focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-green-300 transition-all cursor-pointer"
      >
        <option :value="null">Totes les categories</option>
        <option
          v-for="cat in categoriesUniques"
          :key="cat.id"
          :value="cat.id"
        >
          {{ cat.nom }}
        </option>
      </select>
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <span class="text-gray-400 text-sm">▼</span>
      </div>
    </div>

    <!-- Missatge si no hi ha hàbits per aquesta categoria -->
    <TransitionGroup name="filter-fade">
      <p
        v-if="mostraMissatgeBuit"
        key="empty-msg"
        class="mt-3 text-center text-sm text-gray-400 italic bg-gray-50 rounded-2xl py-4 px-3 border border-gray-100"
      >
        Cap hàbit d'aquesta categoria aquell dia
      </p>
    </TransitionGroup>
  </div>
</template>

<script setup>
/**
 * Filtre de categories per a la vista diària del calendari.
 * Extreu categories úniques del habits_json del snapshot.
 */

var props = defineProps({
  habits: {
    type: Array,
    default: function () { return []; }
  },
  modelValue: {
    type: [Number, null],
    default: null
  }
});

var emit = defineEmits(['update:modelValue']);

/**
 * Extreu les categories úniques dels hàbits del snapshot.
 * Retorna un array d'objectes {id, nom} sense duplicats.
 */
var categoriesUniques = computed(function () {
  var mapa = {};
  var resultat = [];
  var i;

  for (i = 0; i < props.habits.length; i++) {
    var h = props.habits[i];
    if (h.categoria_id && !mapa[h.categoria_id]) {
      mapa[h.categoria_id] = true;
      resultat.push({
        id: h.categoria_id,
        nom: h.categoria_nom || ('Categoria ' + h.categoria_id)
      });
    }
  }

  return resultat;
});

/**
 * Calcula si cal mostrar el missatge de "cap hàbit" visible.
 */
var mostraMissatgeBuit = computed(function () {
  if (props.modelValue === null || props.modelValue === undefined) {
    return false;
  }

  var trobat = false;
  var i;
  for (i = 0; i < props.habits.length; i++) {
    if (props.habits[i].categoria_id === props.modelValue) {
      trobat = true;
      break;
    }
  }

  return !trobat;
});

/**
 * Gestiona el canvi de selecció del dropdown.
 */
function onCanvi(event) {
  var val = event.target.value;
  if (val === '' || val === 'null') {
    emit('update:modelValue', null);
  } else {
    emit('update:modelValue', Number(val));
  }
}
</script>

<style scoped>
/* Animació suau per mostrar/amagar el missatge buit */
.filter-fade-enter-active,
.filter-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.filter-fade-enter-from,
.filter-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
