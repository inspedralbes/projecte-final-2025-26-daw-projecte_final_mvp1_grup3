# Agent de Javascript (Normes de Sintaxi ES5 Estricte)

Aquest document defineix les regles de sintaxi i estil de codi Javascript que s'han d'aplicar transversalment a tot el projecte (Frontend Nuxt i Backend Node.js). L'objectiu és mantenir un codi "Old School", robust i homogeni.

## 1. Objectiu de l'Agent
Garantir que tot el codi Javascript escrit sigui compatible amb la filosofia del projecte: màxima simplicitat, sense "sucre sintàctic" modern (excepte async/await) i amb una estructura clàssica.

## 2. Regles de Sintaxi (ES5 + Async)
Aquestes regles són **NO NEGOCIABLES**:

- **Variables**:
    - Ús EXCLUSIU de `var`.
    - PROHIBIT `const`.
    - PROHIBIT `let`.

- **Funcions**:
    - Ús EXCLUSIU de la declaració `function nomFuncio() { ... }` o `var nom = function() { ... }`.
    - PROHIBIDES les Arrow Functions `=>`.

- **Bucles i Iteració**:
    - Ús EXCLUSIU de `for (var i = 0; i < n; i++)`, `while` o `for...in` (amb check de `hasOwnProperty`).
    - PROHIBIT `forEach`.
    - PROHIBIT `map`.
    - PROHIBIT `filter`.
    - PROHIBIT `reduce`.

- **Asincronia**:
    - PERMÈS `async function` i `await` (compatibilitat amb Node 20).
    - Ús preferent de `try/catch` per a la gestió d'errors asíncrons.

- **Objectes i Arrays**:
    - PROHIBIT *Destructuring* (`var { id } = objecte` -> ERROR).
    - Ús d'accés directe: `var id = objecte.id`.
    - PROHIBIT *Spread Operator* `...` (excepte si és imprescindible per clonar, però preferible `JSON.parse(JSON.stringify)` o còpia manual).

- **Condicionals**:
    - PROHIBIT Operador Ternari `condicio ? a : b`. S'ha d'utilitzar `if / else`.

## 3. Idioma i Comentaris
- **Idioma del Codi**: Variables i funcions en **català** i **camelCase** (ex: `var usuariActiu = ...`).
- **Comentaris**:
    - Totes les funcions han de tenir comentaris explicatius.
    - Els comentaris han de ser en **català**.
    - Estructura pas a pas dins de funcions complexes: `// A. Validar...`, `// B. Processar...`.

## 4. Exemple Correcte vs Incorrecte

### Incorrecte (Moderne):
```javascript
const processUser = (user) => {
    const { id, name } = user;
    return id ? \`User: \${name}\` : 'Unknown';
}
```

### Correcte (Agent Style):
```javascript
function processarUsuari(usuari) {
    var id = usuari.id;
    var nom = usuari.nom;
    
    // A. Comprovar si existeix ID
    if (id) {
        return "Usuari: " + nom;
    } else {
        return "Desconegut";
    }
}
```
