## Why

El sistema actual de chat privado depende de recarga de página o polling GET para visualizar nuevos mensajes, lo cual rompe la experiencia "soft design" del proyecto y genera carga innecesaria en el servidor Laravel. Los usuarios esperan experiencia tiempo real similar a WhatsApp/Telegram.

## What Changes

- Implementar WebSocket real-time para chat privado usando infraestructura Socket.io existente (puerto 3001)
- Implementar patrón Optimistic UI: mensaje aparece instantáneamente antes de confirmar servidor
- Añadir evento typing_status para indicador "escribiendo..."
- Scroll automático suave al recibir mensajes
- Validación de amistad via WebSocket antes de permitir entrada a sala
- Persistencia de mensajes cuando destinatario offline

## Capabilities

### New Capabilities

- **private-chat-websocket**: Sistema de mensajería privada en tiempo real via WebSocket, incluyendo join_chat, send_private_message, typing_status, optimistic UI update

### Modified Capabilities

- **private-chat**: Añadir requisito de typing status y optimistic UI a specs existentes (no cambia requisitos core, añade comportamiento adicional)

## Impact

- **Backend**: Node.js Socket.io handlers (socialHandlers.js)
- **Frontend**: ChatStore (Pinia), ChatWindow.vue
- **Database**: Tabla PRIVATE_MESSAGES con índice compuesto para historial rápido
- **API**: Endpoints existentes no cambian, comportamiento enhanced via sockets