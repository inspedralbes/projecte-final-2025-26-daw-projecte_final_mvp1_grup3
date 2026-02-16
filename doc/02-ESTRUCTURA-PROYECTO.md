# Estructura del proyecto Loopy: archivos, directorios y qué hace cada uno

Este documento describe **qué hay ahora mismo** en el repositorio: carpetas, archivos importantes y la función de cada uno. Sirve como mapa del proyecto para orientarse y para documentar la entrega.

---

## Vista general del árbol de directorios

En la raíz del proyecto encontrarás algo como:

```
projecte-final-2025-26-daw-projecte_final_mvp1_grup3/
├── .env.example          # Plantilla de variables de entorno (copiar a .env)
├── README.md             # Presentación del proyecto (Loopy, stack, instalación)
├── LICENSE               # Licencia del repositorio
├── backend-laravel/      # Backend de negocio (Laravel 11, PHP 8.3)
├── backend-node/         # Backend en tiempo real (Node.js, Socket.io)
├── frontend/             # Interfaz de usuario (Nuxt 3, Pinia, Tailwind)
├── database/             # Scripts SQL de inicialización y datos (PostgreSQL)
├── docker/               # Orquestación: docker-compose y Dockerfiles
└── doc/                  # Documentación (este archivo, setup, etc.)
```

A continuación se detalla cada parte.

---

## Raíz del proyecto

### `.env.example`

- **Qué es:** Plantilla de variables de entorno. No se ejecuta; se **copia** a `.env` para que Docker Compose y las aplicaciones lean las configuraciones reales.
- **Contenido típico:** Credenciales de PostgreSQL (`POSTGRES_USER`, `POSTGRES_PASSWORD`, `POSTGRES_DB`), host/puerto de Redis (`REDIS_HOST`, `REDIS_PORT`), secreto JWT compartido (`JWT_SECRET`), URLs para el frontend (`SOCKET_URL`, `API_URL`) y `APP_KEY` para Laravel.
- **Uso:** Ejecutar `cp .env.example .env` (o `Copy-Item .env.example .env` en PowerShell) y editar `.env` con los valores deseados. El archivo `.env` no debe subirse al repositorio (suele estar en `.gitignore`).

### `README.md`

- **Qué es:** README principal del proyecto. Presenta Loopy (app de hábitos gamificada), la propuesta de valor, el stack tecnológico, la tabla de versiones, un resumen de instalación con Docker y las variables de entorno.
- **Función:** Punto de entrada para cualquiera que abra el repo; enlaza con la documentación detallada (por ejemplo en `doc/`).

### `LICENSE`

- **Qué es:** Archivo de licencia del proyecto (por ejemplo MIT). Indica las condiciones de uso y redistribución del código.

---

## Carpeta `backend-laravel/`

Backend de **negocio**: API REST, base de datos, lógica de dominio. Versión: **Laravel 11** sobre **PHP 8.3**. Se comunica con Node y el frontend vía **Redis** (cola + pub/sub).

### Estructura dentro de `backend-laravel/`

```
backend-laravel/
├── app/
│   ├── Console/Commands/
│   │   └── RedisWorker.php      # Comando que consume habits_queue (BRPOP) y publica en feedback_channel
│   ├── Http/Controllers/
│   │   └── Controller.php       # Controlador base (vacío, para extender)
│   ├── Models/
│   │   └── User.php            # Modelo Eloquent User
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Proveedor de servicios de la aplicación
│   └── Services/
│       └── RedisFeedbackService.php  # Procesa payload y hace Redis::publish al canal feedback
├── bootstrap/
│   ├── app.php                 # Configuración de la aplicación Laravel (rutas, middleware, excepciones)
│   └── providers.php           # Lista de proveedores de servicios (AppServiceProvider)
├── config/
│   ├── app.php                 # Configuración general (nombre, entorno, locale, clave, etc.)
│   ├── cache.php               # Driver de caché (Redis)
│   ├── database.php            # Conexiones de BD (PostgreSQL por defecto)
│   ├── logging.php             # Canales de log (stack, single, stderr)
│   ├── queue.php               # Conexión de colas (Redis)
│   └── redis.php               # Conexiones Redis (default, cache)
├── database/
│   ├── factories/              # (vacío o .gitkeep) Fábricas para modelos
│   ├── migrations/
│   │   └── 0001_01_01_000000_create_users_table.php  # Migración que crea la tabla users
│   └── seeders/
│       └── DatabaseSeeder.php   # Seeder principal (vacío por defecto)
├── public/
│   └── index.php               # Punto de entrada HTTP (front controller)
├── resources/
│   └── views/
│       └── welcome.blade.php    # Vista de bienvenida simple (HTML)
├── routes/
│   ├── web.php                 # Rutas web (p. ej. GET / que devuelve JSON)
│   └── console.php             # Comandos Artisan y (opcional) tareas programadas
├── storage/                    # Logs, caché, sesiones, archivos subidos (subcarpetas con .gitkeep)
├── artisan                     # CLI de Laravel (ejecuta comandos Artisan)
├── composer.json               # Dependencias PHP (Laravel 11, predis, JWT, etc.)
└── .env.example                # Variables de entorno específicas de Laravel (DB, Redis, APP_KEY)
```

