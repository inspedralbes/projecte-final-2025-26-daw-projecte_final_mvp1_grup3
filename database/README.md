# Base de dades - Loopy

## Inicialització

### Opció 1: Docker (primera vegada)

En arrencar per primera vegada amb `docker compose up`, PostgreSQL executa automàticament:

1. `01-init.sql` – crea les taules
2. `02-insert.sql` – insereix les dades inicials

### Opció 2: Base de dades ja existent

Si el contenidor de Postgres ja s'havia creat abans (el volum existeix), els scripts d'inicialització **no** es tornen a executar. En aquest cas:

**A) Reiniciar des de zero (es perd tot):**

```bash
docker compose down -v
docker compose up -d
```

**B) Executar només els inserts amb Laravel:**

```bash
docker compose exec backend-laravel php artisan db:seed
```

## Troubleshooting

Si la base de dades **no s’ha creat bé** (taules incompletes, contrasenyes sense hashear, errors de FK):

1. Atura els contenidors i **elimina el volum** (perquè PostgreSQL torni a executar els scripts):
   ```bash
   docker compose -f docker/docker-compose.yml down -v
   docker compose -f docker/docker-compose.yml up -d
   ```
2. Els scripts `init.sql` i `insert.sql` només s’executen la primera vegada que es crea el volum. Amb `-v` esborres el volum i es torna a executar tot des de zero.

## Estructura

| Fitxer     | Descripció                    |
|-----------|--------------------------------|
| `init.sql` | Crea les taules (estructura)  |
| `insert.sql` | Dades inicials (usuaris, hàbits, etc.) |
