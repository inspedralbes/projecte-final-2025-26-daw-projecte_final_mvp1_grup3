"use strict";

var usuarisConnectats = require("../../shared/usuarisConnectats");

function register(io, socket) {
  socket.on("social_comment", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("social_comment: usuari no autenticat");
        return;
      }
      var postAuthorId = data.post_author_id;
      if (postAuthorId && postAuthorId !== userId) {
        io.to("user_" + postAuthorId).emit("social_notification", {
          type: "new_comment",
          post_id: data.post_id,
         commenter_id: userId,
          message: " algú ha comentat al teu post",
        });
      }
    } catch (error) {
      console.error("Error gestionant social_comment:", error);
    }
  });

  socket.on("social_like", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("social_like: usuari no autenticat");
        return;
      }
      var postAuthorId = data.post_author_id;
      if (postAuthorId && postAuthorId !== userId) {
        io.to("user_" + postAuthorId).emit("social_notification", {
          type: "new_like",
          post_id: data.post_id,
          liker_id: userId,
          message: " algú ha fet like al teu post",
        });
      }
    } catch (error) {
      console.error("Error gestionant social_like:", error);
    }
  });

  socket.on("join_social_post", function (postId) {
    try {
      var room = "social_post_" + postId;
      socket.join(room);
      console.log("Socket " + socket.id + " unit a " + room);
    } catch (error) {
      console.error("Error gestionant join_social_post:", error);
    }
  });

  socket.on("leave_social_post", function (postId) {
    try {
      var room = "social_post_" + postId;
      socket.leave(room);
      console.log("Socket " + socket.id + " abandonat " + room);
    } catch (error) {
      console.error("Error gestionant leave_social_post:", error);
    }
  });

  socket.on("private_message", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("private_message: usuari no autenticat");
        return;
      }
      var receiverId = data.receiver_id;
      if (receiverId) {
        io.to("user_" + receiverId).emit("new_private_message", {
          sender_id: userId,
          message: data.message,
          created_at: data.created_at,
        });
        console.log("Missatge privat enviat de " + userId + " a " + receiverId);
      }
    } catch (error) {
      console.error("Error gestionant private_message:", error);
    }
  });

  socket.on("friend_request_notify", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("friend_request_notify: usuari no autenticat");
        return;
      }
      var addresseeId = data.addressee_id;
      if (addresseeId) {
        io.to("user_" + addresseeId).emit("new_friend_request", {
          requester_id: userId,
          requester_name: data.requester_name,
        });
        console.log("Notificació de sol·licitud d'amistat enviat a " + addresseeId);
      }
    } catch (error) {
      console.error("Error gestionant friend_request_notify:", error);
    }
  });

  socket.on("friend_request_accepted_notify", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("friend_request_accepted_notify: usuari no autenticat");
        return;
      }
      var requesterId = data.requester_id;
      if (requesterId) {
        io.to("user_" + requesterId).emit("friend_request_accepted", {
          acceptor_id: data.acceptor_id,
          acceptor_name: data.acceptor_name,
        });
        console.log("Notificació d'acceptació d'amistat enviat a " + requesterId);
      }
    } catch (error) {
      console.error("Error gestionant friend_request_accepted_notify:", error);
    }
  });
}

module.exports = {
  register: register
};