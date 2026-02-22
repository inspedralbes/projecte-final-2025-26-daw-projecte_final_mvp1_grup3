# Agent de Socket (Client i WebRTC)

Aquest document defineix com s'ha de gestionar la comunicació en temps real des del costat del client (Frontend Nuxt). Treballa en mirall amb l'`AgentSocket` del backend.

## 1. Objectiu de l'Agent
Mantenir una connexió persistent amb el servidor de Node.js, gestionar l'autenticació del socket (Handshake amb JWT) i coordinar la senyalització WebRTC.

## 2. Configuració del Client
- **Llibreria**: `socket.io-client`.
- **Plugin Nuxt**: S'ha de crear un plugin `plugins/socket.client.js` per injectar la instància globalment.
- **Autenticació**: El socket no s'ha de connectar fins que l'usuari tingui un token vàlid.
  - `auth: { token: "Bearer " + tokenJWT }`

## 3. Gestió d'Esdeveniments
Tots els listeners s'han de definir dins de components o composables (`useWebRTC`, `useSocket`), respectant les regles de l'`AgentJavascript`.

### Esdeveniments Principals:
1.  `connect`: Connexió establerta.
2.  `connect_error`: Error d'autenticació (Token caducat -> Redirigir a Login).
3.  `update_xp`: (Recepció) Actualitzar Pinia Store amb nous valors.
4.  `video_offer` / `video_answer`: (WebRTC) Senyalització P2P.

## 4. WebRTC (Peer-to-Peer)
La lògica de WebRTC és complexa i ha de seguir passos estrictes:

1.  **RTCPeerConnection**: Crear objecte amb servidors STUN de Google.
2.  **ICE Candidates**:
    - Quan es genera un candidat local (`onicecandidate`), enviar-lo al servidor via socket (`new_ice_candidate`).
    - Quan arriba un candidat remot, afegir-lo a la connexió (`addIceCandidate`).
3.  **Oferta i Resposta**:
    - Crear Offer -> `setLocalDescription` -> Enviar `video_offer`.
    - Rebre Offer -> `setRemoteDescription` -> Crear Answer -> `setLocalDescription` -> Enviar `video_answer`.

## 5. Exemple d'Ús (Composable style ES5)

```javascript
// composables/useSocketLogic.js

export function useSocketLogic() {
    var socket = useNuxtApp().$socket;
    
    function iniciarEscolta() {
        // A. Validar connexió
        if (!socket) return;
        
        // B. Definir listeners
        socket.on('update_xp', function(dades) {
            console.log("Rebuda nova XP:", dades.xp);
            // Actualitzar Pinia (veure AgentPinia)
        });
    }
    
    function enviarSalutacio() {
        socket.emit('test_event', { missatge: "Hola Backend" });
    }
    
    return { iniciarEscolta, enviarSalutacio };
}
```

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.
