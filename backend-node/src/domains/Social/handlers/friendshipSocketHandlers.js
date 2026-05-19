'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var socketUserId = require('../../../shared/socketUserId');
var emitToUser = require('../../../shared/emitToUser');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('friend_request_notify', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('friend_request_notify: usuari no autenticat');
        return;
      }
      var addresseeId = data.addressee_id;
      if (addresseeId) {
        emitToUser.emitirANotificarUsuari(io, addresseeId, 'new_friend_request', {
          requester_id: userId,
          requester_name: data.requester_name
        });
        console.log('Notificació de sol·licitud d\'amistat enviat a ' + addresseeId);
      }
    } catch (error) {
      console.error('Error gestionant friend_request_notify:', error);
    }
  });

  socket.on('friend_request_accepted_notify', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('friend_request_accepted_notify: usuari no autenticat');
        return;
      }
      var requesterId = data.requester_id;
      if (requesterId) {
        emitToUser.emitirANotificarUsuari(io, requesterId, 'friend_request_accepted', {
          acceptor_id: data.acceptor_id,
          acceptor_name: data.acceptor_name
        });
        console.log('Notificació d\'acceptació d\'amistat enviat a ' + requesterId);
      }
    } catch (error) {
      console.error('Error gestionant friend_request_accepted_notify:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
