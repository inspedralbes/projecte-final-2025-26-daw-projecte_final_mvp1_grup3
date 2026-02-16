# Cómo levantar el proyecto Loopy desde cero

Este documento explica **paso a paso** cómo tener el proyecto funcionando en tu máquina desde un clon vacío, sin asumir que ya tienes nada instalado excepto **Git** y **Docker** (con Docker Compose).

---

## 1. Requisitos previos

### Qué necesitas tener instalado

- **Git**  
  Para clonar el repositorio.

- **Docker Desktop** (o Docker Engine + Docker Compose)  
  El proyecto entero se levanta con contenedores. No es obligatorio tener Node.js, PHP ni PostgreSQL instalados en tu sistema.

- **Editor de código** (opcional)  
  Por ejemplo VS Code o Cursor, para editar archivos y el `.env`.

### Comprobar que Docker funciona

Abre una terminal y ejecuta:

```bash
docker --version
docker compose version
```

Si ambos comandos muestran una versión, puedes seguir.

---

## 2. Obtener el código del proyecto

### Clonar el repositorio

Si aún no tienes el proyecto en tu equipo:

```bash
git clone <URL_DEL_REPOSITORIO> loopy
cd loopy
```

Si ya tienes la carpeta del proyecto, entra en ella:

```bash
cd ruta/donde/esta/el/proyecte-final-2025-26-daw-projecte_final_mvp1_grup3
```

La **raíz del proyecto** es la carpeta donde ves `backend-laravel`, `backend-node`, `frontend`, `docker`, `database` y el archivo `.env.example`.

---

## 3. Configurar las variables de entorno

Las variables de entorno (base de datos, Redis, JWT, etc.) se leen desde un archivo **`.env`** en la raíz del proyecto. Docker Compose usa ese archivo al levantar los contenedores.

### Crear el archivo `.env`

1. En la **raíz del proyecto** (donde está `.env.example`), copia la plantilla:

   ```bash
   cp .env.example .env
   ```

   En Windows (PowerShell):

   ```powershell
   Copy-Item .env.example .env
   ```

2. Abre el archivo **`.env`** con un editor de texto.

### Variables que debes revisar (y opcionalmente cambiar)

- **POSTGRES_USER**, **POSTGRES_PASSWORD**, **POSTGRES_DB**  
  Credenciales de PostgreSQL. Por defecto: usuario `loopy`, contraseña `loopy_secret`, base de datos `loopy_db`. Puedes dejarlas así en desarrollo o cambiarlas si quieres.

- **JWT_SECRET**  
  Clave secreta compartida entre Laravel y Node para firmar/verificar tokens JWT. **En producción debe ser una cadena larga y aleatoria.** En desarrollo puedes dejar la que viene en `.env.example` o poner una propia.

- **SOCKET_URL** y **API_URL**  
  En desarrollo, el navegador se conecta a tu máquina (localhost). Los valores por defecto suelen ser:
  - `SOCKET_URL=http://localhost:3001`
  - `API_URL=http://localhost:8000`  
  No hace falta cambiarlos si accedes a la app desde `http://localhost:3000`.

- **APP_KEY** (Laravel)  
  Se puede generar después del primer arranque (ver más abajo). Puedes dejarlo vacío al principio.

Guarda el archivo **`.env`** cuando termines.

---

## 4. Levantar todos los servicios con Docker

Todos los servicios (PostgreSQL, Redis, Laravel, Node, Nuxt) se levantan con Docker Compose desde la carpeta **`docker`**.

### Pasos

1. Abre una terminal y sitúate en la **raíz del proyecto**.

2. Entra en la carpeta `docker`:

   ```bash
   cd docker
   ```

3. Construye las imágenes y arranca los contenedores en segundo plano:

   ```bash
   docker compose up -d --build
   ```

   - **`--build`** fuerza a construir de nuevo las imágenes (Laravel, Node, Nuxt) por si ha cambiado código o dependencias.
   - **`-d`** hace que los contenedores queden en segundo plano.

4. La primera vez puede tardar varios minutos (descarga de imágenes base, instalación de dependencias, etc.). Cuando termine, deberías ver algo como:

   ```
   ✔ Container loopy-postgres  Started
   ✔ Container loopy-redis     Started
   ✔ Container loopy-laravel  Started
   ✔ Container loopy-node     Started
   ✔ Container loopy-nuxt     Started
   ```

### Comprobar que los contenedores están en marcha

```bash
docker compose ps
```

Todos los servicios deberían estar en estado **Up**.

---

## 5. URLs de la aplicación

Con todo levantado, puedes acceder a:

| Servicio        | URL                    | Descripción                          |
|-----------------|------------------------|--------------------------------------|
| **Frontend**    | http://localhost:3000  | Aplicación Nuxt (interfaz de usuario) |
| **Backend Node**| http://localhost:3001 | API en tiempo real (Socket.io)       |
| **Backend Laravel** | http://localhost:8000 | API REST (negocio, BD)            |
| **PostgreSQL**  | localhost:5432        | Base de datos (cliente SQL o IDE)    |
| **Redis**       | localhost:6379        | Colas y pub/sub (herramientas Redis) |

Abre en el navegador **http://localhost:3000** para ver el frontend y **http://localhost:8000** para comprobar que Laravel responde.

---

## 6. Base de datos: crear tablas (migraciones)

Laravel usa **migraciones** para crear las tablas en PostgreSQL. Solo hay que ejecutarlas una vez (o cuando se añadan nuevas migraciones).

Desde la **raíz del proyecto** (o desde `docker`):

```bash
docker compose exec backend-laravel php artisan migrate
```

Si pregunta por confirmación, escribe **yes**. Al acabar, las tablas (por ejemplo `users`, `migrations`) quedarán creadas en la base de datos `loopy_db`.

---

## 7. (Opcional) Clave de aplicación de Laravel

Si en el `.env` dejaste **APP_KEY** vacía, Laravel puede quejarse. Puedes generarla así:

```bash
docker compose exec backend-laravel php artisan key:generate
```

Esto actualiza el `.env` dentro del contenedor. Si tu `.env` está montado desde el host (por la configuración del proyecto), puede que tengas que copiar la clave generada al `.env` de tu carpeta local. En la configuración actual, el volumen monta `../backend-laravel` en el contenedor; si el `.env` que usa Laravel es el de la raíz del proyecto, asegúrate de que ese archivo tenga **APP_KEY** definida.

---

## 8. (Opcional) Worker Redis: cola hábitos → feedback al frontend

Para que Laravel **consuma la cola de hábitos** (lista Redis `habits_queue`) y envíe feedback al frontend por el canal Redis `feedback_channel`, hay que ejecutar el comando **worker** en un proceso aparte.

Abre **otra terminal**, ve a la carpeta `docker` y ejecuta:

```bash
docker compose exec backend-laravel php artisan habits:redis-worker
```

Este proceso queda escuchando indefinidamente. Para dejarlo en segundo plano en producción se usaría un gestor de procesos (supervisor, systemd, etc.). Para desarrollo, con dejarlo en una terminal es suficiente.

---

## 9. Resumen rápido (checklist)

- [ ] Tienes Git y Docker (y Docker Compose) instalados.
- [ ] Has clonado/abierto el proyecto y estás en la raíz.
- [ ] Has copiado `.env.example` a `.env` y revisado variables (JWT_SECRET, etc.).
- [ ] Has ejecutado `cd docker` y luego `docker compose up -d --build`.
- [ ] Has ejecutado `docker compose exec backend-laravel php artisan migrate`.
- [ ] (Opcional) Has generado `APP_KEY` con `php artisan key:generate`.
- [ ] (Opcional) Tienes en marcha el worker con `php artisan habits:redis-worker`.

Frontend: **http://localhost:3000**  
Laravel: **http://localhost:8000**  
Node: **http://localhost:3001**

---

## 10. Parar el proyecto

Desde la carpeta **`docker`**:

```bash
docker compose down
```

Los volúmenes (datos de PostgreSQL y Redis) se conservan. Para volver a arrancar:

```bash
docker compose up -d
```

No hace falta `--build` cada vez; solo si cambias Dockerfile o dependencias.

---

## 11. Problemas frecuentes

### Los contenedores no arrancan o fallan

- Revisa que el archivo **`.env`** esté en la **raíz del proyecto** (no dentro de `docker`).
- Comprueba que los puertos **3000, 3001, 8000, 5432, 6379** no estén ya en uso por otra aplicación.

### Laravel da error de "APP_KEY" o "No application encryption key"

Ejecuta:

```bash
docker compose exec backend-laravel php artisan key:generate
```

y, si hace falta, copia la línea `APP_KEY=...` al `.env` de tu máquina.

### No se crean las tablas o hay error de conexión a la base de datos

- Asegúrate de que los valores de **POSTGRES_*** en `.env` coincidan con los que usa el servicio `postgres` en `docker-compose.yml` (por defecto usuario `loopy`, base `loopy_db`).
- Espera a que PostgreSQL esté listo (healthcheck) antes de ejecutar migraciones; si acabas de hacer `up`, espera unos segundos y vuelve a lanzar `php artisan migrate`.

### El frontend no conecta con Socket.io o con la API

- Comprueba que **SOCKET_URL** y **API_URL** en `.env` apunten a **localhost** y a los puertos **3001** y **8000** cuando uses el navegador en tu máquina.
- Asegúrate de que los contenedores **backend-node** y **backend-laravel** estén en estado **Up** (`docker compose ps`).

Si sigues estos pasos, tendrás el proyecto levantado desde cero y listo para desarrollar.
