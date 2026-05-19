import { useSocketBridge } from '~/composables/socket/useSocketBridge.js';

/**
 * Emissors socket per notificacions socials P2P.
 */
export function useSocialSocketEmit() {
  var bridge = useSocketBridge();

  function notificarComentari(dades) {
    bridge.emitir('social_comment', dades);
  }

  function notificarLike(dades) {
    bridge.emitir('social_like', dades);
  }

  function notificarFriendRequest(dades) {
    bridge.emitir('friend_request_notify', dades);
  }

  function unirPost(postId) {
    bridge.emitir('join_social_post', postId);
  }

  function sortirPost(postId) {
    bridge.emitir('leave_social_post', postId);
  }

  return {
    notificarComentari: notificarComentari,
    notificarLike: notificarLike,
    notificarFriendRequest: notificarFriendRequest,
    unirPost: unirPost,
    sortirPost: sortirPost
  };
}
