<template>
  <div class="global-app-container onboarding-container">
    <div class="onboarding-lang-switch">
      <LanguageSwitcher />
    </div>

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
      <p class="onboarding-progress-text">Pas {{ currentStep }} de 4</p>
    </div>

    <!-- Main Content Card -->
    <div class="onboarding-card">
      <!-- Question 1: Objectiu (Goal) -->
      <div v-if="currentStep === 1" class="question-section">
        <div class="question-header">
          <span class="question-emoji">🎯</span>
          <h2 class="question-title">{{ $t('onboarding.question1.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question1.subtitle') }}</p>
        </div>
        <div class="options-grid">
          <button 
            v-for="option in goalOptions" 
            :key="option.value"
            class="option-btn"
            :class="{ selected: answers.objectiu === option.value }"
            @click="selectAnswer('objectiu', option.value)"
          >
            <span class="option-icon">{{ option.icon }}</span>
            <span class="option-label">{{ option.label }}</span>
          </button>
        </div>
      </div>

      <!-- Question 2: Energia (Energy) -->
      <div v-if="currentStep === 2" class="question-section">
        <div class="question-header">
          <span class="question-emoji">⚡</span>
          <h2 class="question-title">{{ $t('onboarding.question2.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question2.subtitle') }}</p>
        </div>
        <div class="options-grid">
          <button 
            v-for="option in energyOptions" 
            :key="option.value"
            class="option-btn"
            :class="{ selected: answers.energia === option.value }"
            @click="selectAnswer('energia', option.value)"
          >
            <span class="option-icon">{{ option.icon }}</span>
            <span class="option-label">{{ option.label }}</span>
          </button>
        </div>
      </div>

      <!-- Question 3: Obstacle -->
      <div v-if="currentStep === 3" class="question-section">
        <div class="question-header">
          <span class="question-emoji">🏔️</span>
          <h2 class="question-title">{{ $t('onboarding.question3.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question3.subtitle') }}</p>
        </div>
        <div class="options-grid">
          <button 
            v-for="option in obstacleOptions" 
            :key="option.value"
            class="option-btn"
            :class="{ selected: answers.obstacle === option.value }"
            @click="selectAnswer('obstacle', option.value)"
          >
            <span class="option-icon">{{ option.icon }}</span>
            <span class="option-label">{{ option.label }}</span>
          </button>
        </div>
      </div>

      <!-- Question 4: Temps (Time) -->
      <div v-if="currentStep === 4" class="question-section">
        <div class="question-header">
          <span class="question-emoji">⏰</span>
          <h2 class="question-title">{{ $t('onboarding.question4.title') }}</h2>
          <p class="question-subtitle">{{ $t('onboarding.question4.subtitle') }}</p>
        </div>
        <div class="options-grid">
          <button 
            v-for="option in timeOptions" 
            :key="option.value"
            class="option-btn"
            :class="{ selected: answers.temps === option.value }"
            @click="selectAnswer('temps', option.value)"
          >
            <span class="option-icon">{{ option.icon }}</span>
            <span class="option-label">{{ option.label }}</span>
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
          <span class="question-emoji">💡</span>
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
              <span class="habit-reward">🏆 {{ habit.recompensa }}</span>
            </div>
          </div>
        </div>
        <button 
          class="confirm-btn" 
          :disabled="selectedHabits.length === 0"
          @click="confirmHabits"
        >
          {{ $t('onboarding.confirm') }} ({{ selectedHabits.length }})
        </button>
      </div>

      <!-- Navigation Buttons -->
      <div v-if="!showHabitsSelection && !isLoading" class="navigation-buttons">
        <button 
          v-if="currentStep > 1" 
          class="nav-btn back-btn"
          @click="previousStep"
        >
          {{ $t('onboarding.back') }}
        </button>
        <button 
          class="nav-btn next-btn"
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
import { ref, computed } from 'vue';
import { authFetch } from '~/composables/useApi.js';

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

