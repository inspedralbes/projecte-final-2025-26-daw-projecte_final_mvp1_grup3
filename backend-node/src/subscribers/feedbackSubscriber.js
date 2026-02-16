'use strict';

/**
 * Suscriptor al canal Redis feedback_channel.
 * Laravel publica aquí tras procesar un ítem de habits_queue; este módulo reenvía al Socket.io.
 */
var redis = require('redis');

var subscriber = null;
var feedbackChannel = 'feedback_channel';

function attach(io) {
  if (subscriber) {
    return;
  }
  var host = process.env.REDIS_HOST || '127.0.0.1';
  var port = parseInt(process.env.REDIS_PORT || '6379', 10);
  subscriber = redis.createClient({ socket: { host: host, port: port } });
  subscriber.on('error', function (err) {
    console.error('Redis feedbackSubscriber error:', err);
  });
  subscriber.connect().then(function () {
    return subscriber.subscribe(feedbackChannel, function (message) {
      try {
        var str = typeof message === 'string' ? message : String(message);
        var data = JSON.parse(str);
        io.emit('feedback', data);
      } catch (e) {
        io.emit('feedback', { raw: message });
      }
    });
  }).catch(function (err) {
    console.error('Redis feedbackSubscriber connect error:', err);
  });
}

module.exports = {
  attach: attach,
  feedbackChannel: feedbackChannel
};
