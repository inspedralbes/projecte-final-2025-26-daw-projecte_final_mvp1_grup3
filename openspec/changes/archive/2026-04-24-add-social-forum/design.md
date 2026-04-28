## Context

Loopy es una aplicación de seguimiento de hábitos con gamificación. Los usuarios no tienen forma de interactuar entre sí para compartir éxitos, estrategias o activos (hábitos/plantillas). El módulo social busca fomentar comunidad sin recompensas XP/monedas.

**Stack actual:**
- Backend: Laravel 11 (PHP 8.x) + PostgreSQL 16
- Servidor real-time: Node.js + Socket.IO (puerto 3001)
- Frontend: Nuxt 3 + Pinia + Socket.IO client

**Servicios existentes a reutilitzar:**
- `HabitService`: CRUD d'hàbits, mètode `exportarHabitsDePlantilla()` (línia 1165)
- `PlantillaService`: CRUD de plantilles
- WebSocket: Gestors existents a `backend-node/src/handlers/user/`

## Goals / Non-Goals

**Goals:**
- Implementar el módulo social con Posts, Comentarios (3 niveles), Likes
- Habilitar importación de hábitos compartidos (con configuración de dies_setmana)
- Habilitar importación de plantillas compartidas (reutilizar `exportarHabitsDePlantilla`)
- Notificaciones real-time via Socket.IO
- Sistema de reportes para moderación

**Non-Goals:**
- Gamificación social (sin XP ni monedas por interacciones)
- chat en tiempo real, solo notificaciones asíncronas
- Eliminación de funcionalidades existentes

## Decisions

### 1. Modelo de datos social
Se crean 3 tablas nuevas en PostgreSQL: `SOCIAL_POSTS`, `SOCIAL_COMMENTS`, `SOCIAL_LIKES`.
- Justificación: Separación clara de responsabilidades, consultas optimizadas por índice.

### 2. Profundidad de comentarios limitada a 3 niveles
El campo `depth_level` (SmallInt) en `SOCIAL_COMMENTS` restringirá la respuesta.
- Justificación: Simplifica UI, evita anidamiento excesivo.
- Validation: `store()` en `SocialCommentController` verifica `parent.depth_level < 3`.

### 3. Reutilización de importación existente
La importación de plantillas del foro reutilitza `HabitService::exportarHabitsDePlantilla()`.
- Justificación: DRY, mantiene validaciones de negocio existentes (límite 20 hábitos).
- Para hábitos individuales: se llama a `crearHabit()` con los datos del post.
- El frontend mostra ImportWizard només para seleccionar dies_setmana per cada hábit.

### 4. WebSockets para real-time
El servidor Node.js (puerto 3001) emitirá eventos `new_social_interaction`.
- Rooms: `user_{id}` per a notificar a l'autor interessat.
- Events: `social_comment`, `social_like` per a actualitzar contadors.
- Justificación: Manté l'arquitectura existent.

### 5. Moderación vía tabla REPORTS existent
Los reportes se guardan en la taula `REPORTS` existent (extendida).
- Justificación: Taula ja existeix per a reports generals.
-ADMIN_LOGS: Registro a `ADMIN_LOGS` amb JSONB per a auditoría.

## Risks / Trade-offs

- **[Risk] Límite de 20 hábitos**: La importación desde el foro debe validar contra el límite global.
  - *Mitigation*: Consultar `UsuariHabit::where('usuari_id', $userId)->count()` abans d'importar.

- **[Risk] Cascade delete**: Si un post con hábito associat s'elimina, els imports anteriors s'han de mantenir.
  - *Mitigation*: Els hábitos importats ja sunt независими copies independents (no foreign key).

- **[Risk] WebSocket performance**: Moltes notificcions poden saturar el server.
  - *Mitigation*: Limitar a eventos de nova interacció, no typing indicators.

## Migration Plan

1. **Migració DB**: Crear taules `SOCIAL_POSTS`, `SOCIAL_COMMENTS`, `SOCIAL_LIKES` (Laravel migrations)
2. **API Endpoints**: Afegir rutes a `api.php`, implementar controladors
3. **Socket Events**: Afegir gestors a `backend-node/src/handlers/user/`
4. **Frontend Components**: Desenvoluupar UI pas a pas
5. **Rollback**: Eliminar migrations si cal rollback

## Open Questions

1. Cal afegir paginació al feed social? (Quedara fora del MVP inicial, scroll infinit)
2. Els comentaris es mostren ordenats per data o per likes? (Per defecte: data, pots canviar)