var webrtcHandlers = {
  onOffer: null,
  onAnswer: null,
  onIceCandidate: null
};

/**
 * Permet registrar handlers per senyalització WebRTC P2P.
 */
export function registrarWebRTCHandlers(handlers) {
  if (handlers.onOffer) {
    webrtcHandlers.onOffer = handlers.onOffer;
  }
  if (handlers.onAnswer) {
    webrtcHandlers.onAnswer = handlers.onAnswer;
  }
  if (handlers.onIceCandidate) {
    webrtcHandlers.onIceCandidate = handlers.onIceCandidate;
  }
}

/**
 * Registra listeners video_offer, video_answer, new_ice_candidate.
 */
export function registrarWebRTCFeedback(socket) {
  if (!socket || socket._loopyWebrtcFeedbackRegistrat) {
    return;
  }
  socket._loopyWebrtcFeedbackRegistrat = true;

  socket.on('video_offer', function (payload) {
    if (webrtcHandlers.onOffer && typeof webrtcHandlers.onOffer === 'function') {
      webrtcHandlers.onOffer(payload);
    }
  });

  socket.on('video_answer', function (payload) {
    if (webrtcHandlers.onAnswer && typeof webrtcHandlers.onAnswer === 'function') {
      webrtcHandlers.onAnswer(payload);
    }
  });

  socket.on('new_ice_candidate', function (payload) {
    if (webrtcHandlers.onIceCandidate && typeof webrtcHandlers.onIceCandidate === 'function') {
      webrtcHandlers.onIceCandidate(payload);
    }
  });
}

/**
 * Uneix sala WebRTC i envia senyalització via socket.
 */
export function useWebRTCSocket() {
  var bridge = useSocketBridge();

  function unirSala(targetUserId) {
    bridge.emitir('webrtc_join', { target_user_id: targetUserId });
  }

  function enviarOffer(targetUserId, sdp) {
    bridge.emitir('video_offer', {
      target_user_id: targetUserId,
      sdp: sdp
    });
  }

  function enviarAnswer(targetUserId, sdp) {
    bridge.emitir('video_answer', {
      target_user_id: targetUserId,
      sdp: sdp
    });
  }

  function enviarIceCandidate(targetUserId, candidate) {
    bridge.emitir('new_ice_candidate', {
      target_user_id: targetUserId,
      candidate: candidate
    });
  }

  return {
    unirSala: unirSala,
    enviarOffer: enviarOffer,
    enviarAnswer: enviarAnswer,
    enviarIceCandidate: enviarIceCandidate,
    registrarWebRTCHandlers: registrarWebRTCHandlers
  };
}
