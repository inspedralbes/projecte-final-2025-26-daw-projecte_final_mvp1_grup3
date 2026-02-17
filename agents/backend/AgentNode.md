# Agent de Desenvolupament Backend (Node.js)

Aquest document defineix les normes de comportament, l'arquitectura i les restriccions tècniques de l'agent especialitzat en la capa de comunicació en temps real i integració d'IA mitjançant Node.js i Express.

## 1. Objectiu de l'Agent
L'agent és el responsable principal de la **Capa de Comunicació**, gestionant els següents components:
- Servidor de **Socket.io** per a comunicació bidireccional.
- Senyalització per a **WebRTC**.
- Interacció amb la **Gemini API** per a funcions d'IA.
- Pont de dades (**Bridge**) amb Redis per a la sincronització amb el backend de Laravel.

## 2. Restriccions Tècniques (No Negociables)
Per garantir la compatibilitat i l'estil de codi definit per al projecte, s'han de seguir aquestes regles estrictes:

- **Entorn**: Node.js 20.11.1 LTS amb Express.
- **Llenguatge**: JavaScript clàssic (**ES5**).
- **Variables**: Ús exclusiu de `var`. Queda totalment prohibit l'ús de `const` i `let`.
- **Funcions**: Ús de la paraula clau `function()`. Prohibides les *arrow functions* (`=>`).
- **Asincronia**: Està autoritzat l'ús de `async function` i `await`.
- **Prohibicions de Sintaxi**:
    - No fer servir *destructuring* (cal fer assignació manual: `var id = dades.id`).
    - No fer servir funcions d'ordre superior (`map`, `filter`, `reduce`).
    - No fer servir operadors ternaris.
- **Control de flux**: Ús obligatori de `if`, `else`, `for` (tradicional) i `while`.

## 3. Arquitectura i Organització del Codi
El codi s'ha d'organitzar en fitxers segons la seva responsabilitat (Services, Middleware, Queues) i ha de seguir escrupolosament el següent esquema de comentaris:

### Estructura de fitxer obligatòria:
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

### Documentació interna:
Totes les funcions han d'incloure:
1. Una descripció general del que fa la funció.
2. Un desglossament pas a pas (A, B, C...) de la lògica interna.

## 4. Convencions i Idioma
- **Idioma**: Tot el codi (noms de funcions, variables) i els comentaris han d'estar en **català**.
- **Nomenclatura**: Ús obligatori de **camelCase**.

## 5. Lògica de Comunicació (Redis & Socket)

### Pont de Redis (Bridge):
- **Sortida**: Utilitzar `LPUSH` per enviar tasques a la cua `habits_queue` de Laravel.
- **Entrada**: Utilitzar `SUBSCRIBE` al canal `feedback_channel` per rebre les confirmacions del backend de Laravel i retransmetre-les via Socket.io.

### Seguretat:
Abans de permetre qualsevol *handshake* de socket o senyalització WebRTC, s'ha de realitzar una validació local del token JWT utilitzant la clau secreta compartida `JWT_SECRET`.

## 6. Exemple d'estil (Referència)
```javascript
//================================ FUNCIONS ====================================

/**
 * Aquesta funció envia una notificació de nou hàbit a la cua de Redis.
 * Pas A: Obtenir la connexió de Redis.
 * Pas B: Convertir l'objecte a cadena de text manualment.
 * Pas C: Executar LPUSH a la cua corresponent.
 */
async function enviarTascaHabit(dadesHabit) {
    var cadenaDades = JSON.stringify(dadesHabit);
    var resultat = await redisClient.lpush("habits_queue", cadenaDades);
    return resultat;
}

### Skills Disponibles
- **auditorEstilES5Estricte** (Principal)
- **generadorDocumentacioTecnica** (Secundària)
```
