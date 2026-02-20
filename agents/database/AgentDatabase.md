# 🗄️ Agent de Capa de Dades (PostgreSQL & Laravel Models)

Aquest document defineix estrictament el comportament de l'agent per a la gestió de la base de dades i els models.

## 📋 Context del Projecte
L'agent és l'expert en la **Capa de Dades (SQL)** i la **Capa de Models (Eloquent)**. El seu objectiu és garantir la coherència entre PostgreSQL 16 i Laravel 11, assegurant que les operacions de lectura i escriptura respectin la configuració del sistema.

## 🏗️ Estructura i Sincronització
- **Actualització Directa de SQL:** Quan es demani crear una taula o canviar l'estructura, l'agent **HA D'ACTUALITZAR** directament el fitxer `database/init.sql`.
- **Noms en SQL:** Els noms de taules i columnes han de ser consistents (actualment s'usa MAJORÚSCULES al SQL).
- **Prohibit Generar Migracions:** L'agent té prohibit proposar, crear o modificar fitxers a `backend-laravel/database/migrations/`.
- **Inserció de Dades:** Si la tasca implica dades inicials, l'agent ha d'afegir els `INSERT` corresponents a `database/insert.sql`.

## ⚠️ Regles Crítiques: Accents i Caràcters Especials
> [!WARNING]
> **PROHIBIT L'ÚS D'ACCENTS I "Ñ"**: Els accents i caràcters especials en la base de dades provoquen errors de codificació i comportaments inesperats.

- **Dades en SQL:** Evitar accents en els `INSERT` inicials i en els noms de columnes/taules (ex: `RATXES` en lloc de `RACHAS`).
- **PostgreSQL i Diacrítics:** Recorda que "À" != "A". Utilitza `unaccent()` o `ILIKE` si cal fer cerques sensibles.

## 🛠️ Context Obligatori: app/Models/
- **Models Laravel:** Cada taula ha de tenir el seu model a `app/Models/`.
- **Configuració Manual:** Defineix explícitament `$table` si el nom no és l'estàndard de Laravel i desactiva `$timestamps` si no existeixen les columnes `created_at/updated_at`.
- **Anàlisi previa:** Abans de qualezvol resposta, analitza el fitxer a `app/Models/[NomDelModel].php` per verificar relacions (`belongsTo`, `hasMany`), `casts` i `SoftDeletes`.

## 📜 Estructura de Codi (PHP)
S'ha de seguir aquest esquema de blocs per a qualezvol proposta de codi PHP als models:

```php
//================================ NAMESPACES / IMPORTS ============

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

//================================ RELACIONS ELOQUENT ===========
```
