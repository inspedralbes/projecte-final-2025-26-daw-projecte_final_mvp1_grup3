# 🗄️ Agente de Capa de Datos (PostgreSQL & Laravel Models)

## 📋 Contexto del Proyecto
Este agente es el responsable de mantener la coherencia entre la base de datos **PostgreSQL 16** y los modelos de **Laravel 11**. La capa de datos es el corazón de "Loopy" y cualquier cambio aquí afecta tanto al worker de Redis como a la API REST.

## 🏗️ Estructura y Sincronización
### SQL (init.sql)
- El esquema se define en `database/init.sql`.
- **IMPORTANTE**: Los nombres de tablas y columnas deben ser consistentes (actualmente se usa MAYÚSCULAS en el SQL).

### Modelos Laravel (app/Models)
- Cada tabla debe tener su modelo correspondiente en Laravel.
- Los modelos deben definir explícitamente `$table` si el nombre de la tabla no sigue la convención de plural en inglés de Laravel (ej: `protected $table = 'USUARIS';`).
- Desactivar `$timestamps` si la tabla no tiene `created_at` y `updated_at`.

## ⚠️ Reglas Críticas: Acentos y Caracteres Especiales
> [!WARNING]
> **PROHIBIDO EL USO DE ACENTOS Y "Ñ"**: Los acentos y caracteres especiales en la base de datos (nombres de tablas, columnas o incluso datos de configuración inicial) provocan errores de codificación y comportamientos inesperados en las consultas.

- **Nombres de Tablas/Columnas**: Usar siempre ASCII estándar (ej: `RATXES` en lugar de `RACHAS`, `frequencia` en lugar de `frecuencia`).
- **Datos en SQL**: Evitar acentos en los `INSERT` iniciales del archivo `insert.sql`.
- **Modelos**: Asegurar que las propiedades `$fillable` coincidan exactamente con los nombres sin acento de la base de datos.

## 🛠️ Tareas del Agente
1. **Doble Validación**: Al modificar una tabla, verificar automáticamente si el modelo de Laravel necesita actualizarse.
2. **Control de Caracteres**: Escanear cualquier nueva migración o script SQL en busca de acentos o caracteres no ASCII.
3. **Mantenimiento**: Asegurar que las relaciones (Foreign Keys) estén correctamente definidas en Eloquent (`belongsTo`, `hasMany`).
