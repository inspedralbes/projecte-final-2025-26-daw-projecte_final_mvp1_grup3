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
        var roomName = [userId, receiverId].sort().join("_");
        io.to("chat_" + roomName).emit("new_private_message", {
          sender_id: userId,
          receiver_id: receiverId,
          message: data.message,
          created_at: data.created_at
        });
        console.log("Missatge privat enviat a chat_" + roomName);
      }
    } catch (error) {
      console.error("Error gestionant private_message:", error);
    }
  });

  socket.on("join_private_chat", async function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("join_private_chat: usuari no autenticat");
        socket.emit("chat_error", { error: "No autenticat" });
        return;
      }
      var friendId = data.friend_id;
      if (!friendId) {
        socket.emit("chat_error", { error: "friend_id requerit" });
        return;
      }
      var roomName = [userId, friendId].sort().join("_");
      socket.join("chat_" + roomName);
      socket.emit("chat_joined", { room: "chat_" + roomName });
      console.log("Usuari " + userId + " unit a chat_" + roomName);
    } catch (error) {
      console.error("Error gestionant join_private_chat:", error);
    }
  });

  socket.on("typing_status", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) return;
      var friendId = data.friend_id;
      var isTyping = data.is_typing === true;
      if (friendId) {
        var roomName = [userId, friendId].sort().join("_");
        io.to("chat_" + roomName).emit("typing_indicator", {
          user_id: userId,
          is_typing: isTyping
        });
      }
    } catch (error) {
      console.error("Error gestionant typing_status:", error);
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

  socket.on("clan_message", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId) {
        console.warn("clan_message: usuari no autenticat");
        return;
      }
      var clanId = data.clan_id;
      if (clanId) {
        var userName = data.usuari_nom || "Usuari";
        io.to("clan_" + clanId).emit("new_clan_message", {
          clan_id: clanId,
          sender_id: userId,
          usuari_nom: userName,
          message: data.message,
          created_at: data.created_at,
        });
        console.log("Missatge de clan enviat de " + userId + " (" + userName + ") al clan " + clanId);
      }
    } catch (error) {
      console.error("Error gestionant clan_message:", error);
    }
  });

  socket.on("clan_request_notify", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      console.log(">>> clan_request_notify rebut:", data, "userId:", userId);
      if (!userId) {
        console.warn("clan_request_notify: usuari no autenticat");
        return;
      }
      var leaderId = data.leader_id;
      if (leaderId) {
        io.to("user_" + leaderId).emit("clan_request_received", {
          clan_id: data.clan_id,
          clan_nom: data.clan_nom,
          usuari_id: userId,
          created_at: data.created_at,
        });
        console.log(">>> Notificació de sol·licitud de clan a líder " + leaderId + " (sala user_" + leaderId + ")");
      } else {
        console.warn(">>> clan_request_notify: leader_id no proporcionat");
      }
    } catch (error) {
      console.error("Error gestionant clan_request_notify:", error);
    }
  });

  socket.on("clan_invitation_received", function (data) {
    try {
      var invitedUserId = data.invited_user_id;
      if (invitedUserId) {
        io.to("user_" + invitedUserId).emit("clan_invitation_received", {
          clan_id: data.clan_id,
          clan_nom: data.clan_nom,
          invitador_id: data.invitador_id,
          created_at: data.created_at,
        });
        console.log("Invitació de clan/enviada a " + invitedUserId);
      }
    } catch (error) {
      console.error("Error gestionant clan_invitation_received:", error);
    }
  });

  socket.on("clan_share_notification", function (data) {
    try {
      var clanId = data.clan_id;
      var memberIds = data.member_ids || [];
      memberIds.forEach(function (memberId) {
        io.to("user_" + memberId).emit("clan_share_received", {
          clan_id: clanId,
          sender_id: data.sender_id,
          share_type: data.share_type,
          created_at: data.created_at,
        });
      });
      console.log("Notificació de compartició de clan/enviada");
    } catch (error) {
      console.error("Error gestionant clan_share_notification:", error);
    }
  });

  socket.on("join_clan_room", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId || !data.clan_id) {
        return;
      }
      socket.join("clan_" + data.clan_id);
      console.log("Usuari " + userId + " unit a clan_room " + data.clan_id);
    } catch (error) {
      console.error("Error unint a clan_room:", error);
    }
  });

  socket.on("leave_clan_room", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId || !data.clan_id) {
        return;
      }
      socket.leave("clan_" + data.clan_id);
      console.log("Usuari " + userId + " sortit de clan_room " + data.clan_id);
    } catch (error) {
      console.error("Error sortint de clan_room:", error);
    }
  });

socket.on("clan_member_joined", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!data.clan_id || !data.user_id) {
        return;
      }
      io.to("clan_" + data.clan_id).emit("clan_member_joined", {
        clan_id: data.clan_id,
        user_id: data.user_id,
        user_nom: data.user_nom,
        created_at: data.created_at
      });
    } catch (error) {
      console.error("Error unint member_joined:", error);
    }
  });

  socket.on("clan_member_left", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId || !data.clan_id) {
        return;
      }
      io.to("clan_" + data.clan_id).emit("clan_member_left", {
        clan_id: data.clan_id,
        user_id: data.user_id,
        user_nom: data.user_nom,
        message: data.user_nom + " ha estat expulsat del clan"
      });
    } catch (error) {
      console.error("Error unint member_left:", error);
    }
  });

  socket.on("clan_request_accepted", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      console.log(">>> clan_request_accepted rebut:", data, "de userId:", userId);
      if (!userId || !data.clan_id || !data.usuari_id) {
        return;
      }
      io.to("user_" + data.usuari_id).emit("clan_request_accepted", {
        clan_id: data.clan_id,
        usuari_id: data.usuari_id,
        message: "La teva sol·licitud d'unió al clan ha estat acceptada"
      });
      console.log(">>> Emitint clan_member_joined a clan_", data.clan_id);
      io.to("clan_" + data.clan_id).emit("clan_member_joined", {
        clan_id: data.clan_id,
        user_id: data.usuari_id,
        user_nom: data.usuari_nom,
        created_at: data.created_at
      });
    } catch (error) {
      console.error("Error unint request_accepted:", error);
    }
  });

  socket.on("clan_request_rejected", function (data) {
    try {
      var userId = socket.decoded_token && socket.decoded_token.user_id;
      if (!userId || !data.clan_id || !data.usuari_id) {
        return;
      }
      io.to("user_" + data.usuari_id).emit("clan_request_rejected", {
        clan_id: data.clan_id,
        usuari_id: data.usuari_id,
        message: "La teva sol·licitud d'unió al clan ha estat rebutjada"
      });
    } catch (error) {
      console.error("Error unint request_rejected:", error);
    }
  });
}

module.exports = {
  register: register
};