<!--
  Component o pagina Nuxt: onboarding.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div
    class="global-app-container onboarding-page onboarding-container"
    :class="{ 'onboarding-container--intro': !introFinished && !isLoading && !showHabitsSelection }"
  >
    <div
      v-if="!introFinished && !isLoading && !showHabitsSelection"
      class="onboarding-intro"
      tabindex="0"
      role="region"
      :aria-label="$t('onboarding.intro.region_label')"
      @click="advanceIntro"
      @keydown.enter.prevent="advanceIntro"
      @keydown.space.prevent="advanceIntro"
    >
      <div class="onboarding-intro-bubble" aria-live="polite">
        <p class="onboarding-intro-bubble-text">
          {{ introBubbleDisplayedText }}<span
            v-if="!introTypingComplete"
            class="onboarding-intro-type-caret"
            aria-hidden="true"
          />
        </p>
      </div>
      <div class="onboarding-intro-loopy" aria-hidden="true">
        <img
          :src="loopySaludantUrl"
          alt=""
          class="onboarding-intro-loopy-img"
          :class="{ 'onboarding-intro-loopy-img--entrance': introLoopyFromRegister }"
          width="524"
          height="628"
          decoding="async"
        />
      </div>
    </div>

    <div
      v-if="introFinished && !isLoading && (currentStep <= 4 || showHabitsSelection || showMonsterSelection)"
      class="onboarding-quiz"
    >
      <div
        class="onboarding-quiz-body"
        :class="{ 'onboarding-quiz-body--intro-slide': quizIntroBodySlide }"
      >
        <div class="onboarding-progress-wrap">
          <div
            class="onboarding-progress-bars"
            role="group"
            :aria-label="$t('onboarding.progress', { current: displayProgressStep, total: TOTAL_ONBOARDING_STEPS })"
          >
            <div
              v-for="seg in TOTAL_ONBOARDING_STEPS"
              :key="seg"
              class="onboarding-progress-cell"
            >
              <svg
                class="onboarding-progress-svg"
                viewBox="0 0 100 5"
                preserveAspectRatio="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
              >
                <line
                  x1="2.5"
                  y1="2.5"
                  x2="97.5"
                  y2="2.5"
                  stroke="#79D45D"
                  stroke-width="5"
                  stroke-linecap="round"
                  :stroke-opacity="seg <= displayProgressStep ? 1 : 0.4"
                />
              </svg>
            </div>
          </div>
        </div>

        <div class="onboarding-index-root">
          <Transition :name="quizTransitionName" mode="out-in">
            <p :key="quizSlideKey" class="onboarding-question-index">{{ $t('onboarding.question_number', { n: displayProgressStep }) }}</p>
          </Transition>
        </div>

        <div class="onboarding-mascot">
          <img
            :src="loopyMascotUrl"
            alt=""
            width="265"
            height="318"
            class="onboarding-mascot-img"
            decoding="async"
          />
        </div>

        <div class="onboarding-slide-root">
          <Transition :name="quizTransitionName" mode="out-in">
            <div :key="quizSlideKey" class="onboarding-slide-pane">
              <div class="onboarding-copy">
                <template v-if="!showHabitsSelection && !showMonsterSelection">
                  <h2 class="onboarding-title">{{ currentQuestionTitle }}</h2>
                  <p class="onboarding-subtitle">{{ currentQuestionSubtitle }}</p>
                </template>
                <template v-else-if="showHabitsSelection && !showMonsterSelection">
                  <h2 class="onboarding-title">{{ $t('onboarding.habits.title') }}</h2>
                  <p class="onboarding-subtitle">{{ $t('onboarding.habits.subtitle') }}</p>
                </template>
                <template v-else-if="showMonsterSelection">
                  <h2 class="onboarding-title">Tria el teu monstre!</h2>
                  <p class="onboarding-subtitle">Selecciona l'ou del monstre que t'acompanyarà.</p>
                </template>
              </div>

              <div v-if="!showHabitsSelection && !showMonsterSelection" class="onboarding-options">
                <button
                  v-for="option in currentOptions"
                  :key="option.value"
                  type="button"
                  class="onboarding-option-btn"
                  :class="{ 'onboarding-option-btn--selected': currentAnswerValue === option.value }"
                  @click="selectAnswer(currentAnswerKey, option.value)"
                >
                  {{ option.label }}
                </button>
              </div>
              <div v-else-if="showHabitsSelection && !showMonsterSelection" class="onboarding-options onboarding-options--habits">
                <div
                  v-for="(habit, index) in generatedHabits"
                  :key="index"
                  class="onboarding-habit-card"
                  :class="{ 'onboarding-habit-card--selected': selectedHabits.includes(index) }"
                  role="button"
                  tabindex="0"
                  @click="toggleHabit(index)"
                  @keydown.enter.prevent="toggleHabit(index)"
                  @keydown.space.prevent="toggleHabit(index)"
                >
                  <div class="onboarding-habit-card-header">
                    <span class="onboarding-habit-title">{{ habit.titol }}</span>
                    <span
                      class="onboarding-habit-check"
                      :class="{ 'onboarding-habit-check--checked': selectedHabits.includes(index) }"
                    >✓</span>
                  </div>
                  <p class="onboarding-habit-rutina">{{ habit.rutina }}</p>
                  <div class="onboarding-habit-meta">
                    <span class="onboarding-habit-category">{{ habit.categoria }}</span>
                    <span class="onboarding-habit-reward">{{ habit.recompensa }}</span>
                  </div>
                </div>
              </div>
              <div v-else-if="showMonsterSelection" class="onboarding-options onboarding-options--monsters">
                <div
                  v-for="egg in monsterEggs"
                  :key="egg.type"
                  class="onboarding-monster-card"
                  :class="{ 'onboarding-monster-card--selected': selectedMonsterType === egg.type }"
                  role="button"
                  tabindex="0"
                  @click="selectMonster(egg.type)"
                  @keydown.enter.prevent="selectMonster(egg.type)"
                  @keydown.space.prevent="selectMonster(egg.type)"
                >
                  <div class="onboarding-monster-card-content">
                    <img :src="egg.image" alt="Huevo" class="onboarding-monster-egg" decoding="async" />
                  </div>
                </div>
              </div>
            </div>
          </Transition>
        </div>

        <div class="onboarding-arrows">
          <button
            type="button"
            class="onboarding-arrow onboarding-arrow--back"
            :disabled="!showMonsterSelection && !showHabitsSelection && currentStep <= 1"
            @click="previousStep"
          >
            <svg width="23" height="37" viewBox="0 0 23 37" fill="none"><path d="M18.25 36.5L0 18.25L18.25 0L22.5083 4.25833L8.51667 18.25L22.5083 32.2417L18.25 36.5Z" fill="currentColor" /></svg>
          </button>
          <button
            type="button"
            class="onboarding-arrow onboarding-arrow--forward"
            :disabled="showHabitsSelection || showMonsterSelection || !canProceed"
            @click="nextStep"
          >
            <svg width="23" height="37" viewBox="0 0 23 37" fill="none"><path d="M18.25 36.5L0 18.25L18.25 0L22.5083 4.25833L8.51667 18.25L22.5083 32.2417L18.25 36.5Z" fill="currentColor" /></svg>
          </button>
        </div>

        <button v-if="showHabitsSelection && !showMonsterSelection" type="button" class="onboarding-primary-btn" @click="confirmHabits">
          {{ $t('onboarding.enter_app') }}
        </button>

        <button v-if="showMonsterSelection" type="button" class="onboarding-primary-btn" :disabled="!selectedMonsterType || isConfirmingMonster" @click="confirmMonster">
          <span v-if="isConfirmingMonster" class="loading-spinner-small"></span>
          <span v-else>Triar monstre</span>
        </button>
      </div>
    </div>

    <div v-else-if="isLoading" class="onboarding-panel onboarding-panel--state">
      <div class="loading-section">
        <div class="loading-spinner"></div>
        <p class="loading-text">{{ $t('onboarding.generating_habits') }}</p>
      </div>
    </div>

    <div v-if="errorMessage" class="error-toast">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import loopyMascotUrl from '~/assets/img/Onboarding/Img/2-Loopy-Content.png';
