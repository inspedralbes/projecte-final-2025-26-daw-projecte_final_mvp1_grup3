/**
 * Modul JavaScript ES5: useSocketUiCallbacks.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { defineStore } from 'pinia';

/**
 * Registre de callbacks UI per esdeveniments socket (evita duplicar listeners per pàgina).
 */
export var useSocketUiCallbacks = defineStore('socketUiCallbacks', {
  state: function () {
    return {
      habitConfirmedHandlers: [],
      streakBrokenHandlers: [],
      levelUpHandlers: [],
      rouletteResultHandlers: [],
      habitCompleteAlertHandlers: [],
      missionCompleteHandlers: [],
      habitErrorHandlers: [],
      plantillaConfirmedHandlers: []
    };
  },
  actions: {
    registrarHabitConfirmed: function (fn) {
      if (typeof fn === 'function') {
        this.habitConfirmedHandlers.push(fn);
      }
    },
    eliminarHabitConfirmed: function (fn) {
      var i;
      var nova = [];
      for (i = 0; i < this.habitConfirmedHandlers.length; i++) {
        if (this.habitConfirmedHandlers[i] !== fn) {
          nova.push(this.habitConfirmedHandlers[i]);
        }
      }
      this.habitConfirmedHandlers = nova;
    },
    registrarStreakBroken: function (fn) {
      if (typeof fn === 'function') {
        this.streakBrokenHandlers.push(fn);
      }
    },
    registrarLevelUp: function (fn) {
      if (typeof fn === 'function') {
        this.levelUpHandlers.push(fn);
      }
    },
    registrarRouletteResult: function (fn) {
      if (typeof fn === 'function') {
        this.rouletteResultHandlers.push(fn);
      }
    },
    eliminarRouletteResult: function (fn) {
      var i;
      var nova = [];
      for (i = 0; i < this.rouletteResultHandlers.length; i++) {
        if (this.rouletteResultHandlers[i] !== fn) {
          nova.push(this.rouletteResultHandlers[i]);
        }
      }
      this.rouletteResultHandlers = nova;
    },
    registrarHabitCompleteAlert: function (fn) {
      if (typeof fn === 'function') {
        this.habitCompleteAlertHandlers.push(fn);
      }
    },
    registrarMissionComplete: function (fn) {
      if (typeof fn === 'function') {
        this.missionCompleteHandlers.push(fn);
      }
    },
    registrarHabitError: function (fn) {
      if (typeof fn === 'function') {
        this.habitErrorHandlers.push(fn);
      }
    },
    registrarPlantillaConfirmed: function (fn) {
      if (typeof fn === 'function') {
        this.plantillaConfirmedHandlers.push(fn);
      }
    },
    eliminarPlantillaConfirmed: function (fn) {
      var i;
      var nova = [];
      for (i = 0; i < this.plantillaConfirmedHandlers.length; i++) {
        if (this.plantillaConfirmedHandlers[i] !== fn) {
          nova.push(this.plantillaConfirmedHandlers[i]);
        }
      }
      this.plantillaConfirmedHandlers = nova;
    },
    invocarHabitConfirmed: function (payload) {
      var i;
      for (i = 0; i < this.habitConfirmedHandlers.length; i++) {
        this.habitConfirmedHandlers[i](payload);
      }
    },
    invocarStreakBroken: function (payload) {
      var i;
      for (i = 0; i < this.streakBrokenHandlers.length; i++) {
        this.streakBrokenHandlers[i](payload);
      }
    },
    invocarLevelUp: function (data) {
      var i;
      for (i = 0; i < this.levelUpHandlers.length; i++) {
        this.levelUpHandlers[i](data);
      }
    },
    invocarRouletteResult: function (data) {
      var i;
      for (i = 0; i < this.rouletteResultHandlers.length; i++) {
        this.rouletteResultHandlers[i](data);
      }
    },
    invocarHabitCompleteAlert: function () {
      var i;
      for (i = 0; i < this.habitCompleteAlertHandlers.length; i++) {
        this.habitCompleteAlertHandlers[i]();
      }
    },
    invocarMissionComplete: function (data) {
      var i;
      for (i = 0; i < this.missionCompleteHandlers.length; i++) {
        this.missionCompleteHandlers[i](data);
      }
    },
    invocarHabitError: function (message) {
      var i;
      for (i = 0; i < this.habitErrorHandlers.length; i++) {
        this.habitErrorHandlers[i](message);
      }
    },
    invocarPlantillaConfirmed: function (payload) {
      var i;
      for (i = 0; i < this.plantillaConfirmedHandlers.length; i++) {
        this.plantillaConfirmedHandlers[i](payload);
      }
    }
  }
});
