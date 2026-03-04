## Payload Redis para plantillas

### Cola
- `plantilles_queue`

### Formato (JSON)
```json
{
  "type": "PLANTILLA",
  "action": "CREATE",
  "user_id": 1,
  "plantilla_id": null,
  "plantilla_data": {
    "titol": "Rutina mañanas",
    "categoria": "Salud",
    "es_publica": false
  }
}
```

### Reglas por accion
- `CREATE`:
  - `plantilla_data` obligatorio
  - `plantilla_id` opcional o `null`
- `UPDATE`:
  - `plantilla_id` obligatorio
  - `plantilla_data` obligatorio
- `DELETE`:
  - `plantilla_id` obligatorio
  - `plantilla_data` opcional

### Campos
- `type`: siempre `PLANTILLA`
- `action`: `CREATE|UPDATE|DELETE`
- `user_id`: ID del usuario autenticado en el socket
- `plantilla_id`: ID de la plantilla (si aplica)
- `plantilla_data`:
  - `titol` (obligatorio en `CREATE`)
  - `categoria` (opcional)
  - `es_publica` (opcional, boolean)

## Feedback Redis (Laravel -> Node)

### Diferenciacion de entidad
Mantener `action` como `CREATE|UPDATE|DELETE` y anadir `type: "PLANTILLA"` para diferenciar de habitos.

### Formato (JSON)
```json
{
  "type": "PLANTILLA",
  "action": "UPDATE",
  "user_id": 1,
  "success": true,
  "plantilla": {
    "id": 123,
    "creador_id": 1,
    "titol": "Rutina mañanas",
    "categoria": "Salud",
    "es_publica": false
  }
}
```

## Frontend (socket)

### Emit
```js
socket.emit('plantilla_action', {
  action: 'CREATE',
  plantilla_data: {
    titol: 'Rutina mañanas',
    categoria: 'Salud',
    es_publica: false
  }
});
```

### Confirmacion
```js
socket.on('plantilla_action_confirmed', (payload) => {
  // payload: { type, action, success, plantilla }
});
```

### Acciones disponibles
- `CREATE`, `UPDATE`, `DELETE`

### Nota para frontend
- El backend procesa los cambios de plantillas de forma asincrona via Redis.
- Para refrescar listas, usar el feedback `plantilla_action_confirmed` o volver a consultar `GET /plantilles`.
