## Why

Los usuarios actuales de la aplicación buscan comunidades cerradas para compartir objetivos fitness y mantenermutua responsabilidad. La funcionalidad de redes sociales existente (foro, amigos, chat privado) noaborda la necesidad de grupos temáticos más pequeños y focalizados. Los clanes permiten crear comunidades de10-20 usuarios con intereses comunes, fomentando la adherencia a rutinas compartidas.

## What Changes

- Nueva tabla CLANS con id, nom, categoria_id, es_public, max_membres, lider_id
- Nueva tabla CLAN_MEMBERS con clan_id, usuari_id, rol, data_unio
- Nueva tabla CLAN_REQUESTS para gestionar solicitudes e invitaciones
- Nueva tabla CLAN_MESSAGES para el chat de clan
- Nuevo endpoint API para gestión de clanes (crear, listar, configurar)
- Nuevo endpoint API para solicitudes (enviar, aceptar, rechazar)
- Nuevo endpoint API para expulsar miembros (solo líder)
- Nuevo endpoint API para invitación de usuarios externos
- Sistema de Socket.io para notificaciones en tiempo real
- Chat de clan en tiempo real mediante Socket.io
- Validación de nivel >= 5 para cualquier acción de clan
- Compartición de hábitos y plantillas en el chat del clan
- Componente Vue para configuración del clan
- Componente Vue para gestionar solicitudes (solo líder)
- Componente Vue para lista de miembros
- Componente Vue para modal de invitaciones
- Soporte para disolución de clanes por moderador

## Capabilities

### New Capabilities
- `clan-management`: Crear, configurar, listar y gestionar clanes con privacidad diferenciada
- `clan-chat`: Chat en tiempo real exclusivo para miembros del clan
- `clan-membership`: Solicitudes de entrada, invitaciones y gestión de miembros
- `clan-sharing`: Compartición de hábitos y plantillas entre miembros

### Modified Capabilities
- `user-auth`: Añadir restricción de nivel >= 5 para acceso a funcionalidades de социаль (ya existe spec)
- `reports`: Añadir clanes al sistema de reportes para moderación (ya existe spec)

## Impact

- Backend Laravel: Nuevos controladores (ClanController, ClanRequestController)
- Backend Node.js: Nuevos eventos Socket.io para chat de clan y notificaciones
- Frontend Nuxt 3: Nuevos componentes Vue para gestión de clanes
- Base de datos PostgreSQL: Nuevas tablas y relaciones
- Moderación: Capacidad de disolver clanes desde admin