import { defineStore } from 'pinia';
import { io } from 'socket.io-client';

// Connexió amb el Backend Node.js (Signaling Server)
// Ajustar la URL segons el teu entorn (ex: http://localhost:3001 o ruta relativa si hi ha proxy)
var socket = io('http://localhost:3001', {
  transports: ['websocket', 'polling']
});

export const useHabitStore = defineStore('habit', {
  state: function () {
    return {
      llista_habits: [],
      loading: false,
      error: null
    };
  },
  actions: {
    // Inicialitzar els listeners del socket per rebre actualitzacions del servidor
    initSocketListeners: function () {
      var self = this;
      
      // Escolta per errors de validació des de Node.js
      socket.on('validation_error', function (data) {
        console.error('Error de validació rebut:', data);
        // Aquí podríem revertir l'estat si tinguéssim accés a la snapshot global o específica
        // Per simplicitat, podríem recarregar o notificar l'usuari
        self.error = data.message || 'Error de validació';
      });

      // Escolta per actualitzacions confirmades des de Laravel via Node (Redis Pub/Sub -> Socket)
      socket.on('update_xp', function (data) {
        self.actualitzarEstatLocal(data);
      });
    },

    marcarHabit: function (habitId) {
      // 1. Snapshot: Crear còpia de seguretat per Rollback
      var snapshot = JSON.parse(JSON.stringify(this.llista_habits));
      var self = this;
      var habitTrobat = false;

      // 2. Mutació Optimista: Actualitzar la UI immediatament
      try {
        for (var i = 0; i < self.llista_habits.length; i++) {
          if (self.llista_habits[i].id === habitId) {
            // Canviar l'estat de completat (toggle)
            self.llista_habits[i].completat = !self.llista_habits[i].completat;
            habitTrobat = true;
            break;
          }
        }

        if (!habitTrobat) {
          throw new Error('Hàbit no trobat');
        }

        // 3. Comunicació: Enviar acció al Backend Node
        // Aquest esdeveniment enviarà la tasca a la Redis Queue per a Laravel
        socket.emit('mark_habit', { 
            habit_id: habitId, 
            timestamp: Date.now() 
            // En un entorn real, s'enviaria també el JWT token si no s'ha fet al handshake
        });

      } catch (e) {
        // 4. Restauració (Rollback) en cas d'error local
        console.error('Error local al marcar hàbit:', e);
        self.error = e.message;
        self.llista_habits = snapshot;
      }
    },

    actualitzarEstatLocal: function (dades) {
      // Aquesta funció es crida quan arriba la confirmació del servidor (Socket)
      // dades pot contenir el nou XP, monedes, o l'estat final de l'hàbit
      var self = this;
      
      // Exemple: Si rebem la llista actualitzada o canvis específics
      // En aquest cas, suposem que rebem informació per actualitzar l'hàbit o l'usuari
      // Si dades inclou 'habits', actualitzem la llista
      if (dades && dades.habits) {
        self.llista_habits = dades.habits;
      }
      
      // Nota: L'actualització d'XP i Monedes aniria al useUserStore,
      // però si la resposta inclou canvis en l'hàbit, els apliquem aquí.
    }
  }
});
