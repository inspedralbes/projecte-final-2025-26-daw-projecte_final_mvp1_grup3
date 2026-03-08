"use strict";

//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

var redis = require("redis");
var userFeedbackEmitter = require("./emitters/userFeedbackEmitter");
var adminFeedbackEmitter = require("./emitters/adminFeedbackEmitter");

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var subscriber = null;
var feedbackChannel = "feedback_channel";

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Inicialitza la subscripció de Redis i reemet per Socket.io.
 * Delega a userFeedbackEmitter o adminFeedbackEmitter segons payload.
 *
 * @param {object} io - Instància Socket.io
 */
async function init(io) {
  if (subscriber) {
    return;
  }

  var host = process.env.REDIS_HOST || "127.0.0.1";
  var port = parseInt(process.env.REDIS_PORT || "6379", 10);

  console.log("feedbackSubscriber: Attempting to connect to Redis at host:", host, "port:", port);

  subscriber = redis.createClient({
    socket: {
      host: host,
      port: port
    }
  });

  subscriber.on("error", function (err) {
    console.error("Error Redis Subscriber:", err);
  });

  await subscriber.connect();

  await subscriber.subscribe(feedbackChannel, function (message) {
    var payload;
    try {
      payload = JSON.parse(message);

      if (payload.admin_id !== undefined) {
        adminFeedbackEmitter.emit(io, payload);
      } else if (payload.user_id !== undefined) {
        userFeedbackEmitter.emit(io, payload);
      }
    } catch (e) {
      console.error("Error parsejant feedback de Redis:", e);
    }
  });
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  init: init
};
