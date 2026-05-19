'use strict';


/**
 * Modul JavaScript ES5: chatSocketHandlers.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var socketUserId = require('../../../shared/socketUserId');
var socketRooms = require('../../../shared/socketRooms');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('private_message', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('private_message: usuari no autenticat');
        return;
      }
      var receiverId = data.receiver_id;
      if (receiverId) {
        var roomName = socketRooms.nomSalaXatPrivat(userId, receiverId);
        io.to('chat_' + roomName).emit('new_private_message', {
          sender_id: userId,
          receiver_id: receiverId,
          message: data.message,
          created_at: data.created_at
        });
        console.log('Missatge privat enviat a chat_' + roomName);
      }
    } catch (error) {
      console.error('Error gestionant private_message:', error);
    }
  });

  socket.on('join_private_chat', async function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('join_private_chat: usuari no autenticat');
        socket.emit('chat_error', { error: 'No autenticat' });
        return;
      }
      var friendId = data.friend_id;
      if (!friendId) {
        socket.emit('chat_error', { error: 'friend_id requerit' });
        return;
      }
      var roomName = socketRooms.nomSalaXatPrivat(userId, friendId);
      socket.join('chat_' + roomName);
      socket.emit('chat_joined', { room: 'chat_' + roomName });
      console.log('Usuari ' + userId + ' unit a chat_' + roomName);
    } catch (error) {
      console.error('Error gestionant join_private_chat:', error);
    }
  });

  socket.on('typing_status', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        return;
      }
      var friendId = data.friend_id;
      var isTyping = false;
      if (data.is_typing === true) {
        isTyping = true;
      }
      if (friendId) {
        var roomName = socketRooms.nomSalaXatPrivat(userId, friendId);
        io.to('chat_' + roomName).emit('typing_indicator', {
          user_id: userId,
          is_typing: isTyping
        });
      }
    } catch (error) {
      console.error('Error gestionant typing_status:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
