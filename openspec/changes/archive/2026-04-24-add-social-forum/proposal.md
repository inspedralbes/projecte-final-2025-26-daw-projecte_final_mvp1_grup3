## Why

Los usuarios de Loopy actualmente no tienen una forma de interactuar entre sí para compartir experiencias, objetivos o activos de rutina. Habilitar un módulo social fomentará el soporte entre pares y el intercambio de hábitos y plantillas, aumentando el engagement con la aplicación sin sistema de recompensas.

## What Changes

- Nueva página social con feed de publicaciones
- Sistema de publicaciones (posts) con opción de vincular hábitos o plantillas propias
- Sistema de comentarios con jerarquía limitada a 3 niveles de profundidad
- Sistema de likes para publicaciones y comentarios
- Preview e importación de hábitos compartidos (con configuración de días de la semana)
- Preview e importación de plantillas compartidas (selección manual de hábitos)
- Integración de WebSockets para notificaciones en tiempo real
- Sistema de reportes para moderación de contenido
- Límite de 20 hábitos activos por usuario también aplica a importaciones

## Capabilities

### New Capabilities
- `social-forum`: Sistema completo de foro comunitario con posts, comentarios, likes e importación de activos
- `social-realtime`: Notificaciones en tiempo real mediante WebSockets para interacciones sociales
- `social-moderation`: Sistema de reportes y auditoría para moderación de contenido

### Modified Capabilities
- Ninguno existente requiere modificación

## Impact

- **Backend (Laravel 11)**: Nuevos controladores SocialPostController y SocialCommentController, endpoints de importación
- **Base de datos (PostgreSQL 16)**: Tablas SOCIAL_POSTS, SOCIAL_COMMENTS, SOCIAL_LIKES, extension de REPORTS
- **Frontend (Nuxt 3)**: Componentes FeedSocial.vue, PostCard.vue, CommentTree.vue, AssetPreviewModal.vue, ImportWizard.vue
- **Servidor WebSocket (Node.js Puerto 3001)**: Eventos new_social_interaction para tiempo real
- **Servicios existentes**: Integration con HabitService para importaciones
