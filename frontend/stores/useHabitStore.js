import { defineStore } from 'pinia';
import { io } from 'socket.io-client';

// Connexió amb el Backend Node.js (Signaling Server)
// Ajustar la URL segons el teu entorn (ex: http://localhost:3001 o ruta relativa si hi ha proxy)
var socket = io('http://localhost:3001', {
  transports: ['websocket', 'polling']
});
import { io } from 'socket.io-client';

// Connexió amb el Backend Node.js
var socket = io('http://localhost:3001', {
  transports: ['websocket', 'polling']
});

export const useHabitStore = defineStore('habit', {
  state: function () {
    return {
      llista_habits: [],
      lastSnapshot: null, // Per al rollback en cas d'error del servidor
      loading: false,
      error: null
    };
  },
  actions: {
    initSocketListeners: function () {
      var self = this;
      console.log('Iniciant initSocketListeners...');

      socket.on('validation_error', function (data) {
        console.error('Error de validació rebut:', data);
        self.error = data.message || 'Error de validació';
      });

      socket.on('update_xp', function (data) {
        console.log('Update XP rebut:', data);
        self.actualitzarEstatLocal(data);
      });

      socket.on('habit_action_confirmed', function (data) {
        console.log('Acció confirmada pel servidor:', data);
        if (data.success) {
          // Si tot ha anat bé, esborrem el snapshot de seguretat
          self.lastSnapshot = null;
          console.log('Operació confirmada amb èxit');
        } else {
          // B. En cas d'error, ROOLBACK segons AgentFluxGlobal.md
          console.error('L\'operació ha fallat al servidor. Aplicant Rollback.');
          if (self.lastSnapshot) {
            self.habits = self.lastSnapshot;
            self.lastSnapshot = null;
          }
          self.error = "Error al sincronitzar amb el servidor";
        }
      });
    },

    marcarHabit: function (habitId) {
      console.log('Marcant hàbit:', habitId);
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var self = this;
      var habitTrobat = false;

      try {
        for (var i = 0; i < self.llista_habits.length; i++) {
          if (self.llista_habits[i].id === habitId) {
            // Canviar l'estat de completat (toggle)
            self.llista_habits[i].completat = !self.llista_habits[i].completat;
            habitTrobat = true;
            break;
          }
        }

        if (!habitTrobat) throw new Error('Hàbit no trobat');

        socket.emit('habit_action', {
          action: 'TOGGLE',
          habit_id: habitId,
          timestamp: Date.now()
        });

      } catch (e) {
        // 4. Restauració (Rollback) en cas d'error local
        console.error('Error local al marcar hàbit:', e);
        self.error = e.message;
        self.llista_habits = snapshot;
      }
    },

    addHabit: function (newHabit) {
      console.log('Afegint nou hàbit (addHabit inicialitzat):', newHabit);
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      var self = this;

      try {
        // Mutació Optimista
        var tempHabit = JSON.parse(JSON.stringify(newHabit));
        tempHabit.id = 'temp-' + Date.now();
        self.habits.push(tempHabit);

        // Enviem al backend
        socket.emit('habit_action', {
          action: 'CREATE',
          habit_data: newHabit
        });
        console.log('Event CREATE enviat al socket');

      } catch (e) {
        console.error('Error al afegir hàbit:', e);
        self.habits = snapshot;
      }
    },

    deleteHabit: function (habitId) {
      console.log('Borrant hàbit:', habitId);
      // A. Snapshot: Guardem l'estat actual abans de modificar
      var snapshot = JSON.parse(JSON.stringify(this.habits));
      this.lastSnapshot = snapshot;
      var self = this;
      var novaLlista = [];

      try {
        // B. Mutació Optimista: Eliminem localment de seguida
        for (var i = 0; i < self.habits.length; i++) {
          if (self.habits[i].id !== habitId) {
            novaLlista.push(self.habits[i]);
          }
        }
        self.habits = novaLlista;

        // C. Bridge: Enviem l'event al backend Node.js
        socket.emit('habit_action', {
          action: 'DELETE',
          habit_id: habitId
        });
        console.log('Event DELETE enviat al socket');

      } catch (e) {
        console.error('Error local al borrar hàbit:', e);
        self.error = 'No s\'ha pogut borrar l\'hàbit localment';
        self.habits = snapshot;
        self.lastSnapshot = null;
      }
    },

    actualitzarEstatLocal: function (dades) {
      var self = this;
      if (dades && dades.habits) {
        self.habits = dades.habits;
      }
    }
  }
});
