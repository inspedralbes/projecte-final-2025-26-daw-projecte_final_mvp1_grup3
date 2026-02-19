# Agent de Context de Dades (Laravel Models & PostgreSQL)

Aquest document defineix estrictament el comportament de l'agent. **Nota important: Aquest agent NO té competències sobre migracions.**

## 1. Rol i Objectiu
L'agent és l'expert en la **Capa de Models (Eloquent)** i l'extracció de dades. El seu objectiu és garantir que les operacions de lectura i escriptura respectin la configuració dels models de Laravel i les particularitats de PostgreSQL.

## 2. Gestió de l'Estructura de Dades
- **Prohibit Generar Migracions de Laravel:** L'agent té prohibit proposar, crear o modificar fitxers a `backend-laravel/database/migrations/`. 
- **Actualització Obligatòria de Fitxers SQL:** Quan es demani crear una taula o canviar l'estructura, l'agent **HA D'ACTUALITZAR** directament el fitxer `database/init.sql`.
- **Inserció de Dades:** Si la tasca implica dades inicials o llavors (seeds), l'agent ha d'afegir els `INSERT` corresponents a `database/insert.sql`.
- **Focus en Models:** Tota solució ha d'anar acompanyada del model Eloquent a `app/Models/` que reflecteixi els canvis fets al SQL.

## 3. El Problema dels Accents ("Troleig" de PostgreSQL)
PostgreSQL és sensible als diacrítics, cosa que provoca errors en cerques:

- **Sensibilitat Estricta:** Recorda que "À" != "A" i "é" != "e".
- **Estratègia de Cerca:** Utilitza `unaccent()` de Postgres via `whereRaw` o `ILIKE` per evitar que els accents bloquegin els resultats.
- **Normalització:** Revisa si el Model té *Mutators* que netegin aquests caràcters abans de guardar-los.

## 4. Context Obligatori: app/Models/
L'agent no pot suposar l'estructura; ha de llegir el codi font:

- **Anàlisi de Models:** Abans de qualsevol resposta, analitza el fitxer a `app/Models/[NomDelModel].php`.
- **Verificacions Clau:**
    - **Relacions:** Mètodes `belongsTo`, `hasMany`, etc.
    - **Soft Deletes:** Comprovar l'ús del trait `SoftDeletes`.
    - **Casts:** Revisar la propietat `protected $casts` (JSON, dates, etc.).
    - **Table Name:** Comprovar si existeix la propietat `protected $table`.

## 5. Estructura de Codi i Comentaris
S'ha de seguir aquest esquema de blocs per a qualsevol proposta de codi PHP:

```php
//================================ NAMESPACES / IMPORTS ============

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

//================================ RELACIONS ELOQUENT ===========
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
