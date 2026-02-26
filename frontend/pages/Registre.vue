<template>
  <div class="min-h-screen bg-gray-50 py-12 px-8 lg:px-20 flex items-center">
    <div class="w-full">
      <div
        class="grid grid-cols-2 gap-12 items-stretch px-8 py-8 bg-white rounded-2xl shadow-sm"
      >
        <!-- Esquerra: Targeta de registre -->
        <div class="bg-white rounded-xl p-8 shadow-md" style="max-width: 420px">
          <div class="flex flex-col items-center gap-4">
            <div
              class="w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center"
            >
              <div
                class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center font-semibold"
              >
                ∞
              </div>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800">
              Uneix-te a Loopy
            </h2>
            <p class="text-sm text-gray-500 text-center">
              Crea el teu compte i comença el teu viatge.
            </p>
          </div>

          <form class="mt-6 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >NOM</label
              >
              <input
                v-model="formulari.nom"
                type="text"
                placeholder="El teu nom"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >EMAIL</label
              >
              <input
                v-model="formulari.email"
                type="email"
                placeholder="el.teu.email@exemple.com"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >CONTRASSENYA</label
              >
              <input
                v-model="formulari.contrasenya"
                type="password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-2"
                >CONFIRMAR CONTRASSENYA</label
              >
              <input
                v-model="formulari.confirmacio"
                type="password"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200"
              />
            </div>

            <div class="pt-2">
              <button
                type="button"
                @click="registrarUsuari"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg"
              >
                REGISTRAR-SE
              </button>
            </div>

            <div class="pt-2">
              <NuxtLink to="/login">
                <button
                  type="button"
                  class="w-full bg-white border border-gray-200 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50"
                >
                  TORNAR AL LOGIN
                </button>
              </NuxtLink>
            </div>
          </form>
        </div>

        <!-- Dreta: Test de preguntes -->
        <div class="grid grid-cols-2 gap-4 h-full">
          <!-- Pas 1: Introducció al Quiz Dinàmic -->
          <template v-if="!quizIniciat && !quizFinalitzat">
            <div
              class="col-span-2 bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-8 shadow-sm flex flex-col items-center justify-center text-center"
            >
              <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                <span class="text-3xl">✨</span>
              </div>
              <h3 class="text-lg font-bold text-gray-800 mb-2">Descobreix el teu camí</h3>
              <p class="text-sm text-gray-600 mb-6">
                Respon aquestes 16 preguntes i t'assignarem automàticament la millor categoria d'hàbits per a tu.
              </p>
              <button
                @click="iniciarOnboarding"
                class="bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors shadow-lg"
              >
                COMENÇAR EL TEST
              </button>
            </div>
          </template>

          <!-- Pas 2: Preguntes Seqüencials -->
          <template v-else-if="quizIniciat && !quizFinalitzat">
            <div
              class="col-span-2 bg-white rounded-2xl p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center min-h-[300px]"
            >
              <div class="text-xs font-bold text-green-600 uppercase tracking-wider mb-2">
                Pregunta {{ indexPregunta + 1 }} de {{ llistaPreguntes.length }}
              </div>
              
              <div class="h-1.5 w-full bg-gray-100 rounded-full mb-8 overflow-hidden">
                <div 
                  class="h-full bg-green-500 transition-all duration-500"
                  :style="{ width: ((indexPregunta + 1) / llistaPreguntes.length * 100) + '%' }"
                ></div>
              </div>

              <h4 class="text-xl font-medium text-gray-800 mb-10 min-h-[60px]">
                {{ llistaPreguntes[indexPregunta]?.pregunta }}
              </h4>

              <div class="grid grid-cols-2 gap-4 w-full max-w-sm">
                <button
                  @click="respondrePregunta('si')"
                  class="flex flex-col items-center p-6 border-2 border-gray-100 rounded-2xl hover:border-green-500 hover:bg-green-50 transition-all group"
                >
                  <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">👍</span>
                  <span class="font-bold text-gray-700">SÍ</span>
                </button>
                <button
                  @click="respondrePregunta('no')"
                  class="flex flex-col items-center p-6 border-2 border-gray-100 rounded-2xl hover:border-red-500 hover:bg-red-50 transition-all group"
                >
                  <span class="text-2xl mb-2 group-hover:scale-110 transition-transform">👎</span>
                  <span class="font-bold text-gray-700">NO</span>
                </button>
              </div>
            </div>
          </template>

          <!-- Pas 3: Resultat -->
          <template v-else-if="quizFinalitzat">
            <div
              class="col-span-2 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-8 shadow-sm flex flex-col items-center justify-center text-center"
            >
              <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm">
                <span class="text-3xl">🎯</span>
              </div>
              <h3 class="text-lg font-bold text-gray-800 mb-2">Hem trobat el teu perfil!</h3>
              <p class="text-sm text-indigo-700 mb-2 font-medium uppercase tracking-widest">
                {{ nomCategoriaGuanyadora }}
              </p>
              
              <div v-if="plantillaRecomanada" class="bg-white/60 p-4 rounded-xl mb-6 w-full max-w-xs border border-indigo-100">
                <p class="text-xs text-indigo-500 font-bold uppercase mb-1">Plantilla recomanada</p>
                <p class="text-md font-bold text-gray-800">{{ plantillaRecomanada.titol }}</p>
                <div class="mt-2 flex flex-wrap justify-center gap-1">
                  <span v-for="habit in plantillaRecomanada.habits" :key="habit.id" class="text-[10px] bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">
                    {{ habit.titol }}
                  </span>
                </div>
              </div>

              <p class="text-xs text-gray-500 mb-6 px-4">
                Basant-nos en les teves respostes, aquesta és la millor àrea per començar. Pots canviar-la més endavant si vols.
              </p>
              <button
                @click="quizFinalitzat = false; quizIniciat = false; indexPregunta = 0"
                class="text-indigo-600 font-bold text-xs hover:underline"
              >
                REPETIR TEST
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * Pàgina de Registre amb Onboarding interactiu.
 * Refactoritzada a Vue 3 <script setup>.
 */