const goalOptions = [
  { value: 'salut', label: 'Salut', icon: '💪' },
  { value: 'productivitat', label: 'Productivitat', icon: '📈' },
  { value: 'ment', label: 'Ment', icon: '🧠' },
  { value: 'aprenentatge', label: 'Aprenentatge', icon: '📚' },
];

const energyOptions = [
  { value: 'mati', label: 'Matí', icon: '🌅' },
  { value: 'migdia', label: 'Migdia', icon: '☀️' },
  { value: 'tarda', label: 'Tarda', icon: '🌇' },
  { value: 'nit', label: 'Nit', icon: '🌙' },
];

const obstacleOptions = [
  { value: 'estress', label: 'Estrès', icon: '😰' },
  { value: 'temps', label: 'Temps', icon: '⏳' },
  { value: 'memoria', label: 'Memòria', icon: '🧠' },
  { value: 'andra', label: 'Mandra', icon: '😴' },
];

const timeOptions = [
  { value: '15min', label: '<15 min', icon: '⚡' },
  { value: '30min', label: '30 min', icon: '⏰' },
  { value: '1h', label: '1 hora', icon: '🕐' },
  { value: '1h+', label: '+1 hora', icon: '🌟' },
];

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
    const response = await fetch(`${config.public.socketUrl}/api/onboarding/generate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        categoria: answers.value.objectiu,
        senyal: answers.value.energia,
        dificultat: answers.value.obstacle,
        temps: answers.value.temps,
      }),
    });

    const data = await response.json();

    if (data.success && data.habits) {
      generatedHabits.value = data.habits;
      showHabitsSelection.value = true;
    } else {
      errorMessage.value = data.message || 'Error generant hàbits';
    }
  } catch (error) {
    console.error('Error generating habits:', error);
    errorMessage.value = 'Error de connexió';
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
      navigateTo('/home');
    } else {
      const data = await response.json();
      errorMessage.value = data.message || 'Error guardant hàbits';
    }
  } catch (error) {
    console.error('Error saving habits:', error);
    errorMessage.value = 'Error de connexió';
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
    'temps': 'mitjana',
    'memoria': 'mitjana',
    'andra': 'dificil',
  };
  return map[obstacle] || 'mitjana';
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
</script>

<style scoped>
.onboarding-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
}

.onboarding-lang-switch {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
}

.onboarding-progress-container {
  width: 100%;
  max-width: 400px;
  margin-bottom: 2rem;
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
  background: #e2e8f0;
  transition: all 0.3s ease;
}

.progress-segment.completed {
  background: #10b981;
}

.progress-segment.active {
  background: #3b82f6;
}

.onboarding-progress-text {
  text-align: center;
  font-size: 0.875rem;
  color: #64748b;
}

.onboarding-card {
  width: 100%;
  max-width: 600px;
  background: white;
  border-radius: 2rem;
  padding: 2.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
}

.question-header {
  text-align: center;
  margin-bottom: 2rem;
}

.question-emoji {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

.question-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.question-subtitle {
  color: #64748b;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.option-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.option-btn:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
}

.option-btn.selected {
  border-color: #3b82f6;
  background: #eff6ff;
}

.option-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.option-label {
  font-weight: 600;
  color: #374151;
}

.navigation-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
}

.nav-btn {
  padding: 0.75rem 2rem;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-btn {
  background: #f1f5f9;
  border: none;
  color: #64748b;
}

.back-btn:hover {
  background: #e2e8f0;
}

.next-btn {
  background: #3b82f6;
  border: none;
  color: white;
}

.next-btn:hover:not(:disabled) {
  background: #2563eb;
}

.next-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.loading-section {
  text-align: center;
  padding: 3rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e2e8f0;
  border-top-color: #3b82f6;
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
  border-color: #3b82f6;
}

.habit-card.selected {
  border-color: #10b981;
  background: #f0fdf4;
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
  background: #10b981;
  border-color: #10b981;
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
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.habit-reward {
  color: #64748b;
}

.confirm-btn {
  width: 100%;
  padding: 1rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.confirm-btn:hover:not(:disabled) {
  background: #059669;
}

.confirm-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
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