### Archivos clave de Laravel

| Archivo | Qué hace |
|--------|-----------|
| **app/Console/Commands/RedisWorker.php** | Comando `habits:redis-worker`. Hace **BRPOP** sobre la lista Redis `habits_queue` (timeout configurable). Por cada elemento recibido, decodifica el JSON, llama a `RedisFeedbackService::processAndPublish()` y publica en el canal `feedback_channel`. Así el frontend (vía Node y Socket.io) recibe la confirmación en tiempo real. |
| **app/Services/RedisFeedbackService.php** | Servicio que recibe el payload procesado, construye un objeto de feedback (evento, payload, fecha) y ejecuta **Redis::publish($channel, json_encode($feedback))**. Es el puente Laravel → Redis Pub/Sub → Node → frontend. |
| **config/database.php** | Define la conexión **pgsql** usando variables de entorno (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, etc.). |
| **config/redis.php** | Define conexiones Redis (default y cache) con host/puerto desde env. Laravel usa **predis** como cliente. |
| **routes/web.php** | Rutas web; por defecto una ruta GET `/` que devuelve un JSON de estado. Aquí se irán añadiendo las rutas de la API de negocio. |
| **routes/console.php** | Registro de comandos Artisan y (si se usa) tareas programadas. El worker Redis se ejecuta manualmente, no desde el scheduler. |

---

## Carpeta `backend-node/`

Backend de **tiempo real**: Socket.io, integración con Redis (cola y pub/sub), y en el futuro lógica con IA (Gemini). Versión: **Node.js 20.11.1**, **Socket.io 4.7.4**.

### Estructura dentro de `backend-node/`

```
backend-node/
├── src/
│   ├── index.js                # Servidor HTTP + Socket.io, enlace con feedbackSubscriber
│   ├── queues/
│   │   └── habitQueue.js       # LPUSH a la lista Redis habits_queue (Node → Laravel)
│   ├── subscribers/
│   │   └── feedbackSubscriber.js  # Suscripción a feedback_channel y reemisión a Socket.io
│   └── middleware/
│       └── jwtAuth.js          # Middleware JWT para Socket.io (handshake.auth.token o query.token)
└── package.json               # Dependencias: socket.io, @google/generative-ai, redis, jsonwebtoken
```

### Archivos clave de Node

| Archivo | Qué hace |
|--------|-----------|
| **src/index.js** | Crea un servidor HTTP que responde con JSON de estado y monta **Socket.io** sobre él. Carga el **feedbackSubscriber** y lo asocia al objeto `io` para que los mensajes del canal Redis `feedback_channel` se reenvíen a los clientes como evento `feedback`. Escucha conexiones/desconexiones de clientes. Puerto por defecto 3001. |
| **src/queues/habitQueue.js** | Cliente Redis (módulo `redis`). Expone **push(payload)** que hace **LPUSH** a la lista `habits_queue`. Esa lista la consume Laravel con BRPOP en `RedisWorker`. Así Node envía trabajos de hábitos al backend Laravel. |
| **src/subscribers/feedbackSubscriber.js** | Crea un cliente Redis solo para suscripciones. Se conecta y hace **subscribe** al canal `feedback_channel`. Por cada mensaje recibido, hace **JSON.parse** (o envía raw) y emite **io.emit('feedback', data)** para que todos los clientes Socket.io conectados reciban el feedback en tiempo real. |
| **src/middleware/jwtAuth.js** | Función middleware para Socket.io: lee el token JWT de `handshake.auth.token` o de `handshake.query.token`, lo verifica con **jsonwebtoken** usando `JWT_SECRET`, y asigna `socket.user` con el payload decodificado. Si no hay token o es inválido, llama a `next(new Error(...))`. |

---

## Carpeta `frontend/`

Interfaz de usuario: **Nuxt 3.10.1**, **Pinia 2.1.7**, **Tailwind CSS 3.4.1**, **socket.io-client** para tiempo real.

