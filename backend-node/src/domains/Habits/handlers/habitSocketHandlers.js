'use strict';


/**
 * Modul JavaScript ES5: habitSocketHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var habitQueuePublisher = require('../publishers/habitQueuePublisher');
var socketUserId = require('../../../shared/socketUserId');
var socketRooms = require('../../../shared/socketRooms');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Registra listeners d'hàbits (CUD via Redis).
 *
 * @param {object} io
 * @param {object} socket
 */
function register(io, socket) {
  socket.on('habit_action', async function (payload) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('habit_action: usuari no autenticat');
        socketUserId.emitirErrorAuthHabit(socket, payload);
        return;
      }
      socketRooms.joinUserRoom(socket, userId);
      await habitQueuePublisher.pushToLaravel(payload.action, userId, payload);
    } catch (error) {
      console.error('Error gestionant habit_action:', error);
    }
  });

  socket.on('habit_completed', async function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('habit_completed: usuari no autenticat');
        return;
      }
      socketRooms.joinUserRoom(socket, userId);
      var payload = { habit_id: data.habit_id, data: data.data };
      await habitQueuePublisher.pushToLaravel('TOGGLE', userId, payload);
    } catch (error) {
      console.error('Error gestionant habit_completed:', error);
    }
  });

  socket.on('habit_progress', async function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('habit_progress: usuari no autenticat');
        return;
      }
      socketRooms.joinUserRoom(socket, userId);
      var payload = { habit_id: data.habit_id, valor: data.valor };
      await habitQueuePublisher.pushToLaravel('PROGRESS', userId, payload);
    } catch (error) {
      console.error('Error gestionant habit_progress:', error);
    }
  });

  socket.on('habit_complete', async function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('habit_complete: usuari no autenticat');
        return;
      }
      socketRooms.joinUserRoom(socket, userId);
      var payload = { habit_id: data.habit_id, data: data.data };
      await habitQueuePublisher.pushToLaravel('COMPLETE', userId, payload);
    } catch (error) {
      console.error('Error gestionant habit_complete:', error);
    }
  });

  socket.on('habit_focus_update', async function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('habit_focus_update: usuari no autenticat');
        return;
      }
      socketRooms.joinUserRoom(socket, userId);
      var focusMode = null;
      var focusMinutes = 0;
      var focusEvent = 'update';
      var extraData = null;
      if (data) {
        if (data.mode) {
          focusMode = data.mode;
        }
        if (data.minutes) {
          focusMinutes = data.minutes;
        }
        if (data.event) {
          focusEvent = data.event;
        }
        if (data.data) {
          extraData = data.data;
        }
      }
      var payload = {
        habit_id: data && data.habit_id ? data.habit_id : null,
        focus_mode: focusMode,
        focus_minutes: focusMinutes,
        focus_event: focusEvent,
        data: extraData
      };
      await habitQueuePublisher.pushToLaravel('FOCUS_UPDATE', userId, payload);
    } catch (error) {
      console.error('Error gestionant habit_focus_update:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
