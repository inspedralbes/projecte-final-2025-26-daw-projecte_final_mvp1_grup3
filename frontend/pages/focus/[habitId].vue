<template>
  <section class="focus-mobile-screen">
    <header class="focus-topbar" :class="{ 'is-search-open': isSearchOpen }">
      <button
        v-if="!isSearchOpen"
        class="focus-icon-btn"
        data-testid="focus-exit-button"
        @click="handleManualExit"
        aria-label="Sortir del mode focus"
      >
        <svg width="73" height="73" viewBox="0 0 73 73" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M42.5834 54.75L24.3334 36.5L42.5834 18.25L46.8417 22.5083L32.85 36.5L46.8417 50.4917L42.5834 54.75Z" fill="#FAF9F9" />
        </svg>
      </button>

      <div class="focus-search-bar" :class="{ 'is-open': isSearchOpen }" aria-label="Cercar musica">
        <button
          class="focus-icon-btn focus-search-btn"
          :class="{ 'is-open': isSearchOpen }"
          data-testid="focus-search-button"
          @click="toggleSearch"
          :aria-label="isSearchOpen ? 'Tancar cercador' : 'Obrir cercador'"
        >
          <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path
              d="M28.875 28.875L22.8937 22.8937M26.125 15.125C26.125 21.2001 21.2001 26.125 15.125 26.125C9.04987 26.125 4.125 21.2001 4.125 15.125C4.125 9.04987 9.04987 4.125 15.125 4.125C21.2001 4.125 26.125 9.04987 26.125 15.125Z"
              stroke="#FAF9F9"
              stroke-width="4"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>

        <div v-show="isSearchOpen" class="focus-search-input-wrap">
          <input
            ref="searchInputEl"
            v-model="searchQuery"
            class="focus-search-input"
            type="text"
            autocomplete="off"
            autocapitalize="off"
            spellcheck="false"
            placeholder="Escriu per cercar..."
            aria-label="Escriu per cercar videos de YouTube"
          />
          <svg class="focus-search-line" width="297" height="4" viewBox="0 0 297 4" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <line x1="1.00006" y1="2.06111" x2="296" y2="0.999956" stroke="white" stroke-width="2" stroke-linecap="round" />
          </svg>
        </div>
      </div>
    </header>

    <div v-if="isSearchOpen" class="focus-search-overlay" @click="closeSearch" aria-hidden="true"></div>

    <section v-if="isSearchOpen" class="focus-search-results" aria-label="Resultats de cerca">
      <p v-if="searchQuery.trim().length < 2" class="focus-search-hint">Escriu almenys 2 caràcters.</p>
      <p v-else-if="searchLoading" class="focus-search-hint">Cercant...</p>
      <p v-else-if="searchError" class="focus-search-hint">{{ searchError }}</p>
      <button
        v-for="(item, index) in searchResults"
        :key="String(item && item.api_id ? item.api_id : index)"
        class="focus-search-item"
        type="button"
        @click="selectSearchResult(index)"
      >
        <span class="focus-search-dot" aria-hidden="true"></span>
        <span class="focus-search-text">
          <span class="focus-search-title">{{ item && item.titol ? item.titol : "Video" }}</span>
          <span class="focus-search-meta">- {{ formatResultDuration(item) }}</span>
        </span>
        <span class="focus-search-arrow" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 12H19M12 19L19 12L12 5" stroke="#FAF9F9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
      </button>
    </section>

    <div v-show="!isSearchOpen" class="focus-main-content">
      <p class="focus-main-timer" data-testid="focus-remaining-time-label">{{ remainingTimeLabel }}</p>
      <div class="focus-daily-sets" aria-label="Progres diari de sets focus">
        <span
          v-for="index in 4"
          :key="'focus-set-' + index"
          class="focus-daily-set-dot"
          :class="{ 'is-completed': index <= dailyFocusSets }"
        ></span>
      </div>

      <div class="focus-creature-wrap">
        <svg class="focus-ring" viewBox="0 0 340 340" aria-hidden="true">
          <circle class="focus-ring-track" cx="170" cy="170" r="154" />
          <circle
            class="focus-ring-progress"
            cx="170"
            cy="170"
            r="154"
            :stroke-dasharray="ringCircumference"
            :stroke-dashoffset="ringOffset"
          />
          <circle
            class="focus-ring-marker"
            :cx="ringMarkerX"
            :cy="ringMarkerY"
            r="8"
          />
        </svg>

        <div class="focus-creature" aria-hidden="true">
          <img
            :src="focusMonsterSrc"
            alt="Monstre focus"
            class="focus-creature__img"
            decoding="async"
            draggable="false"
          />
        </div>
      </div>

      <section class="focus-player">
        <div class="focus-player-track-block">
          <button class="focus-track-play-btn" @click="toggleTrackPlayback" aria-label="Play pausa canco">
            <svg v-if="isTrackPaused || !currentTrackVideoId" width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M41.0833 29L20.5416 40.8606V17.1394L41.0833 29Z" fill="#FAF9F9" />
            </svg>
            <svg v-else width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M21 18H27V40H21V18ZM31 18H37V40H31V18Z" fill="#FAF9F9" />
            </svg>
          </button>

          <div class="focus-player-progress-row">
            <button class="focus-media-btn" @click="handlePrevTrack" aria-label="Pista anterior">
              <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15.625 37.5V12.5H11.4583V37.5H15.625ZM38.5416 37.5V12.5L19.7916 25L38.5416 37.5Z" fill="#FAF9F9" />
              </svg>
            </button>
            <input
              class="focus-player-progress"
              type="range"
              min="0"
              max="100"
              :value="playerProgress"
              :style="playerProgressStyle"
              @input="updateProgressFromSlider($event.target.value)"
              aria-label="Progressio de la pista"
            />
            <button class="focus-media-btn" @click="handleNextTrack" aria-label="Seguent pista">
              <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M34.375 37.5V12.5H38.5417V37.5H34.375ZM11.4584 37.5V12.5L30.2084 25L11.4584 37.5Z" fill="#FAF9F9" />
              </svg>
            </button>
          </div>

          <p class="focus-track-progress-time">{{ currentTrackProgressLabel }}</p>
        </div>
        <div class="focus-player-controls">
          <button
            class="focus-mode-btn"
            :class="{ 'is-active': selectedMode === '25_5' }"
            data-testid="focus-preset-25_5"
            @click="applyMode('25_5')"
          >
            <span class="focus-mode-btn-multiline">Deep<br />Focus</span>
          </button>
          <button
            class="focus-media-btn focus-media-btn-main"
            data-testid="focus-play-pause-button"
            @click="toggleTimer"
            aria-label="Play pausa focus"
          >
            <span v-if="isRunning">&#9632;</span>
            <span v-else>&#9654;</span>
          </button>
          <button
            class="focus-mode-btn"
            :class="{ 'is-active': selectedMode === '50_10' }"
            data-testid="focus-preset-50_10"
            @click="applyMode('50_10')"
          >
            Slightly Focus
          </button>
        </div>
      </section>
    </div>

    <div v-if="currentTrackEmbedUrl" class="focus-audio-embed" aria-hidden="true">
      <iframe
        ref="youtubeIframeEl"
        :src="currentTrackEmbedUrl"
        title="Focus audio player"
        width="0"
        height="0"
        frameborder="0"
        allow="autoplay; encrypted-media"
      ></iframe>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { useGameStore } from "~/stores/gameStore.js";
