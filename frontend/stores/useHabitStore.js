import { defineStore } from 'pinia';
import { io } from 'socket.io-client';

// A. Configurar la connexió amb el servidor de sockets (Backend Node.js)
var socket = io('http://localhost:3001', {
  transports: ['websocket', 'polling']
});

// B. Definir el store d'hàbits usant sintaxi ES5 estricte
export var useHabitStore = defineStore('habit', {
  state: function () {
    return {
      llistaHabits: [],
      ultimSnapshot: null, // Per al rollback en cas d'error del servidor
      carregant: false,
      error: null
    };
  },
  actions: {
    // 1. Inicialitzar els escoltadors de sockets per rebre actualitzacions
    inicialitzarEscoltadorsSocket: function () {
      var self = this;
      console.log('Iniciant inicialitzarEscoltadorsSocket...');

      // Escoltant errors de validació del servidor
      socket.on('validation_error', function (dades) {
        console.error('Error de validació rebut:', dades);
        if (dades.message) {
          self.error = dades.message;
        } else {
          self.error = 'Error de validació';
        }
      });

      // Escoltant actualitzacions d'experiència (XP)
      socket.on('update_xp', function (dades) {
        console.log('Actualització d\'XP rebuda:', dades);
        self.actualitzarEstatLocal(dades);
      });

      // Confirmació d'accions realitzades pel servidor
      socket.on('habit_action_confirmed', function (dades) {
        console.log('Acció confirmada pel servidor:', dades);
        if (dades.success) {
          // Si tot ha anat bé, esborrem el snapshot de seguretat
          self.ultimSnapshot = null;
          console.log('Operació confirmada amb èxit');
        } else {
          // En cas d'error, ROOLBACK de l'estat
          console.error('L\'operació ha fallat al servidor. Aplicant Rollback.');
          if (self.ultimSnapshot) {
            self.llistaHabits = self.ultimSnapshot;
            self.ultimSnapshot = null;
          }
          self.error = "Error al sincronitzar amb el servidor";
        }
      });
    },

    // 2. Marcar un hàbit com a completat o desmarcar-lo (Toggle)
    marcarHabit: function (idHabit) {
      console.log('Marcant hàbit:', idHabit);
      var snapshot = JSON.parse(JSON.stringify(this.llistaHabits));
      var self = this;
      var habitTrobat = false;

      try {
        // A. Cercar l'hàbit i aplicar mutació optimista
        for (var i = 0; i < self.llistaHabits.length; i++) {
          if (self.llistaHabits[i].id === idHabit) {
            self.llistaHabits[i].completat = !self.llistaHabits[i].completat;
            habitTrobat = true;
            break;
          }
        }

        if (!habitTrobat) {
          throw new Error('Hàbit no trobat');
        }

        // B. Notificar al backend de l'acció
        socket.emit('habit_action', {
          action: 'TOGGLE',
          habit_id: idHabit,
          timestamp: Date.now()
        });

      } catch (e) {
        // C. Restauració en cas d'error local
        console.error('Error local al marcar hàbit:', e);
        self.error = e.message;
        self.llistaHabits = snapshot;
      }
    },

    // 3. Afegir un nou hàbit a la llista
    afegirHabit: function (nouHabit) {
      console.log('Afegint nou hàbit:', nouHabit);
      var snapshot = JSON.parse(JSON.stringify(this.llistaHabits));
      var self = this;

      try {
        // A. Mutació Optimista amb ID temporal
        var habitTemporal = JSON.parse(JSON.stringify(nouHabit));
        habitTemporal.id = 'temp-' + Date.now();
        self.llistaHabits.push(habitTemporal);

        // B. Enviem l'acció de creació al backend
        socket.emit('habit_action', {
          action: 'CREATE',
          habit_data: nouHabit
        });
        console.log('Esdeveniment CREATE enviat al socket');

      } catch (e) {
        // C. Rollback en cas d'error
        console.error('Error a l\'afegir hàbit:', e);
        self.llistaHabits = snapshot;
      }
    },

    // 4. Eliminar un hàbit existent
    eliminarHabit: function (idHabit) {
      console.log('Eliminant hàbit:', idHabit);

      // A. Crear snapshot per seguretat
      var snapshot = JSON.parse(JSON.stringify(this.llistaHabits));
      this.ultimSnapshot = snapshot;
      var self = this;
      var novaLlista = [];

      try {
        // B. Mutació Optimista: Filtrar la llista localment
        for (var i = 0; i < self.llistaHabits.length; i++) {
          if (self.llistaHabits[i].id !== idHabit) {
            novaLlista.push(self.llistaHabits[i]);
          }
        }
        self.llistaHabits = novaLlista;

        // C. Notificar al backend
        socket.emit('habit_action', {
          action: 'DELETE',
          habit_id: idHabit
        });
        console.log('Esdeveniment DELETE enviat al socket');

      } catch (e) {
        // D. Gestió d'error i rollback
        console.error('Error local al eliminar l\'hàbit:', e);
        self.error = 'No s\'ha pogut eliminar l\'hàbit localment';
        self.llistaHabits = snapshot;
        self.ultimSnapshot = null;
      }
    },

    // 5. Sincronitzar l'estat local amb les dades reals del servidor
    actualitzarEstatLocal: function (dades) {
      var self = this;
      if (dades) {
        if (dades.habits) {
          self.llistaHabits = dades.habits;
        }
      }
    }
  }
});
