'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var socketUserId = require('../../../shared/socketUserId');
var emitToUser = require('../../../shared/emitToUser');
var peerRoomResolver = require('../services/peerRoomResolver');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function reenviarASalaUsuari(io, targetUserId, eventName, payload) {
  if (!targetUserId) {
    return;
  }
  emitToUser.emitirANotificarUsuari(io, targetUserId, eventName, payload);
}

function register(io, socket) {
  socket.on('webrtc_join', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId || !data || !data.target_user_id) {
        return;
      }
      var roomKey = peerRoomResolver.obtenirClauSala(userId, data.target_user_id);
      socket.join(roomKey);
      console.log('WebRTC: usuari ' + userId + ' unit a ' + roomKey);
    } catch (error) {
      console.error('Error webrtc_join:', error);
    }
  });

  socket.on('video_offer', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        return;
      }
      var targetId = data && data.target_user_id ? data.target_user_id : null;
      var payload = {
        from_user_id: userId,
        target_user_id: targetId,
        sdp: data && data.sdp ? data.sdp : null
      };
      reenviarASalaUsuari(io, targetId, 'video_offer', payload);
    } catch (error) {
      console.error('Error video_offer:', error);
    }
  });

  socket.on('video_answer', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        return;
      }
      var targetId = data && data.target_user_id ? data.target_user_id : null;
      var payload = {
        from_user_id: userId,
        target_user_id: targetId,
        sdp: data && data.sdp ? data.sdp : null
      };
      reenviarASalaUsuari(io, targetId, 'video_answer', payload);
    } catch (error) {
      console.error('Error video_answer:', error);
    }
  });

  socket.on('new_ice_candidate', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        return;
      }
      var targetId = data && data.target_user_id ? data.target_user_id : null;
      var payload = {
        from_user_id: userId,
        target_user_id: targetId,
        candidate: data && data.candidate ? data.candidate : null
      };
      reenviarASalaUsuari(io, targetId, 'new_ice_candidate', payload);
    } catch (error) {
      console.error('Error new_ice_candidate:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
