'use strict';

var userRegisterSocketHandlers = require('../../domains/User/handlers/userRegisterSocketHandlers');

module.exports = {
  register: userRegisterSocketHandlers.register
};