import loopySaludantUrl from '~/assets/img/Onboarding/Img/1-Loopy-Saludant.png';
import huevoVerdeUrl from '~/assets/img/Monstres/huevos/Huevo_1.png';
import huevoRosaUrl from '~/assets/img/Monstres/huevos/Huevo_2.png';
import huevoLilaUrl from '~/assets/img/Monstres/huevos/Huevo_3.png';
import huevoAmarilloUrl from '~/assets/img/Monstres/huevos/Huevo_4.png';
import { authFetch } from '~/composables/useApi.js';
import { useHabitStore } from '~/stores/useHabitStore.js';
import { useAuthStore } from '~/stores/useAuthStore.js';

definePageMeta({ layout: false });

useHead({
  link: [
    {
      rel: 'stylesheet',
      href: 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600&family=Comfortaa:wght@400;700&display=swap',
    },
  ],
});

const { t, setLocale, locale } = useI18n();
const onboardingDoneCookie = useCookie('loopy_onboarding_done', { sameSite: 'lax', maxAge: 60 * 60 * 24 * 365 });
const habitStore = useHabitStore();
/** Passos de quiz (4) + tria d'hàbits (1) + tria de monstre (1) per a la barra i l'índex "Pregunta n". */
const TOTAL_ONBOARDING_STEPS = 6;
/** Si és cert, al triar resposta a cada pas es passa automàticament al següent (hàbits sempre manual). */
const AUTO_ADVANCE_STEPS = true;

const config = useRuntimeConfig();

