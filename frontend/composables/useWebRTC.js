import { useWebRTCSocket, registrarWebRTCHandlers } from '~/composables/domains/webrtc/useWebRTCSocket.js';
import { useAuthStore } from '~/stores/useAuthStore.js';

var peerConnection = null;
var dataChannel = null;
var peerId = null;
var messageCallback = null;
var openCallback = null;
var webrtcSocket = null;
var handlersRegistrats = false;

function assegurarHandlersSocket() {
  if (handlersRegistrats) {
    return;
  }
  handlersRegistrats = true;
  registrarWebRTCHandlers({
    onOffer: function (payload) {
      if (!payload || !payload.sdp) {
        return;
      }
      var fromId = payload.from_user_id;
      if (fromId) {
        peerId = fromId;
      }
      handleOffer(payload.sdp);
    },
    onAnswer: function (payload) {
      if (!payload || !payload.sdp) {
        return;
      }
      handleAnswer(payload.sdp);
    },
    onIceCandidate: function (payload) {
      if (!payload || !payload.candidate) {
        return;
      }
      handleIceCandidate(payload.candidate);
    }
  });
}

function obtenirUsuariId() {
  var authStore = useAuthStore();
  if (authStore.user && authStore.user.id) {
    return authStore.user.id;
  }
  return null;
}

export function useWebRTC() {
  var config = {
    iceServers: [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' }
    ]
  };

  if (!webrtcSocket) {
    webrtcSocket = useWebRTCSocket();
    assegurarHandlersSocket();
  }

  function connect(targetPeerId, onMessage, onOpen) {
    peerId = targetPeerId;
    messageCallback = onMessage;
    openCallback = onOpen;

    webrtcSocket.unirSala(targetPeerId);

    peerConnection = new RTCPeerConnection(config);

    peerConnection.onicecandidate = function (event) {
      if (event.candidate) {
        webrtcSocket.enviarIceCandidate(peerId, event.candidate);
      }
    };

    peerConnection.onconnectionstatechange = function () {
      console.log('Connection state:', peerConnection.connectionState);
    };

    peerConnection.ondatachannel = function (event) {
      dataChannel = event.channel;
      setupDataChannel();
    };

    dataChannel = peerConnection.createDataChannel('chat');
    setupDataChannel();

    peerConnection.createOffer().then(function (offer) {
      peerConnection.setLocalDescription(offer);
      webrtcSocket.enviarOffer(peerId, offer);
    });
  }

  function setupDataChannel() {
    if (!dataChannel) {
      return;
    }

    dataChannel.onopen = function () {
      console.log('Data channel open');
      if (openCallback) {
        openCallback();
      }
    };

    dataChannel.onmessage = function (event) {
      console.log('Received message:', event.data);
      if (messageCallback) {
        messageCallback(event.data);
      }
    };

    dataChannel.onerror = function (error) {
      console.error('Data channel error:', error);
    };

    dataChannel.onclose = function () {
      console.log('Data channel closed');
    };
  }

  function handleOffer(offer) {
    peerConnection = new RTCPeerConnection(config);

    peerConnection.onicecandidate = function (event) {
      if (event.candidate) {
        webrtcSocket.enviarIceCandidate(peerId, event.candidate);
      }
    };

    peerConnection.ondatachannel = function (event) {
      dataChannel = event.channel;
      setupDataChannel();
    };

    peerConnection.setRemoteDescription(offer).then(function () {
      return peerConnection.createAnswer();
    }).then(function (answer) {
      return peerConnection.setLocalDescription(answer);
    }).then(function () {
      webrtcSocket.enviarAnswer(peerId, peerConnection.localDescription);
    });
  }

  function handleAnswer(answer) {
    if (peerConnection) {
      peerConnection.setRemoteDescription(answer);
    }
  }

  function handleIceCandidate(candidate) {
    if (peerConnection) {
      peerConnection.addIceCandidate(candidate);
    }
  }

  function sendChatMessage(message) {
    if (dataChannel && dataChannel.readyState === 'open') {
      dataChannel.send(message);
      return true;
    }
    return false;
  }

  function disconnect() {
    if (dataChannel) {
      dataChannel.close();
      dataChannel = null;
    }
    if (peerConnection) {
      peerConnection.close();
      peerConnection = null;
    }
  }

  function isConnected() {
    if (dataChannel && dataChannel.readyState === 'open') {
      return true;
    }
    return false;
  }

  return {
    connect: connect,
    sendChatMessage: sendChatMessage,
    disconnect: disconnect,
    isConnected: isConnected,
    handleOffer: handleOffer,
    handleAnswer: handleAnswer,
    handleIceCandidate: handleIceCandidate
  };
}
