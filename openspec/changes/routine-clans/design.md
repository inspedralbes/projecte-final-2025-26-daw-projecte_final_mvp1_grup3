## Context

El sistema actual incluye funcionalidad social básica (foro, amigos, chat privado) pero carece de grupos temáticos privados. Los clanes extienden esta funcionalidad permitiendo comunidades cerradas de 10-20 usuarios con intereses fitness compartidos.

**Constraints:**
- PostgreSQL 16 ya en uso para la base de datos principal
- Laravel 11 backend API
- Node.js Socket.io en puerto 3001 para tiempo real
- Frontend Nuxt 3 con diseño claymorphism existente
- Nivel mínimo 5 requerido para participar en clanes
- Moderación existente vía sistema REPORTS

**Stakeholders:** Usuarios que buscan grupos de accountability, instructores que crean comunidades temática

## Goals / Non-Goals

**Goals:**
- Crear y configurar clanes con privacidad diferenciada (público/privado)
- Gestionar membresía mediante solicitudes e invitaciones
- Chat en tiempo real exclusivo para miembros
- Compartición de hábitos y plantillas entre miembros
- Sistema de notificaciones en tiempo real
- Moderación de clanes por líder y admin

**Non-Goals:**
- Clanes anidados o jerárquicos
- Video/audio chat en clanes
- Algoritmos de matchmaking
- Sistema de roles avanzado (solo líder/miembro)

## Decisions

### 1. Arquitectura de Base de Datos: Tablas Separadas
**Decision:** Usar tablas independientes CLANS, CLAN_MEMBERS, CLAN_REQUESTS, CLAN_MESSAGES.
**Rationale:** Separa concerns claramente, consultas más eficientes que JSONB.
**Alternative Considered:** Usar tablas genéricas con tipo - rechazado por complejidad de queries.

### 2. Nivel Mínimo: Validación en Backend
**Decision:** Validar nivel >= 5 en controladores Laravel antes de cualquier acción.
**Rationale:** Garantiza consistencia independiente del frontend. Middleware existente puede validar JWT.
**Alternative Considered:** Validar solo en frontend - rechazado por seguridad.

### 3. Chat: Socket.io Separado del Chat Privado
**Decision:** Usar eventos separados (clan_message) del chat privado (private_message).
**Rationale:** Permite diferenciación clara de permisos y persistencia.
**Alternative Considered:** Reutilizar private_message - rechazado por confusión de permisos.

### 4. Invitaciones: Sistema Dual
**Decision:** Clanes públicos: invitación directa, Clanes privados: solicitud intermedia.
**Rationale:** Mantiene control del líder en privados mientras ofrece flexibilidad en públicos.
**Alternative Considered:** Un solo tipo de invitación - rechazado per requirement.

### 5. Compartición de Activos: Referencia por ID
**Decision:** Guardar habit_id y plantilla_id en CLAN_MESSAGES como referencias, no clones.
**Rationale:** Evita duplicación de datos, actualizaciones visibles automáticamente.
**Alternative Considered:** Clonar hábitos - rechazado per complexity.

### 6. Moderación: Disolución por Admin
**Decision:** Admin puede disolver cualquier clan desde AdminUsuariController.
**Rationale:** Extiende sistema de reportes existente. Necesario para contenido inapropiado.
**Alternative Considered:** Solo líder puede disolver - rejected per safety concerns.

## Risks / Trade-offs

**[Risk]** Expulsión de miembro elimina acceso pero no borra contenido compartido
→ **[Mitigation]** Chat persiste, miembro expulsado pierde acceso a canal

**[Risk]** Líder expulsado por admin deja clan huérfano
→ **[Mitigation]** Admin al disolver debe transferir liderazgo o disolver clan

**[Risk]**并发 solicitudes de entrada
→ **[Mitigation]** Validar max_membres antes de aceptar, database unique constraint

**[Risk]** Contenido inapropiado en chat de clan
→ **[Mitigation]** Sistema de reportes existente extensible a clanes

## Migration Plan

1. Añadir tablas CLANS, CLAN_MEMBERS, CLAN_REQUESTS, CLAN_MESSAGES a init.sql
2. Crear controladores Laravel (ClanController, ClanRequestController)
3. Añadir eventos Socket.io para clan_message y notificaciones
4. Crear componentes Vue en frontend
5. Proporcionar logs de errores primer día
6. Rollback: Eliminar tablas si errors críticos

## Open Questions

- ¿El líder puede transferir liderazgo voluntariamente?
- ¿Cuántos clanes puede crear un usuario?
- ¿Tiempo de expiración de solicitudes pendientes?