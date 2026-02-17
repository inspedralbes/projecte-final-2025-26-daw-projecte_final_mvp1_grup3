# Catàleg de Skills (Habilitats) del Projecte

Aquest fitxer conté la definició de les habilitats tècniques que els agents poden utilitzar per garantir la qualitat, la seguretat i la funcionalitat de l'ecosistema del projecte.

### 1. auditorEstilES5Estricte
- **Descripció**: Linter personalitzat per garantir la compatibilitat ES5 i l'estil de codi del projecte.
- **Regles**:
    - Bloqueja l'ús de `const`, `let`, `=>`.
    - Bloqueja funcions d'ordre superior: `map`, `filter`, `reduce`.
    - Bloqueja operadors ternaris.
    - Valida l'ús obligatori de `var`.
    - Valida l'idioma català en tot el codi i comentaris.
    - Valida el format `camelCase` per a tota la nomenclatura.

### 2. validadorReglesNegociXP
- **Descripció**: Verifica la lògica de gamificació al backend de Laravel.
- **Regles**:
    - Confirma l'assignació correcta d'XP segons la dificultat:
        - Fàcil: 100 XP.
        - Mitjà: 250 XP.
        - Difícil: 400 XP.
    - Valida que les ratxes es mantinguin segons la persistència diària a la base de dades.

### 3. mapejadorFluxRedisBridge
- **Descripció**: Valida la integritat del cicle tancat de dades entre microserveis.
- **Regles**:
    - Comprova el flux d'anada: `LPUSH` a `habits_queue` (Node.js) -> `BRPOP` (Laravel).
    - Comprova el flux de tornada: `PUBLISH` a `feedback_channel` (Laravel) -> `SUBSCRIBE` (Node.js).
    - Garanteix que no hi hagi pèrdua de missatges en el bridge.

### 4. generadorDocumentacioTecnica
- **Descripció**: Automatitza la creació de manuals tècnics a partir dels comentaris del codi.
- **Regles**:
    - Extrau la lògica documentada pas a pas (`// A.`, `// B.`, `// C.`) de les funcions.
    - Genera informes estructurats en Markdown per a la documentació oficial del sistema.