import { ref, reactive, computed } from 'vue';

definePageMeta({ layout: false });

const config = useRuntimeConfig();

// --- ESTAT ---
const quizIniciat = ref(false);
const quizFinalitzat = ref(false);
const indexPregunta = ref(0);
const llistaPreguntes = ref([]);
const respostesOnboarding = reactive({}); // { categoria_id: punts }
const formulari = reactive({
  nom: "",
  email: "",
  contrasenya: "",
  confirmacio: ""
});

const nomsCategories = {
  1: 'Activitat física',
  2: 'Alimentació',
  3: 'Estudi',
  4: 'Lectura',
  5: 'Benestar',
  6: 'Vida sense Fum',
  7: 'Neteja Express',
  8: 'Modelisme'
};

const categoriaGuanyadoraId = ref(null);
const plantillaRecomanada = ref(null);

// --- COMPUTED ---
const nomCategoriaGuanyadora = computed(() => {
  return categoriaGuanyadoraId.value ? nomsCategories[categoriaGuanyadoraId.value] : '';
});

// --- MÈTODES ---

/**
 * Inicia el flux d'onboarding carregant les preguntes representatives.
 */
const iniciarOnboarding = async () => {
  try {
    let base = config.public.apiUrl;
    if (base.endsWith("/")) {
      base = base.slice(0, -1);
    }
    
    const resposta = await fetch(`${base}/api/onboarding/questions`);
    const dades = await resposta.json();

    if (dades && dades.preguntes) {
      llistaPreguntes.value = dades.preguntes;
      quizIniciat.value = true;
      indexPregunta.value = 0;
      // Reset respostes
      for (let i = 1; i <= 8; i++) respostesOnboarding[i] = 0;
    }
  } catch (error) {
    console.error("Error al carregar les preguntes d'onboarding:", error);
    alert("Error al carregar el test. Revisa la connexió.");
  }
};

/**
 * Gestiona la resposta a la pregunta actual i avança.
 */
const respondrePregunta = (valor) => {
  const preguntaActual = llistaPreguntes.value[indexPregunta.value];
  if (!preguntaActual) return;

  // Si respon SÍ, sumem punt a la categoria
  if (valor === 'si') {
    respostesOnboarding[preguntaActual.categoria_id]++;
  }

  // Avançar
  if (indexPregunta.value < llistaPreguntes.value.length - 1) {
    indexPregunta.value++;
  } else {
    finalitzarTest();
  }
};

/**
 * Calcula la categoria guanyadora i finalitza el test.
 */
const finalitzarTest = async () => {
  let maxPunts = -1;
  let guanyador = 1;

  // Trobem la categoria amb més punts
  Object.keys(respostesOnboarding).forEach(catId => {
    if (respostesOnboarding[catId] > maxPunts) {
      maxPunts = respostesOnboarding[catId];
      guanyador = parseInt(catId);
    }
  });

  categoriaGuanyadoraId.value = guanyador;
  
  // Buscar plantilla recomanada
  try {
    let base = config.public.apiUrl;
    if (base.endsWith("/")) {
      base = base.slice(0, -1);
    }
    const resp = await fetch(`${base}/api/plantilles/recommend/${guanyador}`);
    const data = await resp.json();
    if (data.success) {
      plantillaRecomanada.value = data.plantilla;
    }
  } catch (err) {
    console.error("Error fetching recommended plantilla:", err);
  }

  quizFinalitzat.value = true;
  console.log("Categoria assignada automàticament:", guanyador);
};

/**
 * Acció per registrar un usuari enviant la categoria calculada.
 */
const registrarUsuari = async () => {
  console.log("Intentant registre...");
  
  if (!formulari.nom || !formulari.email || !formulari.contrasenya) {
    alert("Si us plau, omple tots els camps.");
    return;
  }
  
  if (formulari.contrasenya !== formulari.confirmacio) {
    alert("Les contrasenyes no coincideixen.");
    return;
  }

  if (!categoriaGuanyadoraId.value) {
    alert("Si us plau, completa el test per rebre una recomanació.");
    return;
  }

  const payload = {
    name: formulari.nom,
    email: formulari.email,
    password: formulari.contrasenya,
    categoria_id: categoriaGuanyadoraId.value
  };

  try {
    let base = config.public.apiUrl;
    if (base.endsWith("/")) {
      base = base.slice(0, -1);
    }

    const resposta = await fetch(`${base}/api/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    });

    const dades = await resposta.json();

    if (dades.success) {
      alert("Registre completat amb èxit! Ja pots iniciar sessió.");
      navigateTo('/login');
    } else {
      alert("Error en el registre: " + (dades.message || "Revisa les dades"));
    }
  } catch (error) {
    console.error("Error al registrar:", error);
    alert("Error de connexió al servidor.");
  }
};
</script>

<style scoped>
/* Estils locals del registre */
</style>
