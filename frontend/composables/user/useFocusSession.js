/**
 * Modul JavaScript ES5: useFocusSession.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { ref, computed, onBeforeUnmount } from "vue";

var MODES = {
  "25_5": { label: "50/10", workMinutes: 50, restMinutes: 10 },
  "50_10": { label: "25/5", workMinutes: 25, restMinutes: 5 }
};

export function useFocusSession() {
  var selectedMode = ref("");
  var phase = ref("work");
  var sessionState = ref("idle");
  var remainingSeconds = ref(0);
  var accumulatedWorkSeconds = ref(0);
  var timer = null;

  var modeConfig = computed(function () {
    return MODES[selectedMode.value] || null;
  });

  var canStart = computed(function () {
    return !!modeConfig.value && (sessionState.value === "idle" || sessionState.value === "paused_work" || sessionState.value === "paused_rest");
  });

  var isRunning = computed(function () {
    return sessionState.value === "running_work" || sessionState.value === "running_rest";
  });

  var remainingTimeLabel = computed(function () {
    var total = Math.max(0, remainingSeconds.value);
    var minutes = Math.floor(total / 60);
    var seconds = total % 60;
    return String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0");
  });

  function applyMode(modeKey) {
    if (!MODES[modeKey] || isRunning.value) {
      return;
    }
    selectedMode.value = modeKey;
    phase.value = "work";
    sessionState.value = "idle";
    remainingSeconds.value = MODES[modeKey].workMinutes * 60;
  }

  function clearTick() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function transitionPhase() {
    if (!modeConfig.value) return;
    if (phase.value === "work") {
      phase.value = "rest";
      remainingSeconds.value = modeConfig.value.restMinutes * 60;
      sessionState.value = "running_rest";
      return "work_finished";
    }
    phase.value = "work";
    remainingSeconds.value = modeConfig.value.workMinutes * 60;
    sessionState.value = "running_work";
    return "rest_finished";
  }

  function startOrResume(onWorkFinished) {
    if (!modeConfig.value) return;
    if (remainingSeconds.value <= 0) {
      remainingSeconds.value = phase.value === "work" ? modeConfig.value.workMinutes * 60 : modeConfig.value.restMinutes * 60;
    }
    sessionState.value = phase.value === "work" ? "running_work" : "running_rest";
    clearTick();
    timer = setInterval(function () {
      if (remainingSeconds.value > 0) {
        remainingSeconds.value -= 1;
        if (phase.value === "work") {
          accumulatedWorkSeconds.value += 1;
        }
        return;
      }
      var eventName = transitionPhase();
      if (eventName === "work_finished" && typeof onWorkFinished === "function") {
        onWorkFinished();
      }
    }, 1000);
  }

  function pause() {
    if (!isRunning.value) return;
    clearTick();
    sessionState.value = phase.value === "work" ? "paused_work" : "paused_rest";
  }

  function exitSession() {
    clearTick();
    sessionState.value = "exited";
  }

  function markCompleted() {
    clearTick();
    sessionState.value = "completed";
  }

  onBeforeUnmount(function () {
    clearTick();
  });

  return {
    MODES: MODES,
    selectedMode: selectedMode,
    phase: phase,
    sessionState: sessionState,
    remainingSeconds: remainingSeconds,
    accumulatedWorkSeconds: accumulatedWorkSeconds,
    canStart: canStart,
    isRunning: isRunning,
    remainingTimeLabel: remainingTimeLabel,
    modeConfig: modeConfig,
    applyMode: applyMode,
    startOrResume: startOrResume,
    pause: pause,
    exitSession: exitSession,
    markCompleted: markCompleted
  };
}