import { useHabitStore } from "~/stores/useHabitStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { authFetch } from "~/composables/useApi.js";
import { useFocusSession } from "~/composables/user/useFocusSession.js";
import { enqueuePendingFocusEvent } from "~/composables/user/useFocusEventQueue.js";
import { getFocusMonsterKey } from "~/utils/monsterImage.js";

import focusM11 from "~/assets/img/ModeFocus/Monstres/1.1-MonstrePetitFocus.png";
import focusM12 from "~/assets/img/ModeFocus/Monstres/1.2-MonstreMitjaFocus.png";
import focusM13 from "~/assets/img/ModeFocus/Monstres/1.3-MonstreGranFocus.png";
import focusM14 from "~/assets/img/ModeFocus/Monstres/1.4-MonstreFortFocus.png";
import focusM21 from "~/assets/img/ModeFocus/Monstres/2.1-MonstrePetitFocus.png";
import focusM22 from "~/assets/img/ModeFocus/Monstres/2.2-MonstreMitjaFocus.png";
import focusM23 from "~/assets/img/ModeFocus/Monstres/2.3-MonstreGranFocus.png";
import focusM24 from "~/assets/img/ModeFocus/Monstres/2.4-MonstreFortFocus.png";
import focusM31 from "~/assets/img/ModeFocus/Monstres/3.1-MonstrePetitFocus.png";
import focusM32 from "~/assets/img/ModeFocus/Monstres/3.2-MonstreMitjaFocus.png";
import focusM33 from "~/assets/img/ModeFocus/Monstres/3.3-MonstreGranFocus.png";
import focusM34 from "~/assets/img/ModeFocus/Monstres/3.4-MonstreFortFocus.png";
import focusM41 from "~/assets/img/ModeFocus/Monstres/4.1-MonstrePetitFocus.png";
import focusM42 from "~/assets/img/ModeFocus/Monstres/4.2-MonstreMitjaFocus.png";
import focusM43 from "~/assets/img/ModeFocus/Monstres/4.3-MonstreGranFocus.png";
import focusM44 from "~/assets/img/ModeFocus/Monstres/4.4-MonstreFortFocus.png";

