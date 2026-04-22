<template>
  <div class="global-app-container onboarding-page onboarding-container">
    <!-- Progress Bar -->
    <div class="onboarding-progress-container">
      <div class="onboarding-progress-bar">
        <div 
          v-for="step in 4" 
          :key="step" 
          class="progress-segment"
          :class="{ 
            'completed': currentStep > step,
            'active': currentStep === step
          }"
        ></div>
      </div>
      <p class="onboarding-progress-text">{{ $t('onboarding.progress', { current: currentStep }) }}</p>
    </div>

    <!-- Main Content Card -->
    <div class="onboarding-card">
      <!-- Question 1: Objectiu (Goal) -->
      <div v-if="currentStep === 1" class="question-section">
        <div class="question-header">
          <h2 class="question-title">{{ $t('onboarding.question1.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question1.subtitle') }}</p>
        </div>
        <div class="options-list">
          <button 
            v-for="option in goalOptions" 
            :key="option.value"
            type="button"
            class="option-btn"
            :class="{ 'option-btn--selected': answers.objectiu === option.value }"
            @click="selectAnswer('objectiu', option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <!-- Question 2: Energia (Energy) -->
      <div v-if="currentStep === 2" class="question-section">
        <div class="question-header">
          <h2 class="question-title">{{ $t('onboarding.question2.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question2.subtitle') }}</p>
        </div>
        <div class="options-list">
          <button 
            v-for="option in energyOptions" 
            :key="option.value"
            type="button"
            class="option-btn"
            :class="{ 'option-btn--selected': answers.energia === option.value }"
            @click="selectAnswer('energia', option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <!-- Question 3: Obstacle -->
      <div v-if="currentStep === 3" class="question-section">
        <div class="question-header">
          <h2 class="question-title">{{ $t('onboarding.question3.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question3.subtitle') }}</p>
        </div>
        <div class="options-list">
          <button 
            v-for="option in obstacleOptions" 
            :key="option.value"
            type="button"
            class="option-btn"
            :class="{ 'option-btn--selected': answers.obstacle === option.value }"
            @click="selectAnswer('obstacle', option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <!-- Question 4: Temps (Time) -->
      <div v-if="currentStep === 4" class="question-section">
        <div class="question-header">
          <h2 class="question-title">{{ $t('onboarding.question4.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question4.subtitle') }}</p>
        </div>
        <div class="options-list">
          <button 
            v-for="option in timeOptions" 
            :key="option.value"
            type="button"
            class="option-btn"
            :class="{ 'option-btn--selected': answers.temps === option.value }"
            @click="selectAnswer('temps', option.value)"
          >
            {{ option.label }}
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-section">
        <div class="loading-spinner"></div>
        <p class="loading-text">{{ $t('onboarding.generating_habits') }}</p>
      </div>

      <!-- Habits Selection -->
      <div v-if="showHabitsSelection && !isLoading" class="habits-section">
        <div class="question-header">
          <h2 class="question-title">{{ $t('onboarding.habits.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.habits.subtitle') }}</p>
        </div>
        <div class="habits-list">
          <div 
            v-for="(habit, index) in generatedHabits" 
            :key="index"
            class="habit-card"
            :class="{ selected: selectedHabits.includes(index) }"
            @click="toggleHabit(index)"
          >
            <div class="habit-card-header">
              <span class="habit-title">{{ habit.titol }}</span>
              <span class="habit-check" :class="{ checked: selectedHabits.includes(index) }">✓</span>
            </div>
            <p class="habit-rutina">{{ habit.rutina }}</p>
            <div class="habit-meta">
              <span class="habit-category">{{ habit.categoria }}</span>
              <span class="habit-reward">{{ habit.recompensa }}</span>
            </div>
          </div>
        </div>
        <p class="habits-enter-hint" :class="{ 'habits-enter-hint--empty': selectedHabits.length === 0 }">
          {{ selectedHabits.length === 0
            ? $t('onboarding.habits.hint_zero')
            : selectedHabits.length === 1
              ? $t('onboarding.habits.hint_one')
              : $t('onboarding.habits.hint_other', { n: selectedHabits.length })
          }}
        </p>
        <button 
          type="button"
          class="login-btn-primary w-full mt-3" 
          @click="confirmHabits"
        >
          {{ $t('onboarding.enter_app') }}
        </button>
      </div>

      <!-- Navigation Buttons -->
      <div v-if="!showHabitsSelection && !isLoading" class="onboarding-nav">
        <button 
          v-if="currentStep > 1" 
          type="button"
          class="login-btn-outline onboarding-nav-btn"
          @click="previousStep"
        >
          {{ $t('onboarding.back') }}
        </button>
        <button 
          type="button"
          class="login-btn-primary onboarding-nav-btn"
          :disabled="!canProceed"
          @click="nextStep"
        >
          {{ currentStep === 4 ? $t('onboarding.generate') : $t('onboarding.next') }}
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="error-toast">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { authFetch } from '~/composables/useApi.js';
import { useHabitStore } from '~/stores/useHabitStore.js';
import { useAuthStore } from '~/stores/useAuthStore.js';

definePageMeta({ layout: false });

const { t, setLocale } = useI18n();
const onboardingDoneCookie = useCookie('loopy_onboarding_done', { sameSite: 'lax', maxAge: 60 * 60 * 24 * 365 });
const habitStore = useHabitStore();
/** Si és cert, al triar resposta a cada pas es passa automàticament al següent (hàbits sempre manual). */
const AUTO_ADVANCE_STEPS = true;

