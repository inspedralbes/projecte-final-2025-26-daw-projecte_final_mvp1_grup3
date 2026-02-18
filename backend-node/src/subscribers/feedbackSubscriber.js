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
    var dades;
    try {
      dades = JSON.parse(message);
      // Emetem l'actualització d'XP al frontend
      io.emit('update_xp', dades);
    } catch (e) {
      console.error('Error parsejant feedback de Redis:', e);
    }
  });
}

module.exports = {
  init: init
};
