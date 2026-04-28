var peerConnection = null;
var dataChannel = null;
var peerId = null;
var messageCallback = null;
var openCallback = null;

export function useWebRTC() {
  var config = {
    iceServers: [
      { urls: "stun:stun.l.google.com:19302" },
      { urls: "stun:stun1.l.google.com:19302" },
    ],
  };

  function connect(targetPeerId, onMessage, onOpen) {
    peerId = targetPeerId;
    messageCallback = onMessage;
    openCallback = onOpen;

    peerConnection = new RTCPeerConnection(config);

    peerConnection.onicecandidate = function(event) {
      if (event.candidate) {
        var payload = {
          type: "ice-candidate",
          target: peerId,
          candidate: event.candidate,
        };
        sendSignal(payload);
      }
    };

    peerConnection.onconnectionstatechange = function() {
      console.log("Connection state:", peerConnection.connectionState);
    };

    peerConnection.ondatachannel = function(event) {
      dataChannel = event.channel;
      setupDataChannel();
    };

    dataChannel = peerConnection.createDataChannel("chat");
    setupDataChannel();

    peerConnection.createOffer().then(function(offer) {
      peerConnection.setLocalDescription(offer);
      sendSignal({
        type: "offer",
        target: peerId,
        sdp: offer,
      });
    });
  }

  function setupDataChannel() {
    if (!dataChannel) return;

    dataChannel.onopen = function() {
      console.log("Data channel open");
      if (openCallback) openCallback();
    };

    dataChannel.onmessage = function(event) {
      console.log("Received message:", event.data);
      if (messageCallback) messageCallback(event.data);
    };

    dataChannel.onerror = function(error) {
      console.error("Data channel error:", error);
    };

    dataChannel.onclose = function() {
      console.log("Data channel closed");
    };
  }

  function sendSignal(payload) {
    fetch("/api/webrtc-signal", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    }).catch(function(e) {
      console.error("Signal error:", e);
    });
  }

  function handleOffer(offer) {
    peerConnection = new RTCPeerConnection(config);

    peerConnection.onicecandidate = function(event) {
      if (event.candidate) {
        sendSignal({
          type: "ice-candidate",
          target: peerId,
          candidate: event.candidate,
        });
      }
    };

    peerConnection.ondatachannel = function(event) {
      dataChannel = event.channel;
      setupDataChannel();
    };

    peerConnection.setRemoteDescription(offer).then(function() {
      return peerConnection.createAnswer();
    }).then(function(answer) {
      return peerConnection.setLocalDescription(answer);
    }).then(function() {
      sendSignal({
        type: "answer",
        target: peerId,
        sdp: answer,
      });
    });
  }

  function handleAnswer(answer) {
    peerConnection.setRemoteDescription(answer);
  }

  function handleIceCandidate(candidate) {
    if (peerConnection) {
      peerConnection.addIceCandidate(candidate);
    }
  }

  function sendChatMessage(message) {
    if (dataChannel && dataChannel.readyState === "open") {
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
    return dataChannel && dataChannel.readyState === "open";
  }

  return {
    connect: connect,
    sendChatMessage: sendChatMessage,
    disconnect: disconnect,
    isConnected: isConnected,
    handleOffer: handleOffer,
    handleAnswer: handleAnswer,
    handleIceCandidate: handleIceCandidate,
  };
}