var FOCUS_MONSTER_MAP = {
  "1.1": focusM11, "1.2": focusM12, "1.3": focusM13, "1.4": focusM14,
  "2.1": focusM21, "2.2": focusM22, "2.3": focusM23, "2.4": focusM24,
  "3.1": focusM31, "3.2": focusM32, "3.3": focusM33, "3.4": focusM34,
  "4.1": focusM41, "4.2": focusM42, "4.3": focusM43, "4.4": focusM44
};

var route = useRoute();
var gameStore = useGameStore();
var habitStore = useHabitStore();
var authStore = useAuthStore();
var habit = ref(null);
var currentTrackName = ref("11:32");
var previousPredominantMode = ref(null);
var playerProgress = ref(58);
var dailyFocusSets = ref(0);
var musicSearchResults = ref([]);
var currentTrackIndex = ref(-1);
var currentTrackVideoId = ref("");
var currentTrackDurationSeconds = ref(0);
var trackElapsedSeconds = ref(0);
var isTrackPaused = ref(false);
var isSearchOpen = ref(false);
var searchQuery = ref("");
var searchResults = ref([]);
var searchLoading = ref(false);
var searchError = ref("");
var searchInputEl = ref(null);
var youtubeIframeEl = ref(null);
var searchDebounceTimer = null;
var searchAbortController = null;
var trackProgressTimer = null;

var {
  selectedMode,
  phase,
  sessionState,
  remainingSeconds,
  accumulatedWorkSeconds,
  canStart,
  isRunning,
  remainingTimeLabel,
  modeConfig,
  applyMode,
  startOrResume,
  pause,
  exitSession,
  markCompleted
} = useFocusSession();

var ringCircumference = 2 * Math.PI * 154;

var ringOffset = computed(function () {
  if (!modeConfig.value) {
    return ringCircumference;
  }

  var phaseSeconds;
  if (phase.value === "work") {
    phaseSeconds = modeConfig.value.workMinutes * 60;
  } else {
    phaseSeconds = modeConfig.value.restMinutes * 60;
  }

  if (phaseSeconds <= 0) {
    return ringCircumference;
  }

  var completed = phaseSeconds - remainingSeconds.value;
  if (completed < 0) {
    completed = 0;
  }
  if (completed > phaseSeconds) {
    completed = phaseSeconds;
  }

  var percent = completed / phaseSeconds;
  return ringCircumference * (1 - percent);
});

var ringProgressPercent = computed(function () {
  if (!modeConfig.value) {
    return 0;
  }
  var phaseSeconds = phase.value === "work" ? modeConfig.value.workMinutes * 60 : modeConfig.value.restMinutes * 60;
  if (phaseSeconds <= 0) {
    return 0;
  }
  var completed = phaseSeconds - remainingSeconds.value;
  if (completed < 0) completed = 0;
  if (completed > phaseSeconds) completed = phaseSeconds;
  return completed / phaseSeconds;
});

var ringMarkerX = computed(function () {
  var angle = 2 * Math.PI * ringProgressPercent.value;
  return 170 + 154 * Math.cos(angle);
});

var ringMarkerY = computed(function () {
  var angle = 2 * Math.PI * ringProgressPercent.value;
  return 170 + 154 * Math.sin(angle);
});

var currentTrackProgressLabel = computed(function () {
  var totalSeconds = currentTrackDurationSeconds.value;
  if (!Number.isFinite(totalSeconds) || totalSeconds <= 0) {
    return "0:00";
  }
  var remaining = totalSeconds - trackElapsedSeconds.value;
  if (remaining < 0) {
    remaining = 0;
  }
  return formatSecondsForTrack(remaining);
});

