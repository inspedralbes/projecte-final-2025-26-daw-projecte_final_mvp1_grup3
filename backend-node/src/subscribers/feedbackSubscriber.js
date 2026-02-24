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

      if (payload.admin_id !== undefined) {
        var adminId = payload.admin_id;
        io.to('admin_' + adminId).emit('admin_action_confirmed', {
          admin_id: adminId,
          entity: payload.entity,
          action: payload.action,
          success: payload.success,
          data: payload.data
        });
        console.log('Admin feedback enviat a admin_' + adminId);
      } else {
        var userId = payload.user_id;
        var action = payload.action;
        var habitData = payload.habit;

        if (payload.xp_update) {
          io.to('user_' + userId).emit('update_xp', payload.xp_update);
        }

        io.to('user_' + userId).emit('habit_action_confirmed', {
          action: action,
          habit: habitData,
          success: true
        });

        console.log('Feedback enviat a la sala user_' + userId + ' per l acció ' + action);
      }
    } catch (e) {
      console.error('Error parsejant feedback de Redis:', e);
    }
  });
}

module.exports = {
  init: init
};
