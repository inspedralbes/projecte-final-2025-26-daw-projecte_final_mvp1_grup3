import { defineStore } from "pinia";

export const useHabitStore = defineStore("habit", {
  state: function () {
    return {
      habits: [],
      loading: false,
      error: null,
    };
  },
  actions: {
    addHabit: function (habit) {
      // Snapshot logic for rollback (simulated here as we push to local state first)
      var snapshot = JSON.parse(JSON.stringify(this.habits));

      try {
        // Assign a temporary ID if one doesn't exist
        if (!habit.id) {
          habit.id = Date.now();
        }
        this.habits.push(habit);

        // Here we would typically make an API call
        // If it fails, we revert:
        // this.habits = snapshot;
      } catch (e) {
        this.error = e.message;
        this.habits = snapshot;
      }
    },
    updateHabit: function (updatedHabit) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var self = this; // Capture 'this' for use in inner scopes if needed

      try {
        for (var i = 0; i < self.habits.length; i++) {
          if (self.habits[i].id === updatedHabit.id) {
            self.habits[i] = updatedHabit;
            break;
          }
        }
      } catch (e) {
        self.error = e.message;
        self.habits = snapshot;
      }
    },
    removeHabit: function (habitId) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var self = this;

      try {
        var newHabits = [];
        for (var i = 0; i < self.habits.length; i++) {
          if (self.habits[i].id !== habitId) {
            newHabits.push(self.habits[i]);
          }
        }
        self.habits = newHabits;
      } catch (e) {
        self.error = e.message;
        self.habits = snapshot;
      }
    },
  },
});
