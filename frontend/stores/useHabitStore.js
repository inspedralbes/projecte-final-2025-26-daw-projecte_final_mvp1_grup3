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
    mapHabitFromApi: function (habit) {
      return {
        id: habit.id,
        name: habit.titol || "Sin nombre",
        frequency: habit.frequencia_tipus || "",
        reminder: habit.reminder || "",
        icon: habit.icon || "📝",
        color: habit.color || "#10B981",
        dificultat: habit.dificultat || null,
        dies_setmana: habit.dies_setmana || "",
        objectiu_vegades: habit.objectiu_vegades || 1,
        usuari_id: habit.usuari_id || 1,
        plantilla_id: habit.plantilla_id || null,
      };
    },
    setHabitsFromApi: function (habits) {
      var mapped = [];
      for (var i = 0; i < habits.length; i++) {
        mapped.push(this.mapHabitFromApi(habits[i]));
      }
      this.habits = mapped;
    },
    fetchHabitsFromApi: async function () {
      this.loading = true;
      this.error = null;
      try {
        var response = await fetch("http://localhost:8000/api/habits");
        if (!response.ok) {
          throw new Error("Error al obtener hábitos: " + response.status);
        }
        var rawData = await response.json();
        var habits = Array.isArray(rawData) ? rawData : rawData.data || [];
        this.setHabitsFromApi(habits);
        return this.habits;
      } catch (e) {
        this.error = e.message;
        this.habits = [];
        return [];
      } finally {
        this.loading = false;
      }
    },
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
    upsertHabit: function (habit) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var found = false;
      try {
        for (var i = 0; i < this.habits.length; i++) {
          if (this.habits[i].id === habit.id) {
            this.habits[i] = habit;
            found = true;
            break;
          }
        }
        if (!found) {
          this.habits.push(habit);
        }
      } catch (e) {
        this.error = e.message;
        this.habits = snapshot;
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
