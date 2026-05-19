'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var socketUserId = require('../../../shared/socketUserId');
var socketRooms = require('../../../shared/socketRooms');
var emitToUser = require('../../../shared/emitToUser');

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

function register(io, socket) {
  socket.on('clan_message', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('clan_message: usuari no autenticat');
        return;
      }
      var clanId = data.clan_id;
      if (clanId) {
        var userName = 'Usuari';
        if (data.usuari_nom) {
          userName = data.usuari_nom;
        }
        io.to('clan_' + clanId).emit('new_clan_message', {
          clan_id: clanId,
          sender_id: userId,
          usuari_nom: userName,
          message: data.message,
          created_at: data.created_at
        });
      }
    } catch (error) {
      console.error('Error gestionant clan_message:', error);
    }
  });

  socket.on('clan_request_notify', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId) {
        console.warn('clan_request_notify: usuari no autenticat');
        return;
      }
      var leaderId = data.leader_id;
      if (leaderId) {
        emitToUser.emitirANotificarUsuari(io, leaderId, 'clan_request_received', {
          clan_id: data.clan_id,
          clan_nom: data.clan_nom,
          usuari_id: userId,
          created_at: data.created_at
        });
      }
    } catch (error) {
      console.error('Error gestionant clan_request_notify:', error);
    }
  });

  socket.on('clan_invitation_received', function (data) {
    try {
      var invitedUserId = data.invited_user_id;
      if (invitedUserId) {
        emitToUser.emitirANotificarUsuari(io, invitedUserId, 'clan_invitation_received', {
          clan_id: data.clan_id,
          clan_nom: data.clan_nom,
          invitador_id: data.invitador_id,
          created_at: data.created_at
        });
      }
    } catch (error) {
      console.error('Error gestionant clan_invitation_received:', error);
    }
  });

  socket.on('clan_share_notification', function (data) {
    try {
      var clanId = data.clan_id;
      var memberIds = data.member_ids || [];
      var i;
      for (i = 0; i < memberIds.length; i++) {
        emitToUser.emitirANotificarUsuari(io, memberIds[i], 'clan_share_received', {
          clan_id: clanId,
          sender_id: data.sender_id,
          share_type: data.share_type,
          created_at: data.created_at
        });
      }
    } catch (error) {
      console.error('Error gestionant clan_share_notification:', error);
    }
  });

  socket.on('join_clan_room', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId || !data.clan_id) {
        return;
      }
      socketRooms.joinClanRoom(socket, data.clan_id);
    } catch (error) {
      console.error('Error unint a clan_room:', error);
    }
  });

  socket.on('leave_clan_room', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId || !data.clan_id) {
        return;
      }
      socket.leave('clan_' + data.clan_id);
    } catch (error) {
      console.error('Error sortint de clan_room:', error);
    }
  });

  socket.on('clan_member_joined', function (data) {
    try {
      if (!data.clan_id || !data.user_id) {
        return;
      }
      io.to('clan_' + data.clan_id).emit('clan_member_joined', {
        clan_id: data.clan_id,
        user_id: data.user_id,
        user_nom: data.user_nom,
        created_at: data.created_at
      });
    } catch (error) {
      console.error('Error clan_member_joined:', error);
    }
  });

  socket.on('clan_member_left', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId || !data.clan_id) {
        return;
      }
      io.to('clan_' + data.clan_id).emit('clan_member_left', {
        clan_id: data.clan_id,
        user_id: data.user_id,
        user_nom: data.user_nom,
        message: data.user_nom + ' ha estat expulsat del clan'
      });
    } catch (error) {
      console.error('Error clan_member_left:', error);
    }
  });

  socket.on('clan_request_accepted', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId || !data.clan_id || !data.usuari_id) {
        return;
      }
      emitToUser.emitirANotificarUsuari(io, data.usuari_id, 'clan_request_accepted', {
        clan_id: data.clan_id,
        usuari_id: data.usuari_id,
        message: 'La teva sol·licitud d\'unió al clan ha estat acceptada'
      });
      io.to('clan_' + data.clan_id).emit('clan_member_joined', {
        clan_id: data.clan_id,
        user_id: data.usuari_id,
        user_nom: data.usuari_nom,
        created_at: data.created_at
      });
    } catch (error) {
      console.error('Error clan_request_accepted:', error);
    }
  });

  socket.on('clan_request_rejected', function (data) {
    try {
      var userId = socketUserId.obtenirUserIdSocket(socket);
      if (!userId || !data.clan_id || !data.usuari_id) {
        return;
      }
      emitToUser.emitirANotificarUsuari(io, data.usuari_id, 'clan_request_rejected', {
        clan_id: data.clan_id,
        usuari_id: data.usuari_id,
        message: 'La teva sol·licitud d\'unió al clan ha estat rebutjada'
      });
    } catch (error) {
      console.error('Error clan_request_rejected:', error);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  register: register
};
