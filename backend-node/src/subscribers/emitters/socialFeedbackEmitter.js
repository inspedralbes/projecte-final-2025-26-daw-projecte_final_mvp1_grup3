"use strict";

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

/**
 * Emet el feedback social de forma global (broadcast).
 *
 * @param {object} io - Instància Socket.io
 * @param {object} payload - Missatge de Redis (social_event, post, comment, etc.)
 */
function emit(io, payload) {
    var event = payload.social_event;

    if (event === "new_post") {
        io.emit("new_post", payload.post);
        console.log("[SocialRealTime] Nou post emès globalment");
    } else if (event === "new_comment") {
        io.emit("new_comment", payload.comment);
        console.log("[SocialRealTime] Nou comentari emès globalment");
    } else if (event === "like_update") {
        io.emit("like_update", {
            likeable_id: payload.likeable_id,
            likeable_type: payload.likeable_type,
            likes_count: payload.likes_count,
            liked: payload.liked
        });
        console.log("[SocialRealTime] Update de like (" + payload.likeable_type + ") emès globalment");
    }
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
    emit: emit
};
