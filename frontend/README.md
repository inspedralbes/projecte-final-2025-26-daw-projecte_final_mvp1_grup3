# Frontend - Documentació Sockets

Aquest document descriu com el **backend Node.js** (via Socket.io) rep i emet esdeveniments. Usa aquesta guia per implementar la integració Socket.io al frontend.

---

## Índex

1. [Events que rep el backend (Frontend → Backend)](#1-events-que-rep-el-backend-frontend--backend)
2. [Events que emet el backend (Backend → Frontend)](#2-events-que-emet-el-backend-backend--frontend)
3. [Sales (rooms)](#3-sales-rooms)
4. [Recomanacions d’ús](#4-recomanacions-dús)

---

## 1. Events que rep el backend (Frontend → Backend)

El frontend ha d’enviar aquests events al servidor per desencadenar accions.

---

### `habit_action`

**Què fa:** Envia una acció CRUD sobre un hàbit (crear, actualitzar, eliminar) a la cua de Redis perquè Laravel la processi.

| Camp        | Tipus   | Obligatori | Descripció                                                                                                                                 |
|-------------|---------|------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| `action`    | string  | Sí         | `"CREATE"`, `"UPDATE"` o `"DELETE"`                                                                                                         |
| `habit_id`  | number  | Si UPDATE/DELETE | ID de l’hàbit                                                                                                                         |
| `habit_data`| object  | Si CREATE/UPDATE | Objecte amb les dades de l’hàbit (veure estructura més avall)                                                                          |

**Estructura de `habit_data` (per CREATE i UPDATE):**

| Camp              | Tipus  | Descripció                                                |
|-------------------|--------|------------------------------------------------------------|
| `plantilla_id`    | number | ID de la plantilla associada                               |
| `titol`           | string | Títol de l’hàbit                                           |
| `dificultat`      | string | `"facil"`, `"media"` o `"dificil"`                         |
| `frequencia_tipus`| string | Tipus de freqüència                                        |
| `dies_setmana`    | string | Dies de la setmana (ex: `"1,2,3"`)                         |
| `objectiu_vegades`| number | Nombre d’objectius                                         |
| `categoria_id`    | number | ID de la categoria                                         |
| `icona`           | string | Nom o ruta de la icona                                     |
| `color`           | string | Color associat                                             |

**Exemple – Crear hàbit:**
```javascript
socket.emit('habit_action', {
  action: 'CREATE',
  habit_data: {
    titol: 'Beure aigua',
    dificultat: 'facil',
    plantilla_id: 1,
  }
});
```

**Exemple – Actualitzar hàbit:**
```javascript
socket.emit('habit_action', {
  action: 'UPDATE',
  habit_id: 5,
  habit_data: {
    titol: 'Beure 2 litres d\'aigua',
    dificultat: 'media',
  }
});
```

**Exemple – Eliminar hàbit:**
```javascript
socket.emit('habit_action', {
  action: 'DELETE',
  habit_id: 5,
});
```

**Nota:** El `user_id` es presa del token JWT del socket. Si no hi ha token, s’usa `1` per defecte.

---

### `habit_completed`

**Què fa:** Marca un hàbit com completat avui. Envia acció `TOGGLE` a la cua, calcula XP, actualitza ratxes i registra l’activitat.

| Camp       | Tipus  | Obligatori | Descripció                                |
|------------|--------|------------|--------------------------------------------|
| `habit_id` | number | Sí         | ID de l’hàbit completat                    |
| `user_id`  | number | No         | ID de l’usuari (si no es passa, es pren del token) |
| `data`     | string | No         | Data ISO (ex: `"2025-02-24"`). Per defecte: avui |

**Exemple:**
```javascript
socket.emit('habit_completed', {
  habit_id: 3,
});
```

---

### `admin_join`

**Què fa:** Afegeix el socket a la sala d’admin per rebre confirmacions d’accions i altres esdeveniments d’admin.

| Camp      | Tipus  | Obligatori | Descripció                      |
|-----------|--------|------------|----------------------------------|
| `admin_id`| number | No         | ID de l’administrador. Per defecte: `1` |

**Exemple:**
```javascript
socket.emit('admin_join', { admin_id: 1 });
```

**Nota:** Cal executar `admin_join` abans d’enviar `admin_action` o de rebre `admin_action_confirmed`.

---

### `admin_action`

**Què fa:** Envia una acció CUD (Create, Update, Delete) sobre una entitat gestionada per l’admin. Les accions es processen per Laravel i el resultat es retorna via `admin_action_confirmed`.

| Camp      | Tipus  | Obligatori | Descripció                                                                 |
|-----------|--------|------------|-----------------------------------------------------------------------------|
| `action`  | string | Sí         | `"CREATE"`, `"UPDATE"` o `"DELETE"`                                         |
| `entity`  | string | Sí         | Tipus d’entitat: `plantilla`, `usuari`, `admin`, `habit`, `logro`, `missio` |
| `data`    | object | Sí         | Dades de l’entitat segons el tipus (veure exemples)                         |
| `admin_id`| number | No         | ID de l’admin (si no es passa, es pren de `admin_join`)                     |

**Dades per entitat:**

- **plantilla:** `{ creador_id?, titol, categoria?, es_publica? }` — UPDATE/DELETE: `{ id, titol?, categoria?, es_publica? }`
- **usuari:** `{ nom, email, contrasenya }` — UPDATE: `{ id, nom?, email?, contrasenya? }` — DELETE: `{ id }`
- **admin:** `{ nom, email, contrasenya }` — UPDATE: `{ id, nom?, email?, contrasenya? }` — DELETE: `{ id }` (no es pot eliminar admin 1)
- **habit:** `{ usuari_id?, plantilla_id?, categoria_id?, titol, dificultat?, frequencia_tipus?, dies_setmana?, objectiu_vegades? }` — UPDATE/DELETE: `{ id, titol?, dificultat?, ... }`
- **logro:** `{ nom, descripcio?, tipus? }` — UPDATE/DELETE: `{ id, nom?, descripcio?, tipus? }`
- **missio:** `{ titol }` — UPDATE/DELETE: `{ id, titol? }`

**Exemple – Crear plantilla:**
```javascript
socket.emit('admin_action', {
  action: 'CREATE',
  entity: 'plantilla',
  data: {
    titol: 'Nova plantilla',
    categoria: 'Salut',
    es_publica: true,
  }
});
```

**Exemple – Eliminar usuari:**
```javascript
socket.emit('admin_action', {
  action: 'DELETE',
  entity: 'usuari',
  data: { id: 10 },
});
```

---

### `admin:request_connected`

**Què fa:** Demana la llista d’usuaris connectats en temps real. La resposta arriba directament al mateix socket amb l’event `admin:connected_users`.

| Dades | Cap (payload buit) |
|-------|---------------------|

**Exemple:**
```javascript
socket.emit('admin:request_connected');

// Resposta rebuda amb:
socket.on('admin:connected_users', (llista) => {
  // llista = [{ user_id, nom, email, connected_at }, ...]
});
```

---

### `user_register`

**Què fa:** Registra l’usuari connectat a la llista interna d’usuaris connectats, visible per l’admin amb `admin:request_connected`. Cal cridar-ho quan l’usuari faci login o carregui l’app.

| Camp     | Tipus  | Obligatori | Descripció                          |
|----------|--------|------------|--------------------------------------|
| `user_id`| number/string | No  | ID de l’usuari. Si no es passa, es usa el `socket.id` |
| `nom`    | string | No         | Nom. Per defecte: `"Usuari"`         |
| `email`  | string | No         | Email de l’usuari                    |

**Exemple:**
```javascript
socket.emit('user_register', {
  user_id: 5,
  nom: 'Joan',
  email: 'joan@example.com',
});
```

---

## 2. Events que emet el backend (Backend → Frontend)

El frontend ha de subscriure’s a aquests events per rebre actualitzacions en temps real.

---

### `habit_action_confirmed`

**Què fa:** Confirma el resultat d’una acció d’hàbit (CREATE, UPDATE, DELETE, TOGGLE) processada per Laravel.

| Destinació | Sala `user_{user_id}` |
|------------|------------------------|
| Requeriment | Abans cal haver enviat alguna acció d’hàbit (el socket entra automàticament a `user_{id}`) |

| Camp     | Tipus   | Descripció                                  |
|----------|---------|----------------------------------------------|
| `action` | string  | `"CREATE"`, `"UPDATE"`, `"DELETE"` o `"TOGGLE"` |
| `habit`  | object  | Objecte de l’hàbit actualitzat o `null` si s’ha eliminat |
| `success`| boolean | Si l’operació s’ha completat correctament    |

**Exemple de listener:**
```javascript
socket.on('habit_action_confirmed', (payload) => {
  console.log('Acció confirmada:', payload.action, payload.habit, payload.success);
  // Actualitzar UI: afegir/editar/eliminar hàbit segons payload.action
});
```

---

### `update_xp`

**Què fa:** Envia l’actualització d’XP i ratxa després de completar un hàbit (TOGGLE).

| Destinació | Sala `user_{user_id}` |

| Camp            | Tipus  | Descripció                   |
|-----------------|--------|-------------------------------|
| `xp_total`      | number | XP total actual de l’usuari   |
| `ratxa_actual`  | number | Ratxa actual de dies consecutius |
| `ratxa_maxima`  | number | Millor ratxa fins ara         |

**Exemple de listener:**
```javascript
socket.on('update_xp', (payload) => {
  console.log('XP actualitzat:', payload);
  // Actualitzar barra d'XP, ratxa, etc.
});
```

---

### `admin_action_confirmed`

**Què fa:** Confirma el resultat d’una acció CUD d’admin (CREATE, UPDATE, DELETE) sobre plantilles, usuaris, admins, hàbits, logros o missions.

| Destinació | Sala `admin_{admin_id}` |
|------------|--------------------------|
| Requeriment | Cal haver fet `admin_join` abans |

| Camp     | Tipus   | Descripció                                 |
|----------|---------|---------------------------------------------|
| `admin_id` | number | ID de l’administrador                       |
| `entity` | string  | `plantilla`, `usuari`, `admin`, `habit`, `logro`, `missio` |
| `action` | string  | `"CREATE"`, `"UPDATE"` o `"DELETE"`         |
| `success`| boolean | Si l’operació s’ha completat correctament   |
| `data`   | object  | Entitat creada/actualitzada o `{ id }` en DELETE. Si hi ha error: `{ error: "missatge" }` |

**Exemple de listener:**
```javascript
socket.on('admin_action_confirmed', (payload) => {
  if (payload.success) {
    console.log('Admin:', payload.action, payload.entity, payload.data);
    // Actualitzar llista d'entitats a la UI
  } else {
    console.error('Error admin:', payload.data?.error);
  }
});
```

---

### `admin:connected_users`

**Què fa:** Retorna la llista d’usuaris connectats en resposta a `admin:request_connected`. S’envia directament al socket que va demanar-ho (no per sala).

| Camp | Tipus  | Descripció                             |
|------|--------|-----------------------------------------|
| Array| object[] | `[{ user_id, nom, email, connected_at }]` |

**Exemple de listener:**
```javascript
socket.on('admin:connected_users', (llista) => {
  console.log('Usuaris connectats:', llista);
  // Mostrar llista a la UI de l'admin
});
```

---

## 3. Sales (rooms)

El backend organitza els clients en sales per enviar esdeveniments només als que els hi correspon:

| Sala          | Qui hi entra                     | Events rebuts                                |
|---------------|-----------------------------------|----------------------------------------------|
| `user_{id}`   | Usuaris quan fan accions d’hàbits | `habit_action_confirmed`, `update_xp`         |
| `admin_{id}`  | Admins via `admin_join`           | `admin_action_confirmed`                      |

L’entrada a les sales es gestiona al backend: `user_{id}` en enviar `habit_action` o `habit_completed`, i `admin_{id}` en enviar `admin_join`.

---

## 4. Recomanacions d’ús

1. **Connexió i autenticació:** Connecta el socket amb el token JWT si el backend ho requereix. El `user_id` i `admin_id` es poden inferir del token.
2. **`user_register`:** Crida `user_register` després del login per aparèixer a la llista d’usuaris connectats.
3. **Admin:** Fes `admin_join` quan l’usuari accedeix al panell d’admin; així rebrà `admin_action_confirmed`.
4. **Desconnexió:** En `disconnect`, el backend elimina l’usuari de la llista de connectats si s’havia fet `user_register`.

---

## Resum ràpid

| Event (Frontend envia) | Dades principals                              | Resposta esperada                    |
|------------------------|-----------------------------------------------|--------------------------------------|
| `habit_action`         | `action`, `habit_id?`, `habit_data?`          | `habit_action_confirmed`             |
| `habit_completed`      | `habit_id`, `user_id?`, `data?`               | `habit_action_confirmed`, `update_xp` |
| `admin_join`           | `admin_id?`                                   | —                                    |
| `admin_action`         | `action`, `entity`, `data`, `admin_id?`       | `admin_action_confirmed`             |
| `admin:request_connected` | (cap)                                      | `admin:connected_users`              |
| `user_register`        | `user_id?`, `nom?`, `email?`                  | —                                    |

| Event (Frontend rep)     | Contingut típic                                         |
|--------------------------|----------------------------------------------------------|
| `habit_action_confirmed` | `{ action, habit, success }`                             |
| `update_xp`              | `{ xp_total, ratxa_actual, ratxa_maxima }`               |
| `admin_action_confirmed` | `{ admin_id, entity, action, success, data }`            |
| `admin:connected_users`  | `[{ user_id, nom, email, connected_at }, ...]`           |