var playerProgressStyle = computed(function () {
  var p = Math.max(0, Math.min(100, Number(playerProgress.value) || 0));
  return {
    background: "linear-gradient(to right, #16420A 0%, #16420A " + p + "%, #FAF9F9 " + p + "%, #FAF9F9 100%)"
  };
});

var currentTrackEmbedUrl = computed(function () {
  if (!currentTrackVideoId.value || isTrackPaused.value) {
    return "";
  }
  return "https://www.youtube.com/embed/" + currentTrackVideoId.value + "?autoplay=1&controls=0&rel=0&modestbranding=1&enablejsapi=1";
});

var focusMonsterSrc = computed(function () {
  var user = authStore.user;
  if (!user || !user.monstre_tipus) return focusM11;
  var key = getFocusMonsterKey(user.monstre_tipus, user.nivell);
  return FOCUS_MONSTER_MAP[key] || focusM11;
});

var currentModeLabel = computed(function () {
  if (selectedMode.value === "50_10") {
    return "Slightly Focus";
  }
  return "Deep Focus";
});

var focusGoalSeconds = computed(function () {
  if (!habit.value) return 0;
  var unit = habit.value.unitat || "vegades";
  var amount = Number(habit.value.objectiuVegades || 1);
  if (unit === "minuts") {
    return amount * 60;
  }
  return amount * 60;
});

function resolveHabit() {
  var habitId = Number(route.params.habitId);
  var list = habitStore.habits || [];
  habit.value = list.find(function (item) { return Number(item.id) === habitId; }) || null;
}

function saveFocusSnapshot(reason) {
  if (!habit.value) return;
  var key = "loopy_focus_sessions";
  var raw = localStorage.getItem(key);
  var sessions = raw ? JSON.parse(raw) : [];
  sessions.push({
    habitId: habit.value.id,
    date: new Date().toISOString(),
    reason: reason,
    mode: selectedMode.value || null,
    focusedMinutes: Math.floor(accumulatedWorkSeconds.value / 60)
  });
  localStorage.setItem(key, JSON.stringify(sessions));
}

function loadDailyFocusSets() {
  var key = "loopy_focus_daily_sets";
  var raw = localStorage.getItem(key);
  var map = raw ? JSON.parse(raw) : {};
  var count = Number(map[todayIsoDate()] || 0);
  if (!Number.isFinite(count) || count < 0) {
    count = 0;
  }
  if (count > 4) {
    count = 4;
  }
  dailyFocusSets.value = count;
}

function persistDailyFocusSets() {
  var key = "loopy_focus_daily_sets";
  var raw = localStorage.getItem(key);
  var map = raw ? JSON.parse(raw) : {};
  map[todayIsoDate()] = dailyFocusSets.value;
  localStorage.setItem(key, JSON.stringify(map));
}

function incrementDailyFocusSets() {
  if (dailyFocusSets.value >= 4) return;
  dailyFocusSets.value += 1;
  persistDailyFocusSets();
}

function emitFocusUpdate(eventName, queueOnFailure) {
  if (!habit.value) return false;

  var payload = {
    habit_id: habit.value.id,
    mode: selectedMode.value || null,
    minutes: Math.floor(accumulatedWorkSeconds.value / 60),
    event: eventName,
    data: new Date().toISOString()
  };

  try {
    var nuxt = useNuxtApp();
    var socket = nuxt && nuxt.$socket ? nuxt.$socket : null;
    if (!socket || !socket.connected) {
      if (queueOnFailure) {
        enqueuePendingFocusEvent(payload);
      }
      return false;
    }

    socket.emit("habit_focus_update", payload);
    return true;
  } catch (e) {
    if (queueOnFailure) {
      enqueuePendingFocusEvent(payload);
    }
  }

  return false;
}

function todayIsoDate() {
  var now = new Date();
  var yyyy = String(now.getFullYear());
  var mm = String(now.getMonth() + 1).padStart(2, "0");
  var dd = String(now.getDate()).padStart(2, "0");
  return yyyy + "-" + mm + "-" + dd;
}

