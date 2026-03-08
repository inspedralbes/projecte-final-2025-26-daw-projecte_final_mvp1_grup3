# Docker - Loopy

## Levantar el entorno

Desde la **raíz del proyecto**:

```bash
cp .env.example .env
# Editar .env y definir JWT_SECRET si se desea

cd docker
docker compose up -d --build
```

Servicios:

- **Frontend (Nuxt):** http://localhost:3000  
- **Backend Node (Socket.io):** http://localhost:3001  
- **Backend Laravel:** http://localhost:8000  
- **PostgreSQL:** localhost:5432 (usuario/DB según `.env`)  
- **Redis:** localhost:6379  

## Worker Redis (Laravel → feedback_channel)

El worker unificat s'inicia automàticament amb el servei `backend-laravel-redis-worker`. Processa totes les cues Redis (habits, plantilles, admin, ruleta) i publica a `feedback_channel`. Per a execució manual:

```bash
docker compose exec backend-laravel php artisan redis:unified-worker
```

## Base de datos

El script `../database/init.sql` se ejecuta al crear el contenedor de Postgres. Las tablas se crean con las migraciones de Laravel:

```bash
docker compose exec backend-laravel php artisan migrate
```

## Parar

```bash
cd docker
docker compose down
```