/** Diàleg pre-quiz: 0–2 = tres textos a la bombolla; després del tercer clic es passa a `introFinished`. */
const introBubbleStep = ref(0);
const introFinished = ref(false);
const introLoopyFromRegister = ref(false);
/** Després de l'animació d'entrada del Loopy (post-registre); sense registre és true des de l'inici. */
const introLoopyEntranceDone = ref(true);
/** Primer clic vàlid després d'`introLoopyEntranceDone`: arrenca la màquina d'escriure de la 1a viñeta. */
const introPreambleComplete = ref(false);
const introBubbleDisplayedText = ref('');
/** Un sol cop: animació lateral de tot el bloc del quiz en passar de l'intro. */
const quizIntroBodySlide = ref(false);
var introTypewriterTimer = null;
var introEntranceEndTimer = null;

function introBubbleFullText() {
  return t('onboarding.intro.bubble' + (introBubbleStep.value + 1));
}

function clearIntroTypewriter() {
  if (introTypewriterTimer != null) {
    clearInterval(introTypewriterTimer);
    introTypewriterTimer = null;
  }
}

function runIntroTypewriter(fullText) {
  clearIntroTypewriter();
  introBubbleDisplayedText.value = '';
  if (!fullText) {
    return;
  }
  var i = 0;
  var msPerChar = 19;
  introTypewriterTimer = setInterval(function () {
    i += 1;
    introBubbleDisplayedText.value = fullText.slice(0, i);
    if (i >= fullText.length) {
      clearIntroTypewriter();
    }
  }, msPerChar);
}

watch(introBubbleStep, function () {
  if (introFinished.value) {
    return;
  }
  if (introBubbleStep.value === 0 && !introPreambleComplete.value) {
    return;
  }
  runIntroTypewriter(introBubbleFullText());
}, { immediate: true });

watch(locale, function () {
  if (introFinished.value) {
    return;
  }
  if (introBubbleStep.value === 0 && !introPreambleComplete.value) {
    return;
  }
  runIntroTypewriter(introBubbleFullText());
});

watch(introFinished, function (done) {
  if (!done) {
    return;
  }
  quizIntroBodySlide.value = false;
  nextTick(function () {
    quizIntroBodySlide.value = true;
  });
});

function advanceIntro() {
  if (!introPreambleComplete.value) {
    if (!introLoopyEntranceDone.value) {
      return;
    }
    introPreambleComplete.value = true;
    runIntroTypewriter(introBubbleFullText());
    return;
  }

  var full = introBubbleFullText();
  if (introBubbleDisplayedText.value.length < full.length) {
    clearIntroTypewriter();
    introBubbleDisplayedText.value = full;
    return;
  }
  if (introBubbleStep.value < 2) {
    introBubbleStep.value++;
  } else {
    introFinished.value = true;
  }
}

onBeforeUnmount(function () {
  clearIntroTypewriter();
  if (introEntranceEndTimer != null) {
    clearTimeout(introEntranceEndTimer);
    introEntranceEndTimer = null;
  }
});

const introTypingComplete = computed(function () {
  if (!introPreambleComplete.value) {
    return true;
  }
  var full = introBubbleFullText();
  return full.length > 0 && introBubbleDisplayedText.value.length >= full.length;
});

onMounted(function () {
  setLocale('ca');
  if (typeof window !== 'undefined' && sessionStorage.getItem('loopy_register_onboarding_entrance') === '1') {
    sessionStorage.removeItem('loopy_register_onboarding_entrance');
    introLoopyFromRegister.value = true;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) {
      introLoopyEntranceDone.value = true;
    } else {
      introLoopyEntranceDone.value = false;
      introEntranceEndTimer = setTimeout(function () {
        introEntranceEndTimer = null;
        introLoopyEntranceDone.value = true;
      }, 1180);
    }
  } else {
    introPreambleComplete.value = true;
    runIntroTypewriter(introBubbleFullText());
  }
});

const currentStep = ref(1);
const isLoading = ref(false);
const showHabitsSelection = ref(false);
const errorMessage = ref('');

watch(isLoading, function (loading) {
  if (loading) {
    quizIntroBodySlide.value = false;
  }
});

const answers = ref({
  objectiu: null,
  energia: null,
  obstacle: null,
  temps: null,
});

const goalOptions = computed(function () {
  return [
    { value: 'salut', label: t('onboarding.options.goal.salut') },
    { value: 'productivitat', label: t('onboarding.options.goal.productivitat') },
    { value: 'ment', label: t('onboarding.options.goal.ment') },
    { value: 'aprenentatge', label: t('onboarding.options.goal.aprenentatge') },
  ];
});

const energyOptions = computed(function () {
  return [
    { value: 'mati', label: t('onboarding.options.energy.mati') },
    { value: 'migdia', label: t('onboarding.options.energy.migdia') },
    { value: 'tarda', label: t('onboarding.options.energy.tarda') },
    { value: 'nit', label: t('onboarding.options.energy.nit') },
  ];
});

const obstacleOptions = computed(function () {
  return [
    { value: 'estress', label: t('onboarding.options.obstacle.estress') },
    { value: 'temps', label: t('onboarding.options.obstacle.temps') },
    { value: 'memoria', label: t('onboarding.options.obstacle.memoria') },
    { value: 'andra', label: t('onboarding.options.obstacle.andra') },
  ];
});

