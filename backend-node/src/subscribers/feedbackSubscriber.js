'use strict';

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redis = require('redis');

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var subscriptor = null;
var canalFeedback = 'feedback_channel';

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Inicialitza la subscripció i connecta amb Socket.io.
 * A. Crear client Redis si no existeix.
 * B. Connectar i subscriure's al canal.
 * C. Processar missatges i retransmetre'ls.
 */
async function init(io) {
  if (subscriptor) {
    return;
  }

  // A. Crear client Redis
  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);

  console.log('feedbackSubscriber: connectant a Redis a host', host, 'port', port);

  subscriptor = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  subscriptor.on('error', function (err) {
    console.error('Error Redis Subscriber:', err);
  });

  // B. Connectar
  await subscriptor.connect();

  // C. Subscriure's al canal i processar missatges
  await subscriptor.subscribe(canalFeedback, function (missatge) {
    var carrega = null;
    try {
      carrega = JSON.parse(missatge);

      // C1. Si és feedback d'admin, enviar a la sala corresponent
      if (carrega.admin_id !== undefined) {
        var administradorId = carrega.admin_id;
        io.to('admin_' + administradorId).emit('admin_action_confirmed', {
          admin_id: administradorId,
          entity: carrega.entity,
          action: carrega.action,
          success: carrega.success,
          data: carrega.data
        });
        console.log('Feedback admin enviat a admin_' + administradorId);
        return;
      }

      // C2. Feedback d'usuari: preparar dades
      var usuariId = carrega.user_id;
      var tipus = carrega.type;
      var accio = carrega.action;

      // C3. Enviar actualització d'XP si Laravel la inclou
      if (carrega.xp_update) {
        io.to('user_' + usuariId).emit('update_xp', carrega.xp_update);
      }

      // C4. Si s'ha completat una missió diària, emetre mission_completed
      if (carrega.mission_completed) {
        io.to('user_' + usuariId).emit('mission_completed', carrega.mission_completed);
      }

      // C5. Confirmar acció CRUD al frontend
      var nomEvent = 'habit_action_confirmed';
      if (tipus === 'PLANTILLA') {
        nomEvent = 'plantilla_action_confirmed';
      }

      io.to('user_' + usuariId).emit(nomEvent, carrega);
      console.log('Feedback enviat a user_' + usuariId + ' per l acció ' + accio);
    } catch (e) {
      console.error('Error parsejant feedback de Redis:', e);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  init: init
};