onMounted(function () {
  setLocale('ca');
});

const config = useRuntimeConfig();

const currentStep = ref(1);
const isLoading = ref(false);
const showHabitsSelection = ref(false);
const errorMessage = ref('');

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
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

async function nextStep() {
  if (currentStep.value < 4) {
    currentStep.value++;
  } else {
    await generateHabits();
  }
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

function toggleHabit(index) {
  const idx = selectedHabits.value.indexOf(index);
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
    const habitsToSave = selectedHabits.value.map(index => generatedHabits.value[index]);
    if (habitsToSave.length === 0) {
      habitStore.establirHabitsDesDeApi([]);
      marcarOnboardingCompletat();
      navigateTo('/home');
      return;
    }

    const response = await authFetch('/api/habits/assign', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        habits: habitsToSave.map(h => ({
          titol: h.titol,
          categoria_id: mapCategoria(h.categoria),
          dificultat: mapDificultat(answers.value.obstacle),
          objectiu_vegades: mapTemps(answers.value.temps),
        })),
      }),
    });

    if (response.ok) {
      const data = await response.json();
      if (data && Array.isArray(data.habits)) {
        habitStore.establirHabitsDesDeApi(data.habits);
      }
      marcarOnboardingCompletat();
      navigateTo('/home');
    } else {
      const data = await response.json();
      errorMessage.value = data.message || t('onboarding.errors.save');
    }
  } catch (error) {
    console.error('Error saving habits:', error);
    errorMessage.value = t('onboarding.errors.connection');
  } finally {
    isLoading.value = false;
  }
}

function mapCategoria(categoria) {
  const map = {
    'salut': 1,
    'productivitat': 2,
    'ment': 3,
    'aprenentatge': 4,
  };
  return map[categoria?.toLowerCase()] || 1;
}

function mapDificultat(obstacle) {
  const map = {
    'estress': 'facil',
    'temps': 'media',
    'memoria': 'media',
    'andra': 'dificil',
  };
  return map[obstacle] || 'media';
}

function mapTemps(temps) {
  const map = {
    '15min': 1,
    '30min': 1,
    '1h': 1,
    '1h+': 2,
  };
  return map[temps] || 1;
}

function marcarOnboardingCompletat() {
  const authStore = useAuthStore();
  onboardingDoneCookie.value = '1';
  if (typeof window !== 'undefined') {
    localStorage.setItem('loopy_onboarding_done', '1');
    if (authStore.user && authStore.user.id != null) {
      localStorage.setItem('loopy_onboarding_user_id', String(authStore.user.id));
    }
  }
}

function generarHabitsRapids() {
  const categoria = answers.value.objectiu || 'salut';
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
</script>

<style scoped>
.onboarding-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem 3rem;
  position: relative;
  z-index: 1;
}

.onboarding-progress-container {
  width: 100%;
  max-width: 28rem;
  margin-bottom: 1.5rem;
}

.onboarding-progress-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 0.5rem;
}

.progress-segment {
  flex: 1;
  height: 8px;
  border-radius: 4px;
  background: #e5e7eb;
  transition: background 0.3s ease;
}

.progress-segment.completed {
  background: #568039;
}

.progress-segment.active {
  background: #7cb342;
}

.onboarding-progress-text {
  text-align: center;
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.onboarding-card {
  width: 100%;
  max-width: 28rem;
  background: #ffffff;
  border-radius: 1.5rem;
  padding: 2rem 1.5rem;
  box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(0, 0, 0, 0.06);
  position: relative;
  z-index: 2;
}

.question-header {
  text-align: left;
  margin-bottom: 1.5rem;
}

.question-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #3a5826;
  margin-bottom: 0.5rem;
  line-height: 1.25;
}

.question-subtitle {
  color: #6b7280;
  font-size: 0.95rem;
  font-weight: 500;
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

.option-btn {
  width: 100%;
  padding: 1rem 1.25rem;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  color: #1f2937;
  font-weight: 600;
  font-size: 0.95rem;
  text-align: left;
  cursor: pointer;
  transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.option-btn:hover {
  border-color: rgba(81, 125, 54, 0.45);
  background: #ffffff;
}

.option-btn--selected {
  background: #568039;
  border-color: #568039;
  color: #ffffff;
  box-shadow: 0 4px 0 0 #3f5e29;
}

.option-btn--selected:hover {
  background: #45682c;
  border-color: #45682c;
}

.onboarding-nav {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.75rem;
  width: 100%;
}

.onboarding-nav-btn {
  width: 100%;
}

.loading-section {
  text-align: center;
  padding: 3rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: #568039;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-text {
  color: #64748b;
}

.habits-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.habit-card {
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.habit-card:hover {
  border-color: rgba(81, 125, 54, 0.5);
}

.habit-card.selected {
  border-color: #568039;
  background: #f7faf5;
}

.habit-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.habit-title {
  font-weight: 600;
  color: #1f2937;
}

.habit-check {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  color: transparent;
}

.habit-check.checked {
  background: #568039;
  border-color: #568039;
  color: white;
}

.habit-rutina {
  color: #64748b;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.habit-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
}

.habit-category {
  background: #e8f5e9;
  color: #2e7d32;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.habit-reward {
  color: #64748b;
}

.habits-enter-hint {
  text-align: center;
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
  line-height: 1.4;
}

.habits-enter-hint--empty {
  color: #94a3b8;
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
}
</style>