const timeOptions = computed(function () {
  return [
    { value: '15min', label: t('onboarding.options.time.15min') },
    { value: '30min', label: t('onboarding.options.time.30min') },
    { value: '1h', label: t('onboarding.options.time.1h') },
    { value: '1h+', label: t('onboarding.options.time.1h_plus') },
  ];
});

const generatedHabits = ref([]);
const selectedHabits = ref([]);
const showMonsterSelection = ref(false);
const selectedMonsterType = ref(null);
const isConfirmingMonster = ref(false);

const monsterEggs = [
  { type: 'VV', image: huevoVerdeUrl },
  { type: 'VR', image: huevoRosaUrl },
  { type: 'VL', image: huevoLilaUrl },
  { type: 'VA', image: huevoAmarilloUrl },
];

const currentAnswerKey = computed(function () {
  var map = { 1: 'objectiu', 2: 'energia', 3: 'obstacle', 4: 'temps' };
  return map[currentStep.value] || 'objectiu';
});

const currentAnswerValue = computed(function () {
  return answers.value[currentAnswerKey.value];
});

const currentOptions = computed(function () {
  switch (currentStep.value) {
    case 1: return goalOptions.value;
    case 2: return energyOptions.value;
    case 3: return obstacleOptions.value;
    case 4: return timeOptions.value;
    default: return [];
  }
});

const currentQuestionTitle = computed(function () {
  return t('onboarding.question' + currentStep.value + '.title');
});

const currentQuestionSubtitle = computed(function () {
  return t('onboarding.question' + currentStep.value + '.subtitle');
});

const displayProgressStep = computed(function () {
  if (showMonsterSelection.value) {
    return 6;
  }
  if (showHabitsSelection.value) {
    return 5;
  }
  return currentStep.value;
});

/** 1 = endavant (surten cap a l'esquerra, entren per la dreta); -1 = enrere (invers). */
const quizSlideDirection = ref(1);

const quizSlideKey = computed(function () {
  if (showMonsterSelection.value) {
    return 'monster';
  }
  if (showHabitsSelection.value) {
    return 'habits';
  }
  return 'step-' + String(currentStep.value);
});

const quizTransitionName = computed(function () {
  return quizSlideDirection.value === 1 ? 'onboarding-q-next' : 'onboarding-q-prev';
});

const canProceed = computed(() => {
  if (currentStep.value === 1) return answers.value.objectiu !== null;
  if (currentStep.value === 2) return answers.value.energia !== null;
  if (currentStep.value === 3) return answers.value.obstacle !== null;
  if (currentStep.value === 4) return answers.value.temps !== null;
  return false;
});

function selectAnswer(key, value) {
  answers.value[key] = value;
  if (AUTO_ADVANCE_STEPS && canProceed.value && !isLoading.value && !showHabitsSelection.value) {
    setTimeout(function () {
      nextStep();
    }, 0);
  }
}

function previousStep() {
  quizSlideDirection.value = -1;
  if (showMonsterSelection.value) {
    showMonsterSelection.value = false;
    showHabitsSelection.value = true;
    return;
  }
  if (showHabitsSelection.value) {
    showHabitsSelection.value = false;
    currentStep.value = 4;
    return;
  }
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

async function nextStep() {
  quizSlideDirection.value = 1;
  if (currentStep.value < 4) {
    currentStep.value++;
  } else {
    await generateHabits();
  }
}

function selectMonster(type) {
  selectedMonsterType.value = type;
}

function toggleHabit(index) {
  var idx = selectedHabits.value.indexOf(index);
  if (idx > -1) {
    selectedHabits.value.splice(idx, 1);
  } else {
    selectedHabits.value.push(index);
  }
}

async function confirmHabits() {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    var habitsToSave = selectedHabits.value.map(function (index) { return generatedHabits.value[index]; });
    if (habitsToSave.length === 0) {
      habitStore.establirHabitsDesDeApi([]);
      quizSlideDirection.value = 1;
      showMonsterSelection.value = true;
      isLoading.value = false;
      return;
    }
    var authStore = useAuthStore();
    var response = await authFetch('/api/habits/assign', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: authStore.user ? authStore.user.id : null,
        habits: habitsToSave.map(function (h) { return {
          titol: h.titol,
          categoria_id: mapCategoria(h.categoria),
          dificultat: mapDificultat(answers.value.obstacle),
          objectiu_vegades: mapTemps(answers.value.temps),
          icona: getIconaPerCategoria(h.categoria),
          color: getColorPerCategoria(h.categoria),
        }; }),
      }),
    });
    if (response.ok) {
      var data = await response.json();
      if (data && Array.isArray(data.habits)) {
        habitStore.establirHabitsDesDeApi(data.habits);
      } else {
        habitStore.establirHabitsDesDeApi([]);
      }
    } else {
      habitStore.establirHabitsDesDeApi([]);
    }
    quizSlideDirection.value = 1;
    showMonsterSelection.value = true;
  } catch (error) {
    console.error('Error saving habits:', error);
    habitStore.establirHabitsDesDeApi([]);
    quizSlideDirection.value = 1;
    showMonsterSelection.value = true;
  } finally {
    isLoading.value = false;
  }
}

