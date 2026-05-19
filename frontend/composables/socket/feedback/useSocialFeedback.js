import { useSocialStore } from '~/stores/useSocialStore.js';
import { useFriendshipStore } from '~/stores/useFriendshipStore.js';
import { useChatStore } from '~/stores/useChatStore.js';

/**
 * Registra feedback social i xat privat.
 */
export function registrarSocialFeedback(socket, nuxtApp) {
  if (!socket || socket._loopySocialFeedbackRegistrat) {
    return;
  }
  socket._loopySocialFeedbackRegistrat = true;

  socket.on('new_post', function (post) {
    useSocialStore().handleNewPost(post);
  });

  socket.on('new_comment', function (comment) {
    useSocialStore().handleNewComment(comment);
  });

  socket.on('like_update', function (data) {
    useSocialStore().handleLikeUpdate(data);
  });

  socket.on('post_updated', function (post) {
    useSocialStore().handlePostUpdated(post);
  });

  socket.on('post_deleted', function (data) {
    useSocialStore().handlePostDeleted(data);
  });

  socket.on('comment_updated', function (comment) {
    useSocialStore().handleCommentUpdated(comment);
  });

  socket.on('comment_deleted', function (data) {
    useSocialStore().handleCommentDeleted(data);
  });

  socket.on('new_friend_request', function (data) {
    console.log('[Socket] Nova sol·licitud d\'amistat:', data);
    var friendshipStore = useFriendshipStore();
    if (friendshipStore) {
      friendshipStore.fetchPendingRequests();
    }
  });

  socket.on('friend_request_accepted', function (data) {
    console.log('[Socket] Sol·licitud d\'amistat acceptada:', data);
    var friendshipStore = useFriendshipStore();
    if (friendshipStore) {
      friendshipStore.fetchFriendsList();
    }
  });

  socket.on('connect', function () {
    socket.emit('get_online_users', function (users) {
      var chatStore = useChatStore();
      if (chatStore) {
        var intUsers = [];
        var u;
        var i;
        for (i = 0; i < (users || []).length; i++) {
          u = users[i];
          intUsers.push(parseInt(u, 10));
        }
        chatStore.setOnlineUsers(intUsers);
      }
    });
  });

  socket.on('user_status', function (data) {
    var chatStore = useChatStore();
    if (chatStore && data) {
      chatStore.updateUserStatus(data.userId, data.online);
    }
  });

  socket.on('new_private_message', function (data) {
    console.log('[Socket] Nou missatge privat:', data);
    var chatStore = useChatStore();
    if (chatStore && data.sender_id) {
      chatStore.receiveMessage(data.sender_id, {
        id: data.id || Date.now(),
        sender_id: data.sender_id,
        receiver_id: data.receiver_id,
        contingut: data.message,
        created_at: data.created_at || new Date().toISOString()
      });
    }
  });

  if (nuxtApp && nuxtApp.$onTypingIndicator) {
    socket.on('typing_indicator', function (data) {
      if (nuxtApp.$typingCallbacks) {
        var j;
        for (j = 0; j < nuxtApp.$typingCallbacks.length; j++) {
          nuxtApp.$typingCallbacks[j](data);
        }
      }
    });
  }
}