### Estructura dentro de `frontend/`

```
frontend/
├── app.vue                    # Componente raíz (layout + NuxtPage)
├── nuxt.config.ts             # Configuración Nuxt: módulos (Pinia, Tailwind), runtimeConfig (socketUrl, apiUrl)
├── package.json               # Dependencias: nuxt, pinia, socket.io-client, @nuxtjs/tailwindcss, tailwindcss
├── assets/
│   └── css/
│       └── main.css           # Importa @tailwind base/components/utilities
├── layouts/
│   └── default.vue            # Layout por defecto (cabecera "Loopy", slot para el contenido)
├── pages/
│   └── index.vue              # Página principal (Habit Loop, texto de bienvenida)
├── stores/
│   └── useHabitStore.js       # Store Pinia: state (habits, lastFeedback), getters (count), actions (setHabits, setLastFeedback, addHabit)
├── plugins/
│   └── socket.client.js       # Plugin cliente: conecta Socket.io al backend Node, escucha 'feedback' y actualiza useHabitStore
└── composables/
    └── useSnapshot.js         # useSnapshot(): take() hace snapshot del estado de hábitos, restore(snapshot) restaura
```

### Archivos clave del frontend

| Archivo | Qué hace |
|--------|-----------|
| **nuxt.config.ts** | Define módulos (**@pinia/nuxt**, **@nuxtjs/tailwindcss**), CSS global (`~/assets/css/main.css`) y **runtimeConfig.public** (`socketUrl`, `apiUrl`) para que el plugin y los componentes sepan la URL del Socket y de la API sin hardcodear. |
| **stores/useHabitStore.js** | Store Pinia **habit**: estado `habits` (array) y `lastFeedback` (último mensaje de feedback); getter `count`; acciones `setHabits`, `setLastFeedback`, `addHabit`. La lógica interna sigue estilo ES5 (var, function, for). |
| **plugins/socket.client.js** | Plugin que solo se ejecuta en el cliente. Usa **runtimeConfig.public.socketUrl** para conectar **socket.io-client** al backend Node. Escucha el evento **feedback** y actualiza el store con `useHabitStore().setLastFeedback(data)`. Expone `$socket` vía `provide`. |
| **composables/useSnapshot.js** | Composable que usa **useHabitStore()**. **take()** devuelve un array de snapshots (id, name, at) del estado actual de hábitos. **restore(snapshot)** vuelve a cargar el store con ese array. Útil para guardar/restaurar estado. |

---

## Carpeta `database/`

Scripts SQL para **PostgreSQL**. Pensados para ejecutarse en el contenedor de Postgres (init al crear el contenedor, insert manual si se desea).

### Archivos

| Archivo | Qué hace |
|--------|-----------|
| **init.sql** | Se monta en el contenedor Postgres como **/docker-entrypoint-initdb.d/init.sql**, por lo que se ejecuta **solo la primera vez** que se crea el volumen de datos. Por ahora está preparado con comentarios (extensiones, etc.); las tablas se crean con las **migraciones de Laravel** (`php artisan migrate`). |
| **insert.sql** | Plantilla para datos iniciales (por ejemplo seeds en SQL). No se ejecuta automáticamente; se puede lanzar a mano con `psql` o desde un script si se desea. |

---

## Carpeta `docker/`

Todo lo necesario para orquestar y construir los servicios con **Docker Compose** y **Dockerfiles**.

### Estructura

```
docker/
├── docker-compose.yml         # Definición de los 5 servicios y volúmenes
├── Dockerfile.laravel         # Imagen PHP 8.3.3 + Laravel (backend-laravel)
├── Dockerfile.node            # Imagen Node 20.11.1 (backend-node)
├── Dockerfile.nuxt            # Imagen Node 20.11.1 (frontend Nuxt)
├── entrypoint-laravel.sh      # Script de arranque Laravel: composer install si no hay vendor, luego php artisan serve
└── README.md                  # Instrucciones rápidas: up, worker, migrate, down
```

### docker-compose.yml

