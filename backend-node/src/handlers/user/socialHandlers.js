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
}

module.exports = {
  register: register
};