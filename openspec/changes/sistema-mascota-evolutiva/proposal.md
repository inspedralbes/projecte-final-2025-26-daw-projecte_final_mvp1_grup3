## Why

La mascota evolutiva es el nucleo del feedback emocional de Loopy. Actualmente falta un companion digital que refleje visualmente el crecimiento personal del usuario, generando mayor apego emocional y motivacion para mantener sus habitos. Este sistema permitira que cada usuario tenga un monstruo unico que evoluciona segun su nivel.

## What Changes

- Pantalla de seleccion de huevo post-onboarding con 4 opciones de color de monstruo (verde, rosa, lila, amarillo)
- Sistema de evolucion por niveles (Bebe, Nino, Adolescente, Mamado)
- Nomenclatura de sprites MXY (X = color V/R/L/A, Y = inicial etapa B/N/A/M)
- Etapas: Bebe (1-5), Nino (6-15), Adolescente (16-30), Mamado (31+)
- Sprites ya existentes en /public/img/monster/
- Componente MonsterDisplay.vue que renderiza el sprite correcto segun tipo y nivel
- Animacion de evolucion al subir de nivel mediante EvolutionModal.vue
- Almacenamiento permanente del tipo de monstruo en la tabla USUARIS
- Sincronizacion en tiempo real del evento xp_updated para detectar cambios de etapa
- Visualizacion de mascota en perfiles publicos y privados

## Capabilities

### New Capabilities
- `monster-selector`: Interfaz de seleccion de huevo post-registro con 4 opciones de tipo
- `monster-evolution`: Logica de evolucion con 4 etapas (Bebe/Nino/Adolescente/Mamado)
- `monster-display`: Componente de renderizado de sprite con nomenclatura MXY
- `monster-realtime`: Sincronizacion de eventos de nivel para animaciones de evolucion

### Modified Capabilities
- `user-profile`: Extension para incluir monstre_tipus y datos de nacimiento del monstruo
- `xp-calculation`: Recalculo automatico de etapa de mascota al cambiar nivel

## Impact

- **Base de datos (PostgreSQL 16)**: Extension tabla USUARIS con columnas monstre_tipus y data_naixement_monstre
- **Backend (Laravel 11)**: Endpoint POST /api/user/monster-choice, actualizacion UserProfileReadController
- **Frontend (Nuxt 3)**: Componentes MonsterEggSelector.vue, MonsterDisplay.vue, EvolutionModal.vue
- **Assets**: Sprites ya en /public/img/monster/ con formato MXY.png
- **WebSocket**: Evento xp_updated para deteccion de cambios de etapa de evolucion