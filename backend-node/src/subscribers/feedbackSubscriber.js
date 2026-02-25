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
      var type = payload.type || 'HABIT';

      // Enviem l'actualització d'XP si Laravel la inclou
      if (payload.xp_update) {
        io.to('user_' + userId).emit('update_xp', payload.xp_update);
      }

      // Confirmem l'acció del CRUD al front per tancar el cicle
      // Fem servir "to('user_' + userId)" per a que només li arribi a qui toca
      var eventName = type === 'PLANTILLA' ? 'plantilla_action_confirmed' : 'habit_action_confirmed';
      
      io.to('user_' + userId).emit(eventName, payload);

      console.log('Feedback enviat a la sala user_' + userId + ' per l acció ' + (payload.action || 'unknown'));

    } catch (e) {
      console.error('Error parsejant feedback de Redis:', e);
    }
  });
}

module.exports = {
  init: init
};
