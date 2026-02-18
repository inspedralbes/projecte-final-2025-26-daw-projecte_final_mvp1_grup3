
import { defineStore } from 'pinia';

export const useHabitStore = defineStore('habitStore', {
  state: function () {
    return {
      habits: [],
      loading: false,
      error: null
    };
  },
  actions: {
    fetchHabits: async function () {
      this.loading = true;
      // Simulacio de carrega inicial
      var self = this;
      setTimeout(function () {
        self.habits = [
          { id: 1, title: 'Beure 2L aigua', completed: false },
          { id: 2, title: 'Fer 30 minuts exercici', completed: true },
          { id: 3, title: 'Llegir 10 pagines', completed: false }
        ];
        self.loading = false;
      }, 500);
    },

    addHabit: async function (title) {
      // 1. Snapshot de l'estat actual per si falla
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      
      // 2. Optimistic Update: Afegim directament
      var newHabit = {
        id: Date.now(), // ID temporal
        title: title,
        completed: false
      };
      this.habits.push(newHabit);

      // 3. Simulacio de peticio al backend
      try {
        await new Promise(function(resolve, reject) {
          setTimeout(function() {
            // Simulem un 20% de probabilitat d'error
            if (Math.random() < 0.2) {
              reject(new Error('Error al servidor'));
            } else {
              resolve();
            }
          }, 1000);
        });
      } catch (error) {
        // 4. Rollback en cas d'error
        console.error('Error afegint habit, fent rollback...', error);
        this.habits = snapshot;
        alert('Error guardant l\'hàbit. S\'han desfet els canvis.');
      }
    },

    deleteHabit: async function (id) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      
      // Optimistic delete
      var index = -1;
      for (var i = 0; i < this.habits.length; i++) {
        if (this.habits[i].id === id) {
          index = i;
          break;
        }
      }
      
      if (index > -1) {
        this.habits.splice(index, 1);
      }

      try {
        await new Promise(function(resolve, reject) {
          setTimeout(function() {
             if (Math.random() < 0.2) reject(new Error('Simulated error'));
             else resolve();
          }, 800);
        });
      } catch (e) {
        this.habits = snapshot;
        alert('Error eliminant. Rollback executat.');
      }
    },

    toggleHabit: async function (id) {
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      
      // Optimistic toggle
      for (var i = 0; i < this.habits.length; i++) {
        if (this.habits[i].id === id) {
          this.habits[i].completed = !this.habits[i].completed;
          break;
        }
      }

      try {
        await new Promise(function(resolve, reject) {
          setTimeout(function() {
             if (Math.random() < 0.2) reject(new Error('Simulated error'));
             else resolve();
          }, 600);
        });
      } catch (e) {
        this.habits = snapshot;
        alert('Error actualitzant estat. Rollback executat.');
      }
    }
  }
});