async function loadFocusSessionContext() {
  authStore.loadFromStorage();
  var userId = authStore.user && authStore.user.id ? Number(authStore.user.id) : null;
  if (!userId || !habit.value) {
    return;
  }

  var url = "/api/calendar/snapshot/" + userId + "/" + todayIsoDate();
  try {
    var response = await authFetch(url, {});
    if (!response.ok) {
      return;
    }
    var snapshot = await response.json();
    var habits = snapshot && Array.isArray(snapshot.habits_json) ? snapshot.habits_json : [];
    var i;
    for (i = 0; i < habits.length; i++) {
      if (Number(habits[i].id) !== Number(habit.value.id)) {
        continue;
      }
      previousPredominantMode.value = habits[i].predominant_focus_mode || null;
      if (!selectedMode.value && previousPredominantMode.value && (previousPredominantMode.value === "25_5" || previousPredominantMode.value === "50_10")) {
        applyMode(previousPredominantMode.value);
      }
      break;
    }
  } catch (e) {}
}

async function handleMusicSearch() {
  toggleSearch();
}

function toggleSearch() {
  if (isSearchOpen.value) {
    closeSearch();
    return;
  }
  isSearchOpen.value = true;
  searchError.value = "";
  if (typeof window !== "undefined") {
    setTimeout(function () {
      if (searchInputEl.value && searchInputEl.value.focus) {
        searchInputEl.value.focus();
      }
    }, 50);
  }
}

function closeSearch() {
  isSearchOpen.value = false;
  searchError.value = "";
  if (searchAbortController) {
    try {
      searchAbortController.abort();
    } catch (e) {}
  }
}

function onKeydown(e) {
  if (!isSearchOpen.value) return;
  if (!e) return;
  if (e.key === "Escape") {
    closeSearch();
  }
}

function formatResultDuration(item) {
  if (!item) return "--:--";
  if (item.duracio) return String(item.duracio);
  if (item.duration) return String(item.duration);
  return "mm:ss";
}

function parseDurationToSeconds(rawDuration) {
  var value = String(rawDuration || "").trim();
  if (value === "") return 0;
  var parts = value.split(":");
  var i;
  var seconds = 0;

  if (parts.length !== 2 && parts.length !== 3) {
    return 0;
  }

  for (i = 0; i < parts.length; i++) {
    if (!/^\d+$/.test(parts[i])) {
      return 0;
    }
  }

  if (parts.length === 2) {
    seconds = Number(parts[0]) * 60 + Number(parts[1]);
  } else {
    seconds = Number(parts[0]) * 3600 + Number(parts[1]) * 60 + Number(parts[2]);
  }
  return seconds;
}

function formatSecondsForTrack(total) {
  var seconds = Math.max(0, Math.floor(Number(total) || 0));
  var hours = Math.floor(seconds / 3600);
  var minutes = Math.floor((seconds % 3600) / 60);
  var secs = seconds % 60;

  if (hours > 0) {
    return String(hours) + ":" + String(minutes).padStart(2, "0") + ":" + String(secs).padStart(2, "0");
  }
  return String(minutes) + ":" + String(secs).padStart(2, "0");
}

function toggleTrackPlayback() {
  if (!currentTrackVideoId.value) {
    return;
  }
  isTrackPaused.value = !isTrackPaused.value;
}

function syncPlayerProgressFromElapsed() {
  var total = Number(currentTrackDurationSeconds.value || 0);
  if (!Number.isFinite(total) || total <= 0) {
    playerProgress.value = 0;
    return;
  }
  var percent = (trackElapsedSeconds.value / total) * 100;
  if (percent < 0) percent = 0;
  if (percent > 100) percent = 100;
  playerProgress.value = percent;
}

function stopTrackProgressTimer() {
  if (trackProgressTimer) {
    clearInterval(trackProgressTimer);
    trackProgressTimer = null;
  }
}

function startTrackProgressTimer(immediateTick) {
  stopTrackProgressTimer();

  var total = Number(currentTrackDurationSeconds.value || 0);
  if (!currentTrackVideoId.value || isTrackPaused.value || !Number.isFinite(total) || total <= 0) {
    return;
  }

  if (immediateTick && trackElapsedSeconds.value < total) {
    trackElapsedSeconds.value += 1;
    syncPlayerProgressFromElapsed();
  }

  trackProgressTimer = setInterval(function () {
    var maxSeconds = Number(currentTrackDurationSeconds.value || 0);
    if (!Number.isFinite(maxSeconds) || maxSeconds <= 0) {
      stopTrackProgressTimer();
      return;
    }
    if (trackElapsedSeconds.value >= maxSeconds) {
      trackElapsedSeconds.value = maxSeconds;
      syncPlayerProgressFromElapsed();
      isTrackPaused.value = true;
      stopTrackProgressTimer();
      return;
    }
    trackElapsedSeconds.value += 1;
    syncPlayerProgressFromElapsed();
  }, 1000);
}

