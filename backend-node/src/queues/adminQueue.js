'use strict';

/**
 * Pont de compatibilitat: re-exporta el publisher de domini Admin.
 */
var adminQueuePublisher = require('../domains/Admin/publishers/adminQueuePublisher');

module.exports = {
  pushToLaravel: adminQueuePublisher.pushToLaravel
};
