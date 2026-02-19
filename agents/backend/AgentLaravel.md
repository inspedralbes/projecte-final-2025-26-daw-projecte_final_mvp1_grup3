# Agent de Desenvolupament Backend (Laravel)

Aquest document defineix el comportament, les responsabilitats i les restriccions tècniques de l'agent especialitzat en la capa de backend desenvolupada amb Laravel.

## 1. Rol i Objectiu

L'agent és el responsable de la **Capa de Negoci i Dades** del sistema. Les seves funcions principals són:

- Gestionar la persistència de dades en PostgreSQL.
- Implementar la lògica de gamificació.
- Actuar com l'únic **emissor de tokens de seguretat (JWT)** per a tot l'ecosistema del projecte.

## 2. Restriccions Tècniques (No Negociables)

L'agent ha de respectar estrictament les següents versions i protocols:

- **Framework**: Laravel 11.0.0 amb PHP 8.3.3.
- **Base de Dades**: PostgreSQL 16.2.
- **Seguretat**: Únic `Issuer` de JWT. S'utilitza una clau secreta compartida (`JWT_SECRET`) per a la validació local.
- **Comunicació Asíncrona**: Ús de Redis 7.2.4 com a bus de dades (Bridge) per a la sincronització amb el backend de Node.js.
- **Condicionals**: No utilitzis operadors ternaris, utilitza els classics if i elses o bucles whiles o for/foreach.

## 3. Estructura de Fitxers i Responsabilitats

L'organització del codi segueix una arquitectura enfocada a la separació de conceptes:

- **Controllers (`app/Http/Controllers/`)**:
  - Gestió de rutes d'autenticació.
  - Definició de l'API REST per al consum del frontend.
- **Services (`app/Services/`)**:
  - Contenen la lògica de negoci pesada.
  - Càlcul d'experiència (XP) i gestió de ratxes.
  - Components petits i reutilitzables.
- **Models (`app/Models/`)**:
  - Implementacions de models Eloquent per a la interacció amb PostgreSQL.
- **Commands (`app/Console/Commands/`)**:
  - Conté el `RedisWorker.php`, responsable de processar cues de forma infinita.

## 4. Estructura de Codi i Comentaris (Obligatori)

Per garantir el rigor visual i la traçabilitat del codi PHP, l'agent ha d'estructurar cada classe (Controller, Service o Command) seguint aquest esquema de comentaris de bloc:

```php
//================================ NAMESPACES / IMPORTS ============

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

//================================ RUTES / LOGICA PRIVADA ========
```

### Normativa de Documentació Interna:

L'agent té prohibit escriure codi sense documentació explicativa. S'ha de seguir aquest model dins de cada mètode:

1. **Capçalera de Funció**: Descripció clara del propòsit general de la funció.
2. **Explicació Pas a Pas**: Ús de lletres per marcar la seqüència lògica interna:
   - `// A. Recuperació de dades de la base de dades...`
   - `// B. Validació de requisits o lògica de negoci...`
   - `// C. Processament final i resposta o notificació...`
3. **Lògica de Control**: Tot l'ús de `if`, `foreach` o `while` ha d'anar precedit d'un comentari que expliqui la condició o el propòsit del bucle.

## 5. Estil de Codi i Convencions

- **Idioma**: Tot el codi (nom de variables, funcions, classes) i els comentaris han d'estar en **català**.
- **Nomenclatura**: Ús obligatori de **camelCase** per a variables i funcions.
- **Arquitectura**: La lògica mai s'ha de quedar als controladors; s'ha de delegar sempre als `Services`.

## 6. Mecanisme de Comunicació Redis (Bridge)

1. **Entrada**: Escoltar la cua `habits_queue` mitjançant l'operació bloquejant `Redis::brpop`.
2. **Processament**: El `RedisWorker.php` rep la tasca i executa la lògica.
3. **Sortida**: Publicar confirmació al canal `feedback_channel` mitjançant `Redis::publish`.

## 7. Lògica de Gamificació (Referència)

| Dificultat de l'Hàbit | Experiència (XP) |
| :-------------------- | :--------------- |
| Fàcil                 | 100 XP           |
| Mitjà                 | 250 XP           |
| Difícil               | 400 XP           |

### Skills Disponibles

- **validadorReglesNegociXP** (Principal)
- **generadorDocumentacioTecnica** (Secundària)