function updateProgressFromSlider(rawValue) {
  var nextProgress = Number(rawValue);
  if (!Number.isFinite(nextProgress)) {
    nextProgress = 0;
  }
  if (nextProgress < 0) nextProgress = 0;
  if (nextProgress > 100) nextProgress = 100;
  playerProgress.value = nextProgress;

  var total = Number(currentTrackDurationSeconds.value || 0);
  if (!Number.isFinite(total) || total <= 0) {
    trackElapsedSeconds.value = 0;
    return;
  }
  var targetSeconds = Math.floor((nextProgress / 100) * total);
  trackElapsedSeconds.value = targetSeconds;

  if (youtubeIframeEl.value && youtubeIframeEl.value.contentWindow) {
    try {
      youtubeIframeEl.value.contentWindow.postMessage(JSON.stringify({
        event: "command",
        func: "seekTo",
        args: [targetSeconds, true]
      }), "*");
    } catch (e) {}
  }
}

async function runVideoSearch(query) {
  if (searchAbortController) {
    try {
      searchAbortController.abort();
    } catch (e) {}
  }

  var trimmed = String(query || "").trim();
  if (trimmed.length < 2) {
    searchResults.value = [];
    searchLoading.value = false;
    searchError.value = "";
    return;
  }

  searchLoading.value = true;
  searchError.value = "";
  searchAbortController = new AbortController();

  try {
    var response = await authFetch("/api/external/videos?q=" + encodeURIComponent(trimmed), {
      signal: searchAbortController.signal
    });
    var data = await response.json();

    if (!response.ok || !data || !data.ok || !Array.isArray(data.items)) {
      searchResults.value = [];
      searchError.value = data && data.error ? String(data.error) : "Error cercant videos.";
      return;
    }

    searchResults.value = data.items;
  } catch (e) {
    if (e && e.name === "AbortError") {
      return;
    }
    searchResults.value = [];
    searchError.value = "No s'ha pogut completar la cerca.";
  } finally {
    searchLoading.value = false;
  }
}

function selectSearchResult(index) {
  var pickedIndex = Number(index);
  if (!Number.isFinite(pickedIndex) || pickedIndex < 0 || pickedIndex >= searchResults.value.length) {
    return;
  }

  var pickedItem = searchResults.value[pickedIndex];
  musicSearchResults.value = searchResults.value.slice();
  currentTrackIndex.value = pickedIndex;
  currentTrackName.value = pickedItem && pickedItem.titol ? pickedItem.titol : "Video";
  currentTrackVideoId.value = pickedItem && pickedItem.api_id ? String(pickedItem.api_id) : "";
  currentTrackDurationSeconds.value = parseDurationToSeconds(pickedItem && pickedItem.duracio ? pickedItem.duracio : "");
  trackElapsedSeconds.value = 0;
  isTrackPaused.value = false;
  playerProgress.value = 0;
  startTrackProgressTimer(true);
  closeSearch();
}

watch(
  searchQuery,
  function (newValue) {
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }
    searchDebounceTimer = setTimeout(function () {
      runVideoSearch(newValue);
    }, 250);
  },
  { immediate: false }
);

function handlePrevTrack() {
  if (!musicSearchResults.value.length) {
    playerProgress.value = 0;
    return;
  }
  var nextIndex = currentTrackIndex.value - 1;
  if (nextIndex < 0) {
    nextIndex = musicSearchResults.value.length - 1;
  }
  currentTrackIndex.value = nextIndex;
  var item = musicSearchResults.value[nextIndex];
  currentTrackName.value = item && item.titol ? item.titol : "Video";
  currentTrackVideoId.value = item && item.api_id ? String(item.api_id) : "";
  currentTrackDurationSeconds.value = parseDurationToSeconds(item && item.duracio ? item.duracio : "");
  trackElapsedSeconds.value = 0;
  isTrackPaused.value = false;
  playerProgress.value = 0;
  startTrackProgressTimer(true);
}

