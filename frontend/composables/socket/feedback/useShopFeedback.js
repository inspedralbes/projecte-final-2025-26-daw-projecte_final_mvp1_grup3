import { useShopStore } from '~/stores/useShopStore.js';

/**
 * Registra shop_event des de Redis feedback.
 */
export function registrarShopFeedback(socket) {
  if (!socket || socket._loopyShopFeedbackRegistrat) {
    return;
  }
  socket._loopyShopFeedbackRegistrat = true;

  socket.on('shop_event', function (data) {
    console.log('[Socket] shop_event:', data);
    var shopStore = useShopStore();
    if (shopStore) {
      shopStore.aplicarEvent(data);
    }
  });
}
