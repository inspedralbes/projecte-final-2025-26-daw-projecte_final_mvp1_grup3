# Agent de Pinia (Gestió d'Estat i Optimistic UI)

Aquest document defineix com s'ha de gestionar l'estat global de l'aplicació mitjançant **Pinia**. L'objectiu és garantir una interfície ultraràpida utilitzant patrons de **UI Optimista**.

## 1. Objectiu de l'Agent
Centralitzar les dades de l'aplicació (Usuari, Hàbits, Mascota) i gestionar la sincronització amb el Backend (Laravel/Node) sense bloquejar la interfície d'usuari.

## 2. Definició de Stores
Les stores s'han de definir a la carpeta `stores/`.
- S'ha d'utilitzar la sintaxi de **Setup Stores** (`defineStore` amb funció), però respectant les regles de `AgentJavascript` (ES5 `var`, `function`).

### Exemple d'Estructura:
```javascript
import { defineStore } from 'pinia';

export var useHabitStore = defineStore('habit', function() {
    // ESTAT (Variables amb var, mai ref() directe si no és compatible, però en Nuxt 3 ref() és necessari)
    // NOTA: En Nuxt 3 "ref" és un auto-import.
    var llistaHabits = ref([]);
    
    // ACCIONS (Funcions clàssiques)
    function carregarHabits() { ... }
    
    return { llistaHabits, carregarHabits };
});
```

## 3. Patró Optimistic UI (Snapshot & Rollback)
Aquest és el patró **MÉS IMPORTANT** de l'agent. Per a qualsevol acció que modifiqui dades (ex: "Marcar hàbit com a completat"):

1.  **Snapshot (Còpia de Seguretat)**:
    - Abans de tocar res, crear una còpia de l'objecte o variable.
    - Ús de `JSON.parse(JSON.stringify(objecte))` per trencar referències.
    
2.  **Mutació Optimista (Zero Latència)**:
    - Modificar l'estat de Pinia **IMMEDIATAMENT**.
    - L'usuari veu el canvi a l'instant (0ms).
    
3.  **Sincronització (Background)**:
    - Enviar l'acció al backend (via Socket o API).
    
4.  **Rollback (Recuperació d'Errors)**:
    - Si l'API retorna error o el Socket envia `validation_error`.
    - Restaurar l'estat utilitzant el Snapshot.
    - Mostrar notificació d'error a l'usuari ("Toast").

## 4. Estructura del Codi de l'Acció
```javascript
async function marcarHabit(idHabit) {
    // 1. Cercar l'hàbit
    var habit = null;
    for (var i = 0; i < llistaHabits.value.length; i++) {
        if (llistaHabits.value[i].id === idHabit) {
            habit = llistaHabits.value[i];
            break;
        }
    }
    
    // 2. SNAPSHOT
    var backup = JSON.parse(JSON.stringify(habit));
    
    // 3. MUTACIÓ OPTIMISTA
    habit.completat = true;
    habit.xp += 10; // Feedback visual instantani
    
    try {
        // 4. SINCRONITZACIÓ
        // Cridar al servei de cues o socket
        await enviarAccioHabit(idHabit);
    } catch (e) {
        // 5. ROLLBACK
        console.error("Error al servidor, revertint...");
        habit.completat = backup.completat;
        habit.xp = backup.xp;
        mostrarError("No s'ha pogut guardar el canvi.");
    }
}
```

## 5. Integració amb Socket.io
- Les stores s'han de subscriure als canvis que arriben per Socket (ex: `update_xp`).
- Quan arriba un event extern, l'store actualitza l'estat local per reflectir la realitat del servidor (font de veritat final).

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.
