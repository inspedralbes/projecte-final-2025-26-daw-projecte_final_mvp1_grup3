'use strict';

/**
 * Subscriptor de Redis per rebre el feedback de Laravel.
 */
var redis = require('redis');

var subscriber = null;
var feedbackChannel = 'feedback_channel';

/**
 * Inicialitza la subscripció i connecta amb Socket.io.
 */
async function init(io) {
  if (subscriber) {
    return;
  }

  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);

  subscriber = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  subscriber.on('error', function (err) {
    console.error('Error Redis Subscriber:', err);
  });

  await subscriber.connect();

  await subscriber.subscribe(feedbackChannel, function (message) {
    var payload;
    try {
      payload = JSON.parse(message);

      var userId = payload.user_id;
      var action = payload.action; // CREATE, UPDATE, DELETE, TOGGLE
      var type = payload.type || 'HABIT';
      var habitData = payload.habit; // L'objecte que ve de la DB
      var plantillaData = payload.plantilla;

      // 1. Enviem l'actualització d'XP si Laravel la inclou (com tenies abans)
      if (payload.xp_update) {
        io.to('user_' + userId).emit('update_xp', payload.xp_update);
      }

      // 2. IMPORTANT: Confirmem l'acció del CRUD al front per tancar el cicle
      // Fem servir "to('user_' + userId)" per a que només li arribi a qui toca
      var success;
      if (payload.success === false) {
        success = false;
      } else {
        success = true;
      }

      if (type === 'PLANTILLA') {
        io.to('user_' + userId).emit('plantilla_action_confirmed', {
          type: type,
          action: action,
          plantilla: plantillaData,
          success: success
        });
      } else {
        io.to('user_' + userId).emit('habit_action_confirmed', {
          action: action,
          habit: habitData,
          success: success
        });
      }

      console.log('Feedback enviat a la sala user_' + userId + ' per l acció ' + action);

    } catch (e) {
      console.error('Error parsejant feedback de Redis:', e);
    }
  });
}

module.exports = {
  init: init
};