async function confirmMonster() {
  if (!selectedMonsterType.value || isConfirmingMonster.value) {
    return;
  }
  isConfirmingMonster.value = true;
  errorMessage.value = '';
  try {
    var authStore = useAuthStore();
    var userId = authStore.user && authStore.user.id ? authStore.user.id : null;
    var bodyData = { monstre_tipus: selectedMonsterType.value };
    if (userId) {
      bodyData.user_id = userId;
    }
    var response = await authFetch('/api/user/monster-choice', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(bodyData),
    });
    var data = await response.json();
    if (response.ok && data.success) {
      if (typeof window !== 'undefined') {
        localStorage.setItem('loopy_monstre_tipus', selectedMonsterType.value);
      }
      await authStore.refrescarSessio();
      var nuxt = useNuxtApp();
      if (nuxt.$updateSocketAuth) {
        nuxt.$updateSocketAuth();
      }
      marcarOnboardingCompletat();
      navigateTo('/eggReveal?type=' + selectedMonsterType.value);
    } else {
      errorMessage.value = data.error || 'Error al triar el monstre';
    }
  } catch (error) {
    console.error('Error confirming monster choice:', error);
    if (typeof window !== 'undefined') {
      localStorage.setItem('loopy_monstre_tipus', selectedMonsterType.value);
    }
    marcarOnboardingCompletat();
    navigateTo('/eggReveal?type=' + selectedMonsterType.value);
  } finally {
    isConfirmingMonster.value = false;
  }
}

var CATEGORIA_MAP = {
  'salut': { id: 1, icona: '🏃', color: '#10B981' },
  'physical': { id: 1, icona: '🏃', color: '#10B981' },
  'food': { id: 2, icona: '🥗', color: '#3B82F6' },
  'alimentacio': { id: 2, icona: '🥗', color: '#3B82F6' },
  'aprenentatge': { id: 3, icona: '📚', color: '#F59E0B' },
  'study': { id: 3, icona: '📚', color: '#F59E0B' },
  'lectura': { id: 4, icona: '📖', color: '#EF4444' },
  'reading': { id: 4, icona: '📖', color: '#EF4444' },
  'ment': { id: 5, icona: '🧘', color: '#8B5CF6' },
  'wellness': { id: 5, icona: '🧘', color: '#8B5CF6' },
  'productivitat': { id: 6, icona: '✨', color: '#EC4899' },
  'improvement': { id: 6, icona: '✨', color: '#EC4899' },
  'llar': { id: 7, icona: '🏠', color: '#06B6D4' },
  'home': { id: 7, icona: '🏠', color: '#06B6D4' },
  'hobby': { id: 8, icona: '🎨', color: '#1F2937' },
};

function mapCategoria(categoria) {
  var key = (categoria || '').toLowerCase();
  var entry = CATEGORIA_MAP[key];
  return entry ? entry.id : 1;
}

function getIconaPerCategoria(categoria) {
  var key = (categoria || '').toLowerCase();
  var entry = CATEGORIA_MAP[key];
  return entry ? entry.icona : '📝';
}

function getColorPerCategoria(categoria) {
  var key = (categoria || '').toLowerCase();
  var entry = CATEGORIA_MAP[key];
  return entry ? entry.color : '#10B981';
}

function mapDificultat(obstacle) {
  var map = {
    'estress': 'facil',
    'temps': 'media',
    'memoria': 'media',
    'andra': 'media',
  };
  return map[obstacle] || 'media';
}

function mapTemps(temps) {
  var map = {
    '15min': 1,
    '30min': 1,
    '1h': 1,
    '1h+': 2,
  };
  return map[temps] || 1;
}

function marcarOnboardingCompletat() {
  var authStore = useAuthStore();
  onboardingDoneCookie.value = '1';
  authStore.desmarcarOnboardingPendent();
  if (typeof window !== 'undefined') {
    localStorage.setItem('loopy_onboarding_done', '1');
    if (authStore.user && authStore.user.id != null) {
      localStorage.setItem('loopy_onboarding_user_id', String(authStore.user.id));
    }
  }
}

function generarHabitsRapids() {
  var categoria = answers.value.objectiu || 'salut';
  return [
    {
      titol: 'Micro habit del matí',
      rutina: 'Fes una acció petita en menys de 2 minuts.',
      categoria: categoria,
      recompensa: '+10 XP',
    },
    {
      titol: 'Pausa conscient',
      rutina: 'Respira profundament durant 1 minut.',
      categoria: categoria,
      recompensa: '+10 XP',
    },
    {
      titol: 'Tancament del dia',
      rutina: 'Marca una petita victòria abans de dormir.',
      categoria: categoria,
      recompensa: '+10 XP',
    },
  ];
}

