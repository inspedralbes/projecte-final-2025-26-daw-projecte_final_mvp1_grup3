<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="grid grid-cols-1 gap-6 w-full max-w-lg">
      <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col items-center text-center transition-all">
        <div
          class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6"
          :class="classesIcona"
        >
          <span class="text-3xl font-black text-white">{{ codi }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2 uppercase tracking-wide">
          {{ titol }}
        </h1>
        <p class="text-gray-500 mb-8">{{ missatge }}</p>
        <div class="flex flex-col sm:flex-row gap-4">
          <NuxtLink
            v-if="mostrarTornar"
            :to="rutaTornar"
            class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition-all duration-300 uppercase tracking-widest text-xs"
          >
            Tornar
          </NuxtLink>
          <NuxtLink
            to="/login"
            class="px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all duration-300 uppercase tracking-widest text-xs"
          >
            Anar al Login
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * Component reutilitzable per a pàgines d'error.
 * Rep codi, títol i missatge. Estil Bento Grid segons AgentTailwind.
 */
var props = defineProps({
  codi: {
    type: [Number, String],
    default: 404
  },
  titol: {
    type: String,
    required: true
  },
  missatge: {
    type: String,
    required: true
  },
  mostrarTornar: {
    type: Boolean,
    default: true
  },
  rutaTornar: {
    type: String,
    default: '/'
  }
});

/**
 * Retorna la classe CSS de la icona segons el codi d'error.
 * 403: amber, 404: gris, altres: vermell.
 */
function obtenirClasseIcona() {
  var c = Number(props.codi);
  if (c === 403) {
    return 'bg-amber-500';
  }
  if (c === 404) {
    return 'bg-gray-600';
  }
  return 'bg-red-500';
}

var classesIcona = computed(function () {
  return obtenirClasseIcona();
});
</script>
