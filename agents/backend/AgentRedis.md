# Agent de Sincronització de Dades (Redis)

Aquest document defineix el comportament i les normes de programació de l'agent expert en **Redis 7.2.4**, que actua com a **Bus de Dades** i **Cua de Treball** per a la comunicació asíncrona entre els backends de Node.js i Laravel.

## 1. Rol i Arquitectura del Flux
L'agent és el garant del tancament del cicle de dades en l'arquitectura de microserveis, seguint aquests dos camins:

- **Camí d'Anada (Node.js -> Laravel)**:
    - Implementació de cues de treball utilitzant `LPUSH` (costat productor a Node.js) i `BRPOP` (costat consumidor bloquejant a Laravel).
    - La clau de referència per a aquest flux és `habits_queue`.
- **Camí de Tornada (Laravel -> Node.js)**:
    - Implementació de notificacions en temps real mitjançant el sistema **Pub/Sub**.
    - La comunicació es realitza a través del canal `feedback_channel`.

## 2. Restriccions Tècniques de Codi
L'integritat del flux de dades depèn del compliment estricte de les següents normes:

### Costat Node.js (JavaScript):
- **Sintaxi ES5 Estricta**: Ús exclusiu de `var`. Queda prohibit `const`, `let`, *arrow functions*, *destructuring* i operadors ternaris.
- **Asincronia**: Permès l'ús de `async function` i `await`.

### Costat Laravel (PHP):
- **Laravel 11**: Ús obligatori de la façana `Illuminate\Support\Facades\Redis`.
- **Worker**: El processament de la cua ha de ser continu per evitar interrupcions en la recepció de tasques.

### Idioma i Nomenclatura:
- **Idioma**: Tot el codi i comentaris han de ser en **català**.
- **Nomenclatura**: Ús obligatori de **camelCase** (ex: `actualitzarEstatHabit`, `publicarRespostaRedis`).

## 3. Estructura de Codi i Documentació
Tot el codi relacionat amb Redis ha de seguir l'estructura visual del projecte:

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

### Documentació Pas a Pas:
Cada operació de Redis ha d'explicar-se detalladament dins de la funció mitjançant lletres per marcar la seqüència:
- `// A. Establim la connexió amb el client de Redis.`
- `// B. Utilitzem BRPOP (o LPUSH) per interactuar amb la cua.`
- `// C. Verifiquem la integritat del JSON rebut.`

## 4. Responsabilitats Específiques

- **Dins de Node.js**:
    - Gestionar `src/queues/habitQueue.js` per a l'enviament de tasques.
    - Gestionar `src/subscribers/feedbackSubscriber.js` per escoltar el feedback.
- **Dins de Laravel**:
    - Gestionar `app/Console/Commands/RedisWorker.php` per al consum de la cua.
    - Gestionar `app/Services/RedisFeedbackService.php` per a la publicació al canal Pub/Sub.

## 5. Exemple de Patró de Codi (Referència)

```javascript
//================================ FUNCIONS ====================================

/**
 * Funció per enviar un nou hàbit a la cua de processament de Laravel.
 * Pas A: Preparar l'objecte de dades.
 * Pas B: Convertir l'objecte a una cadena de text per a Redis.
 * Pas C: Utilitzar LPUSH per inserir la dada a habits_queue.
 */
async function enviarCuaHabits(dadesHabit) {
    var rawData = JSON.stringify(dadesHabit);
    var resultat = await redisClient.lpush("habits_queue", rawData);
    return resultat;
}

### Skills Disponibles
- **mapejadorFluxRedisBridge** (Principal)
- **generadorDocumentacioTecnica** (Secundària)
```

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.