function handleNextTrack() {
  if (!musicSearchResults.value.length) {
    playerProgress.value = 0;
    return;
  }
  var nextIndex = currentTrackIndex.value + 1;
  if (nextIndex >= musicSearchResults.value.length) {
    nextIndex = 0;
  }
  currentTrackIndex.value = nextIndex;
  var item = musicSearchResults.value[nextIndex];
  currentTrackName.value = item && item.titol ? item.titol : "Video";
  currentTrackVideoId.value = item && item.api_id ? String(item.api_id) : "";
  currentTrackDurationSeconds.value = parseDurationToSeconds(item && item.duracio ? item.duracio : "");
  trackElapsedSeconds.value = 0;
  isTrackPaused.value = false;
  playerProgress.value = 0;
  startTrackProgressTimer(true);
}

watch(
  isTrackPaused,
  function (paused) {
    if (paused) {
      stopTrackProgressTimer();
      return;
    }
    startTrackProgressTimer(false);
  }
);

watch(
  currentTrackVideoId,
  function (videoId) {
    if (!videoId) {
      stopTrackProgressTimer();
      trackElapsedSeconds.value = 0;
      playerProgress.value = 0;
    }
  }
);

function completeHabitIfReached() {
  if (!habit.value || focusGoalSeconds.value <= 0) return;
  if (accumulatedWorkSeconds.value < focusGoalSeconds.value) return;

  var sent = emitFocusUpdate("work_finished", false);
  if (!sent) {
    // Fallback: si no hi ha socket, completem l'hàbit per API perquè
    // el flow no quedi trencat. (Sense metadades de focus al calendari.)
    gameStore.completarHabit(habit.value.id, null).catch(function () {});
  }
  markCompleted();
  setTimeout(function () {
    navigateTo("/home");
  }, 500);
}

function onVisibilityChange() {
  if (document.visibilityState === "hidden" && isRunning.value) {
    var nuxt = useNuxtApp();
    nuxt.$swal.fire({
      icon: "warning",
      title: "Ei!",
      text: "La teva mascota s'ha posat trista: torna a la sessió de focus."
    });
  }
}

function toggleTimer() {
  if (isRunning.value) {
    pause();
    return;
  }
  startOrResume(function () {
    incrementDailyFocusSets();
    var nuxt = useNuxtApp();
    nuxt.$swal.fire({
      icon: "success",
      title: "Bon treball!",
      text: "La teva mascota està celebrant el teu esforç 🎉"
    });
    completeHabitIfReached();
  });
}

function handleManualExit() {
  var sent = emitFocusUpdate("manual_exit", true);
  if (!sent && accumulatedWorkSeconds.value >= focusGoalSeconds.value) {
    // Fallback per mantenir el flow quan no hi ha socket.
    gameStore.completarHabit(habit.value.id, null).catch(function () {});
  }
  saveFocusSnapshot("manual_exit");
  exitSession();
  navigateTo("/home");
}

onMounted(async function () {
  if (!habitStore.habits || habitStore.habits.length === 0) {
    await gameStore.carregarDadesHome();
  }
  resolveHabit();
  if (!habit.value) {
    navigateTo("/home");
    return;
  }
  loadDailyFocusSets();

  // GET via API (authFetch) per tenir un "completat avui" coherent
  // abans de decidir l'eligibilitat d'entrada a Focus Mode.
  try {
    await gameStore.obtenirProgresHabits();
  } catch (e) {}
  await loadFocusSessionContext();
  if (!selectedMode.value) {
    applyMode("25_5");
  }

  if (gameStore.habitProgress && gameStore.habitProgress[habit.value.id] && gameStore.habitProgress[habit.value.id].completed_today) {
    var nuxt = useNuxtApp();
    await nuxt.$swal.fire({ icon: "info", title: "Hàbit completat", text: "Aquest hàbit ja està completat avui." });
    navigateTo("/home");
    return;
  }
  document.addEventListener("visibilitychange", onVisibilityChange);
  document.addEventListener("keydown", onKeydown);
});

onBeforeUnmount(function () {
  saveFocusSnapshot(sessionState.value === "completed" ? "completed" : "leave_page");
  document.removeEventListener("visibilitychange", onVisibilityChange);
  document.removeEventListener("keydown", onKeydown);
  stopTrackProgressTimer();
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer);
  }
  if (searchAbortController) {
    try {
      searchAbortController.abort();
    } catch (e) {}
  }
});
</script>

<style src="~/assets/css/focus-mode-mobile.css"></style>
