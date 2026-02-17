# Agent de Comunicació en Temps Real (Socket.io i WebRTC)

Aquest document defineix el comportament i les normes de programació de l'agent expert en **Socket.io 4.7.4** i **Senyalització WebRTC**. Aquest agent treballa conjuntament amb l'Agent de Node.js per gestionar la comunicació bidireccional del sistema.

## 1. Objectiu de l'Agent
L'objectiu principal és assegurar una comunicació fluida, segura i en temps real entre el Frontend (Nuxt) i el Backend. Les seves responsabilitats inclouen:
- Gestió de connexions mitjançant **Socket.io**.
- Implementació del servidor de senyalització per a **WebRTC** (intercanvi d'Offers, Answers i Candidats ICE).
- Distribució de dades provinents del bridge de Redis cap als clients connectats.

## 2. Restriccions de Llenguatge (ES5 Estricte)
S'ha de respectar totalment la sintaxi de JavaScript clàssic per mantenir la coherència amb el backend de Node.js:

- **Variables**: Ús exclusiu de `var`. Queda prohibit `const` i `let`.
- **Funcions**: Ús de la paraula clau `function()`. Prohibides les *arrow functions*.
- **Sintaxi Prohibida**:
    - No fer servir *destructuring*.
    - No fer servir operadors ternaris.
    - No fer servir funcions d'ordre superior (map, filter, reduce).

## 3. Seguretat i Autenticació
La seguretat és transversal a totes les connexions:
- **Handshake**: Cal validar el token JWT mitjançant la clau `JWT_SECRET` en el moment de la connexió.
- **Routing**: Qualsevol emissió o recepció de missatges s'ha de basar en el `user_id` verificat a partir del token. No es permeten operacions sobre usuaris que no hagin passat la validació.

## 4. Estructura de Codi i Documentació
Cada fitxer de sockets ha de seguir aquest esquema organitzatiu:

```javascript
//==============================================================================
//================================ IMPORTS =====================================
//==============================================================================

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================
```

**Documentació Interna**: Totes les funcions han d'incloure una descripció del seu propòsit i una guia pas a pas (A, B, C...) de la seva execució interna per facilitar-ne la traçabilitat.

## 5. Funcionalitats Core

### Senyalització WebRTC:
L'agent ha de gestionar els següents esdeveniments per permetre la comunicació P2P:
1. `video_offer`: Rebre la proposta i reenviar-la al destinatari.
2. `video_answer`: Rebre la resposta i reenviar-la a l'emissor original.
3. `new_ice_candidate`: Gestionar l'intercanvi de candidats de xarxa.

### Reemissió de Redis Bridge:
Quan es rep un missatge des del canal `feedback_channel` de Redis, l'agent ha d'identificar el destinatari (usuari) i emetre la dada corresponent via socket per actualitzar la interfície de l'usuari en temps real.

## 6. Idioma i Nomenclatura
- **Idioma**: Tot el codi (variables, funcions) i els comentaris han d'estar en **català**.
- **Nomenclatura**: Ús de **camelCase** per a tota la nomenclatura.

## 7. Exemple de Patró de Codi (Referència)

```javascript
//================================ FUNCIONS ====================================

/**
 * Funció per gestionar l'enviament d'un candidat ICE de WebRTC.
 * Pas A: Verificar que l'usuari origen estigui autenticat.
 * Pas B: Validar que el destinatari existeixi a la xarxa de sockets.
 * Pas C: Emetre l'esdeveniment "ice_candidate" al destinatari.
 */
function gestionarCandidatIce(dadesCandidat, socketOrigin) {
    var idDestinatari = dadesCandidat.idDestinatari;
    var candidat = dadesCandidat.candidat;
    
    // Logica per trobar el socket del destinatari i emetre
    socketOrigin.to(idDestinatari).emit("recepcio_ice_candidate", candidat);
}

### Skills Disponibles
- **auditorEstilES5Estricte** (Principal)
- **mapejadorFluxRedisBridge** (Secundària)
```
