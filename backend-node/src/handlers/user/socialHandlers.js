'use strict';

/**
 * Pont de compatibilitat: registra els 4 handlers Social dividits.
 */
var socialPostSocketHandlers = require('../../domains/Social/handlers/socialPostSocketHandlers');
var chatSocketHandlers = require('../../domains/Social/handlers/chatSocketHandlers');
var friendshipSocketHandlers = require('../../domains/Social/handlers/friendshipSocketHandlers');
var clanSocketHandlers = require('../../domains/Social/handlers/clanSocketHandlers');

function register(io, socket) {
  socialPostSocketHandlers.register(io, socket);
  chatSocketHandlers.register(io, socket);
  friendshipSocketHandlers.register(io, socket);
  clanSocketHandlers.register(io, socket);
}

module.exports = {
  register: register
};
