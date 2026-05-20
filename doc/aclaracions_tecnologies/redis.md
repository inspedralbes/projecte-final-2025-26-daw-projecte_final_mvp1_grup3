# Redis — Aclaracions tècniques (Loopy)

> Pont asíncron entre Node.js i Laravel: cues de treball i Pub/Sub de feedback.

---

## 1. Què és Redis al projecte

| Ús | Mecanisme Redis | Qui l'utilitza |
| :--- | :--- | :--- |
| **Cues CUD** | Llistes + `LPUSH` / `BRPOP` | Node → Laravel |
| **Feedback temps real** | Pub/Sub `feedback_channel` | Laravel → Node → Frontend |
| **Cache Laravel** | Key-value | Laravel (`CACHE_DRIVER=redis`) |
| **Sessions Laravel** | Key-value | Laravel (`SESSION_DRIVER=redis`) |

**Versió:** Redis 7 (Docker, port host `6380` → `6379` intern)

---

## 2. Model d'arquitectura: Message Bus

Redis actua com a **bus de missatges** entre dos backends que no es criden per HTTP:

```text
Productor (Node)  ──LPUSH──►  [Queue]  ◄──BRPOP──  Consumidor (Laravel Worker)
Emissor (Laravel) ──PUBLISH► [Channel] ◄──SUBSCRIBE── Receptor (Node)
```

Això implementa un **CQRS lleuger** i **event-driven architecture** sense Kafka/RabbitMQ (adequat per MVP).

---

## 3. Cues (Node → Laravel)

| Cua | Accions típiques |
| :--- | :--- |
| `habits_queue` | Crear, editar, eliminar, completar hàbits |
| `plantilles_queue` | CRUD plantilles |
| `admin_queue` | Accions d'administració |
| `roulette_queue` | Tirada de ruleta diària |
| `snapshot_queue` | Snapshots de calendari (si actiu) |

### Anada: LPUSH

```javascript
// Node (habitQueuePublisher)
await redisClient.lpush('habits_queue', JSON.stringify(payload));
```

Payload inclou: `action`, `user_id`, dades de l'hàbit.

### Processament: BRPOP multillista

```text
php artisan redis:unified-worker
```

`UnifiedRedisWorker.php` escolta **totes** les cues amb `BRPOP` bloquejant. Quan arriba un missatge, despatxa al handler corresponent (`HabitQueueHandler`, etc.).

**Per què BRPOP?** El worker espera sense consum CPU; quan Node envia, es desperta immediatament.

---

## 4. Pub/Sub (Laravel → Node)

| Element | Valor |
| :--- | :--- |
| **Canal** | `feedback_channel` |
| **Publicador** | `RedisFeedbackService.php` |
| **Subscriptor** | `feedbackSubscriber.js` |

Flux:

1. Laravel processa l'acció i escriu a PostgreSQL
2. `Redis::publish('feedback_channel', json_encode($payload))`
3. Node rep el missatge i fa `io.to('user_X').emit('feedback', data)`

---

## 5. Per què aquest disseny?

| Benefici | Explicació |
| :--- | :--- |
| **Desacoblament** | Laravel no coneix Socket.io; Node no coneix Eloquent |
| **Resiliència** | Si Laravel reinicia, les tasques queden a la cua |
| **Escalabilitat** | Es poden executar diversos workers |
| **Velocitat** | Pub/Sub és de baixa latència per confirmacions UI |

---

## 6. Com es relaciona amb Docker

El servei `redis` al `docker-compose.yml` exposa el port i comparteix xarxa amb `backend-laravel` i `backend-node`.

Variables típiques al `.env`:

```env
REDIS_HOST=redis
REDIS_PORT=6379
```

---

## 7. Depuració

```bash
# Dins el contenidor Redis
redis-cli monitor
redis-cli llen habits_queue
redis-cli subscribe feedback_channel
```

---

## 8. Preguntes freqüents dels professors

**P: Redis substitueix PostgreSQL?**  
R: No. Redis és **temporal** (cues i missatges). La font de veritat és PostgreSQL.

**P: Què passa si Redis cau?**  
R: No es processen CUD asíncrons; GET per Laravel segueix. Cal Redis per al flux complet d'hàbits en viu.

**P: Per què no RabbitMQ?**  
R: Redis ja el necessitàvem per cache/sessions Laravel; reutilitzar-lo simplifica infraestructura del projecte educatiu.

**P: Qui consumeix les cues?**  
R: Només Laravel (`redis:unified-worker`). Node només produeix i subscriu feedback.

---

## 9. Referències internes

- `doc/arquitectura_backend/CONEXION-REDIS-LARAVEL.md`
- `.cursor/rules/redis-bridge.mdc`
- [node.md](./node.md), [laravel.md](./laravel.md), [sockets.md](./sockets.md)
