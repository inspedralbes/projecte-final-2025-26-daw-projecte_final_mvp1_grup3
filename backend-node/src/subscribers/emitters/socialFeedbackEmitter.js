/**
 * Modul JavaScript ES5: socialFeedbackEmitter.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

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
    } else if (event === "post_updated") {
        io.emit("post_updated", payload.post);
        console.log("[SocialRealTime] Post actualitzat emès globalment");
    } else if (event === "post_deleted") {
        io.emit("post_deleted", { post_id: payload.post_id });
        console.log("[SocialRealTime] Post eliminat emès globalment");
    } else if (event === "comment_updated") {
        io.emit("comment_updated", payload.comment);
        console.log("[SocialRealTime] Comentari actualitzat emès globalment");
    } else if (event === "comment_deleted") {
        io.emit("comment_deleted", {
            comment_id: payload.comment_id,
            post_id: payload.post_id
        });
        console.log("[SocialRealTime] Comentari eliminat emès globalment");
    }
}

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
    emit: emit
};
