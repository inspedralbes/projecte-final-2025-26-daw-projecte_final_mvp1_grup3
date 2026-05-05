## Context

El proyecto usa Laravel 11 para API REST y Node.js Socket.io en puerto 3001 para tiempo real. El sistema de chat privado existente (/api/chat) funciona pero requiere polling. El objetivo es migrar a WebSocket para experiencia "soft design" fluida.

**Constraints:**
- PostgreSQL 16 para base de datos
- Laravel 11 backend API
- Node.js Socket.io en puerto 3001
- Frontend Nuxt 3 con diseño claymorphism
- WebSocket existente para clanes (socialHandlers.js)
- Redis para pub/sub entre Laravel y Node.js

**Stakeholders:** Usuarios que esperan mensajería tipo WhatsApp/Telegram

## Goals / Non-Goals

**Goals:**
- Mensajería privada en tiempo real via WebSocket
- Optimistic UI para mensaje instantáneo
- Indicador "escribiendo..."
- Scroll automático suave
- Validación de amistad en handshake WebSocket
- Persistencia offline

**Non-Goals:**
- Voz/video chat
- Cifrado de mensajes
- Notificaciones push
- Mensajería de grupo (solo 1:1)

## Decisions

### 1. WebSocket vs Long Polling: WebSocket
**Decision:** Usar WebSocket existente en lugar de implementar polling largo.
**Rationale:** Socket.io ya configurado para clanes, reutilizar infraestructura. Latencia mínima vs polling cada 2-5 segundos.
**Alternative Considered:** Polling largo - rechazado por rendimiento.

### 2. Validación de Amistad: En handshake WebSocket
**Decision:** Validar friendship en evento join_chat, no en cada mensaje.
**Rationale:** Una validación al entrar a sala es más eficiente que validar cada mensaje.
**Alternative Considered:** Validar cada mensaje - rechazado per overhead.

### 3. Persistencia de Mensajes: Laravel API + Redis
**Decision:** Enviar mensaje a través de Laravel API para persistencia, Node.js solo para tiempo real.
**Rationale:** Mantiene consistencia con arquitectura existente. Redis pub/sub para通知.
**Alternative Considered:** Node.js directo a PostgreSQL - rechazado per proyecto standards.

### 4. Optimistic UI: Frontend-first
**Decision:** Mostrar mensaje inmediatamente en UI, revertir si servidor rechaza.
**Rationale:** Experiencia nativa tipo app. Transición suave sin parpadeo.
**Alternative Considered:** Confirmación primero - rechazado per UX.

### 5. Room Naming: chat_{user_id}_{friend_id}
**Decision:** Usar IDs ordenados para room name.
**Rationale:** Ambos usuarios comparten misma sala sin importar quién inicia.
**Alternative Considered:** Salas individuales por usuario - rechazado per complejidad.

## Risks / Trade-offs

**[Risk]** WebSocket desconectado pierde mensajes
→ **[Mitigation]** Mensaje persiste en servidor antes de emitir, se entrega al reconectar

**[Risk]** Invalid friendship en WebSocket
→ **[Mitigation]** Validar en join y rechazar con error claro

**[Risk]** Doble persistencia (API + Socket)
→ **[Mitigung]** Usar proceso único: API persiste, Socket solo notifica

**[Risk]** Typing status spam
→ **[Mitigation]** Debounce 1 segundo mínimo

## Migration Plan

1. Añadir índice compuesto a PRIVATE_MESSAGES (sender_id, receiver_id)
2. Implementar handlers en socialHandlers.js
3. Actualizar ChatStore para WebSocket
4. Actualizar ChatWindow.vue con optimistic UI
5. Testear con usuario de prueba
6. Rollback: Mantener polling como fallback

## Open Questions

- ¿Timeout para typing indicator? (propuesto: 3 segundos)
- ¿Número máximo de reconnect attempts?
- ¿Mantener polling como fallback para fallback?