async function generateHabits() {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(function () {
      controller.abort();
    }, 4500);

    const response = await fetch(`${config.public.socketUrl}/api/onboarding/generate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      signal: controller.signal,
      body: JSON.stringify({
        categoria: answers.value.objectiu,
        senyal: answers.value.energia,
        dificultat: answers.value.obstacle,
        temps: answers.value.temps,
      }),
    });
    clearTimeout(timeoutId);

    const data = await response.json();

    if (data.success && data.habits) {
      generatedHabits.value = data.habits;
      selectedHabits.value = [];
      showHabitsSelection.value = true;
    } else {
      errorMessage.value = data.message || t('onboarding.errors.generate');
    }
  } catch (error) {
    generatedHabits.value = generarHabitsRapids();
    selectedHabits.value = [];
    showHabitsSelection.value = true;
  } finally {
    isLoading.value = false;
  }
}
</script>

<style scoped>
.onboarding-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 1.5rem 1.25rem 2rem;
  position: relative;
  z-index: 1;
  background-color: #FAF9F9;
}

.onboarding-container.onboarding-container--intro {
  padding: 0;
}

.onboarding-intro {
  position: relative;
  width: 100%;
  max-width: 430px;
  margin: 0 auto;
  min-height: 100vh;
  min-height: 100dvh;
  overflow-x: clip;
  background-color: #faf9f9;
  isolation: isolate;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  outline: none;
}

.onboarding-intro:focus-visible {
  box-shadow: inset 0 0 0 2px #79d45d;
}

.onboarding-intro-bubble {
  position: absolute;
  z-index: 3;
  top: 6%;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 2rem);
  max-width: 320px;
  margin: 0;
  padding: 1rem 1.1rem 0.75rem;
  border: 3px solid #1f2937;
  border-radius: 1.25rem 1.5rem 1.35rem 1.1rem / 1.2rem 1.3rem 1.4rem 1.15rem;
  background: #fff;
  box-shadow: 4px 4px 0 #1f2937;
  pointer-events: none;
  text-align: left;
  font-family: 'Comfortaa', sans-serif;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}

.onboarding-intro-bubble::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: -14px;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 12px solid transparent;
  border-right: 12px solid transparent;
  border-top: 14px solid #1f2937;
}

.onboarding-intro-bubble::before {
  content: '';
  position: absolute;
  left: 50%;
  bottom: -10px;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 9px solid transparent;
  border-right: 9px solid transparent;
  border-top: 11px solid #fff;
  z-index: 1;
}

.onboarding-intro-bubble-text {
  margin: 0;
  font-weight: 700;
  font-size: 30px;
  line-height: 1.35;
  color: #1f2937;
}

.onboarding-intro-type-caret {
  display: inline-block;
  width: 0.07em;
  min-width: 2px;
  height: 0.9em;
  margin-left: 3px;
  background: #1f2937;
  vertical-align: -0.06em;
  border-radius: 1px;
  animation: onboarding-intro-caret 0.92s ease-in-out infinite;
}

@keyframes onboarding-intro-caret {
  0%,
  40% {
    opacity: 1;
  }
  50%,
  100% {
    opacity: 0.15;
  }
}

.onboarding-intro-loopy {
  position: absolute;
  width: 524px;
  height: 628px;
  left: -149px;
  top: 468px;
  pointer-events: none;
  z-index: 1;
}

.onboarding-intro-loopy-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  user-select: none;
}

.onboarding-intro-loopy-img--entrance {
  opacity: 0;
  animation: onboarding-intro-loopy-in 1.12s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  will-change: transform, opacity;
}

@keyframes onboarding-intro-loopy-in {
  0% {
    opacity: 0;
    transform: translate3d(88px, 10px, 0) scale(0.96);
  }
  100% {
    opacity: 1;
    transform: translate3d(0, 0, 0) scale(1);
  }
}

@media (prefers-reduced-motion: reduce) {
  .onboarding-intro-loopy-img--entrance {
    animation: none;
    opacity: 1;
    transform: none;
  }

  .onboarding-quiz-body--intro-slide {
    animation: none;
    opacity: 1;
    transform: none;
  }

  .onboarding-intro-type-caret {
    animation: none;
    opacity: 1;
  }

  .onboarding-q-next-enter-active,
  .onboarding-q-next-leave-active,
  .onboarding-q-prev-enter-active,
  .onboarding-q-prev-leave-active {
    transition: none;
  }
}

@media (max-width: 480px) {
  .onboarding-intro-loopy {
    width: min(524px, 135vw);
    height: auto;
    aspect-ratio: 524 / 628;
    left: max(-149px, -22vw);
    top: clamp(320px, 52vh, 468px);
  }
}

@media (max-height: 700px) {
  .onboarding-intro-loopy {
    transform: scale(0.72);
    transform-origin: left bottom;
  }
}

.onboarding-quiz {
  width: 100%;
  max-width: 22.5rem;
}

.onboarding-quiz-body {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.onboarding-quiz-body--intro-slide {
  animation: onboarding-quiz-body-intro-slide 0.52s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes onboarding-quiz-body-intro-slide {
  from {
    opacity: 0;
    transform: translate3d(100%, 0, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}

.onboarding-progress-wrap {
  width: 100%;
  max-width: 20.5rem;
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 0.75rem;
  text-align: center;
}

.onboarding-progress-bars {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  width: 100%;
  margin-bottom: 0.625rem;
}

.onboarding-progress-cell {
  flex: 1;
  min-width: 0;
  height: 5px;
}

.onboarding-progress-svg {
  display: block;
  width: 100%;
  height: 5px;
}

.onboarding-index-root {
  position: relative;
  width: 100%;
  overflow: hidden;
  min-height: 1.35rem;
}

.onboarding-question-index {
  margin: 0.35rem 0 0.25rem;
  text-align: center;
  font-family: 'Comfortaa', sans-serif;
  font-weight: 700;
  font-size: 15px;
  line-height: 1.2;
  color: #79d45d;
}

.onboarding-mascot {
  position: relative;
  width: 265px;
  max-width: 100%;
  height: 318px;
  margin: 0.5rem auto 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  align-self: center;
}

.onboarding-mascot-img {
  width: 265px;
  max-width: 100%;
  height: auto;
  max-height: 318px;
  object-fit: contain;
  display: block;
  user-select: none;
  pointer-events: none;
}

.onboarding-slide-root {
  position: relative;
  width: 100%;
  overflow: hidden;
}

.onboarding-slide-pane {
  width: 100%;
}

.onboarding-q-next-enter-active,
.onboarding-q-next-leave-active,
.onboarding-q-prev-enter-active,
.onboarding-q-prev-leave-active {
  transition: transform 0.42s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.36s ease;
}

.onboarding-q-next-leave-from {
  transform: translateX(0);
  opacity: 1;
}

.onboarding-q-next-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.onboarding-q-next-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.onboarding-q-next-enter-to {
  transform: translateX(0);
  opacity: 1;
}

.onboarding-q-prev-leave-from {
  transform: translateX(0);
  opacity: 1;
}

.onboarding-q-prev-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.onboarding-q-prev-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}

.onboarding-q-prev-enter-to {
  transform: translateX(0);
  opacity: 1;
}

.onboarding-copy {
  width: 100%;
  text-align: left;
  margin-bottom: 1.25rem;
  box-sizing: border-box;
}

.onboarding-title {
  margin: 0 0 0.5rem;
  font-family: 'Bricolage Grotesque', sans-serif;
  font-weight: 600;
  font-size: 24px;
  line-height: 1.25;
  color: #1f2937;
}

.onboarding-subtitle {
  margin: 0;
  font-family: 'Comfortaa', sans-serif;
  font-weight: 400;
  font-size: 15px;
  line-height: 1.45;
  color: #4b5563;
}

.onboarding-options {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: stretch;
  margin-bottom: 1.5rem;
  box-sizing: border-box;
}

.onboarding-option-btn {
  width: 100%;
  min-height: 59px;
  padding: 0.5rem 1rem;
  box-sizing: border-box;
  border-radius: 0.75rem;
  border: 2px solid #79d45d;
  background: transparent;
  color: #79d45d;
  font-family: 'Comfortaa', sans-serif;
  font-weight: 700;
  font-size: 15px;
  line-height: 1.3;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.onboarding-option-btn:hover {
  background: rgba(121, 212, 93, 0.08);
}

.onboarding-option-btn--selected {
  background: #79d45d;
  border-color: #79d45d;
  color: #faf9f9;
}

.onboarding-option-btn--selected:hover {
  background: #6bc24d;
  border-color: #6bc24d;
  color: #faf9f9;
}

.onboarding-arrows {
  display: flex;
  width: 100%;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: auto;
  padding-top: 0.5rem;
  box-sizing: border-box;
}

.onboarding-arrow {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border: none;
  background: transparent;
  color: #d8d8d8;
  cursor: pointer;
  transition: color 0.15s ease, transform 0.1s ease;
}

.onboarding-arrow--back {
  padding: 0;
}

.onboarding-arrow:not(:disabled) {
  color: #79d45d;
}

.onboarding-arrow:not(:disabled):active {
  transform: scale(0.96);
}

.onboarding-arrow:disabled {
  cursor: default;
  color: #d8d8d8;
}

.onboarding-arrow--forward svg {
  transform: scaleX(-1);
}

.onboarding-panel {
  width: 100%;
  max-width: 28rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  background: transparent;
}

.onboarding-panel--state {
  align-items: center;
  justify-content: center;
  min-height: 40vh;
}

.onboarding-primary-btn {
  width: 100%;
  margin: 1rem 0 0;
  min-height: 52px;
  border: none;
  border-radius: 0.75rem;
  background: #79d45d;
  color: #faf9f9;
  font-family: 'Comfortaa', sans-serif;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.1s ease;
}

.onboarding-primary-btn:hover {
  background: #6bc24d;
}

.onboarding-primary-btn:active {
  transform: scale(0.99);
}

.onboarding-primary-btn--habits {
  margin-top: 1.25rem;
}

.onboarding-options--habits {
  margin-bottom: 1.5rem;
}

.onboarding-habit-card {
  width: 100%;
  min-height: 59px;
  box-sizing: border-box;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 2px solid #79d45d;
  background: transparent;
  cursor: pointer;
  text-align: left;
  font-family: 'Comfortaa', sans-serif;
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.onboarding-habit-card:hover {
  background: rgba(121, 212, 93, 0.08);
}

.onboarding-habit-card--selected {
  background: #79d45d;
  border-color: #79d45d;
}

.onboarding-habit-card--selected:hover {
  background: #6bc24d;
  border-color: #6bc24d;
}

.onboarding-habit-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.5rem;
  margin-bottom: 0.375rem;
}

.onboarding-habit-title {
  font-weight: 700;
  font-size: 15px;
  line-height: 1.3;
  color: #79d45d;
}

.onboarding-habit-card--selected .onboarding-habit-title {
  color: #faf9f9;
}

.onboarding-habit-check {
  flex-shrink: 0;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 2px solid #79d45d;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  font-weight: 700;
  color: transparent;
}

.onboarding-habit-check--checked {
  background: #79d45d;
  border-color: #79d45d;
  color: #faf9f9;
}

.onboarding-habit-card--selected .onboarding-habit-check {
  background: #faf9f9;
  border-color: #faf9f9;
  color: #79d45d;
}

.onboarding-habit-card--selected .onboarding-habit-check--checked {
  background: #faf9f9;
  border-color: #faf9f9;
  color: #79d45d;
}

.onboarding-habit-rutina {
  margin: 0 0 0.5rem;
  font-weight: 400;
  font-size: 14px;
  line-height: 1.4;
  color: #4b5563;
}

.onboarding-habit-card--selected .onboarding-habit-rutina {
  color: rgba(250, 249, 249, 0.92);
}

.onboarding-habit-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
  font-size: 12px;
}

.onboarding-habit-category {
  background: rgba(121, 212, 93, 0.2);
  color: #3a5826;
  padding: 0.2rem 0.45rem;
  border-radius: 0.25rem;
  font-weight: 700;
}

.onboarding-habit-card--selected .onboarding-habit-category {
  background: rgba(255, 255, 255, 0.25);
  color: #faf9f9;
}

.onboarding-habit-reward {
  color: #64748b;
  font-weight: 700;
}

.onboarding-habit-card--selected .onboarding-habit-reward {
  color: rgba(250, 249, 249, 0.9);
}

.onboarding-options--monsters {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  justify-items: center;
  padding: 0.5rem;
  max-width: 100%;
  overflow: hidden;
}

.onboarding-monster-card {
  width: 100%;
  max-width: 150px;
  aspect-ratio: 3 / 4;
  border-radius: 1rem;
  background: transparent;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid transparent;
}

.onboarding-monster-card:hover {
  transform: scale(1.05);
}

.onboarding-monster-card:hover .onboarding-monster-egg {
  animation: eggShake 0.5s ease-in-out;
}

.onboarding-monster-card--selected {
  border-color: #79d45d;
  background: rgba(121, 212, 93, 0.15);
}

.onboarding-monster-card-content {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  padding: 0.5rem;
}

.onboarding-monster-egg {
  width: 80%;
  height: 80%;
  object-fit: contain;
  transition: transform 0.3s ease;
}

@media (min-width: 640px) {
  .onboarding-options--monsters {
    gap: 1rem;
  }
  .onboarding-monster-card {
    max-width: 180px;
  }
}

@media (min-width: 768px) {
  .onboarding-options--monsters {
    gap: 1.5rem;
    padding: 1rem;
  }
  .onboarding-monster-card {
    max-width: 200px;
  }
}

@media (min-width: 1024px) {
  .onboarding-options--monsters {
    gap: 2rem;
  }
  .onboarding-monster-card {
    max-width: 220px;
  }
}

@keyframes eggShake {
  0%, 100% { transform: translateX(0) rotate(0); }
  20% { transform: translateX(-5px) rotate(-3deg); }
  40% { transform: translateX(5px) rotate(3deg); }
  60% { transform: translateX(-3px) rotate(-2deg); }
  80% { transform: translateX(3px) rotate(2deg); }
}

.loading-section {
  text-align: center;
  padding: 3rem 1rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(121, 212, 93, 0.25);
  border-top-color: #79d45d;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-text {
  font-family: 'Comfortaa', sans-serif;
  font-size: 15px;
  color: #64748b;
}

.error-toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background: #ef4444;
  color: white;
  padding: 1rem 2rem;
  border-radius: 0.5rem;
  font-weight: 500;
  z-index: 50;
  font-family: 'Comfortaa', sans-serif;
}
</style>