- **postgres:** Imagen **postgres:16.2-alpine**. Variables `POSTGRES_*` desde `.env`. Puerto 5432. Volumen persistente y montaje de **../database/init.sql** en **/docker-entrypoint-initdb.d/init.sql**. Healthcheck con `pg_isready`.
- **redis:** Imagen **redis:7.2.4-alpine**. Puerto 6379. Volumen para persistencia. Healthcheck con `redis-cli ping`.
- **backend-laravel:** Construido con **Dockerfile.laravel** (contexto raíz del proyecto). Monta **../backend-laravel** en `/var/www` y un volumen nombrado en **/var/www/vendor**. Variables de entorno: DB_*, REDIS_*, JWT_SECRET, CACHE_DRIVER, etc. Depende de postgres y redis con `condition: service_healthy`. Comando: `php artisan serve --host=0.0.0.0 --port=8000` (el entrypoint puede ejecutar antes `composer install`).
- **backend-node:** Construido con **Dockerfile.node** (contexto **../backend-node**). Monta el código y un volumen para **node_modules**. Variables: PORT, REDIS_HOST, JWT_SECRET, LARAVEL_URL. Depende de redis. Comando: `node src/index.js`. Puerto 3001.
- **frontend:** Construido con **Dockerfile.nuxt** (contexto **../frontend**). Monta código, node_modules y .nuxt en volúmenes. Variables: NUXT_HOST, NUXT_PORT, SOCKET_URL, API_URL. Depende de backend-node y backend-laravel. Comando: `npm run dev`. Puerto 3000.

**Volúmenes nombrados:** postgres_data, redis_data, laravel_vendor, node_modules, nuxt_node_modules, nuxt_dist (para no perder dependencias y build entre reinicios).

### Dockerfiles

| Archivo | Qué hace |
|--------|-----------|
| **Dockerfile.laravel** | Base **php:8.3.3-cli-alpine**. Instala extensiones (pdo_pgsql, zip, redis, etc.) y Composer. Copia **backend-laravel** y **docker/entrypoint-laravel.sh** a la imagen. Ejecuta `composer install`. Entrypoint: script que hace `composer install` si no existe `vendor/autoload.php` y luego `php artisan serve`. Así el volumen montado puede tener vendor vacío la primera vez y se rellena al arrancar. |
| **Dockerfile.node** | Base **node:20.11.1-alpine**. WORKDIR /app. Copia **package.json** (y lock si existe), ejecuta **npm ci** o **npm install**. Luego copia el resto del código. Comando por defecto: `node src/index.js`. |
| **Dockerfile.nuxt** | Base **node:20.11.1-alpine**. Misma idea: copia package.json, instala dependencias, copia el resto. Comando: `npm run dev` para desarrollo. |

### entrypoint-laravel.sh

- Comprueba si existe **/var/www/vendor/autoload.php**. Si no, ejecuta **composer install**.
- Luego ejecuta **php artisan serve --host=0.0.0.0 --port=8000** como proceso principal. Así, con el volumen de código montado y el volumen nombrado en `vendor`, la primera vez que arranca el contenedor se instalan dependencias sin tener que construir de nuevo la imagen.

### docker/README.md

- Resumen de cómo levantar el stack desde la raíz (`cp .env.example .env`, `cd docker`, `docker compose up -d --build`).
- Cómo ejecutar el worker Redis (`php artisan habits:redis-worker`).
- Cómo ejecutar migraciones (`php artisan migrate`).
- Cómo parar (`docker compose down`).

---

## Carpeta `doc/`

Documentación del proyecto.

| Archivo | Qué hace |
|--------|-----------|
| **README.md** | Lista mínima de documentación obligatoria (objectivos, arquitectura, entorn, desplegament, API, Android, etc.). Plantilla a completar. |
| **01-SETUP-DESDE-CERO.md** | Guía paso a paso para levantar el proyecto desde cero (requisitos, clonar, .env, docker compose, migraciones, worker, problemas frecuentes). |
| **02-ESTRUCTURA-PROYECTO.md** | Este documento: descripción de la estructura actual, archivos y directorios y qué hace cada uno. |

---

## Flujo de comunicación Redis (resumen)

- **Node → Laravel:** El backend Node usa **habitQueue.push(payload)** → **LPUSH** en la lista Redis **habits_queue**. Laravel no hace polling HTTP; consume esa cola.
- **Laravel → Node/Frontend:** El comando **habits:redis-worker** (Laravel) hace **BRPOP** de **habits_queue**, procesa el mensaje con **RedisFeedbackService** y hace **Redis::publish('feedback_channel', json_encode($feedback))**. El backend Node tiene un suscriptor a **feedback_channel** que recibe el mensaje y hace **io.emit('feedback', data)**. El frontend, con el plugin **socket.client.js**, escucha **feedback** y actualiza **useHabitStore** (lastFeedback).

Con esto tienes documentado qué hay en el proyecto y para qué sirve cada parte. Para ponerlo en marcha desde cero, usa **doc/01-SETUP-DESDE-CERO.md**.
