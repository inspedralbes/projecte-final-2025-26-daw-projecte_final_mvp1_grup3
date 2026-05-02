# Conexión entre Redis y Laravel en Loopy

Este documento explica detalladamente cómo se comunican **Laravel** y **Redis** en el ecosistema de Loopy, actuando Redis como el puente principal para el tiempo real entre el backend de negocio (Laravel) y el backend de sockets (Node.js).

---

## Arquitectura de Comunicación

La comunicación no es una "llamada" HTTP tradicional, sino un flujo basado en **Colas (Queues)** y **Publicación/Suscripción (Pub/Sub)**.

### 1. Flujo de Entrada: Node.js → Redis → Laravel (Colas)

Cuando un usuario realiza una acción en el frontend (ej. crear un hábito), la petición llega a Node.js por Sockets y este la deposita en Redis para que Laravel la procese.

*   **En Node.js:** Se usa `LPUSH` para insertar un JSON en la lista `habits_queue`.
*   **En Laravel (El Receptor):** Laravel no espera a que Redis le "llame". En su lugar, Laravel tiene un proceso activo llamado **Worker**.
    *   **Archivo:** `backend-laravel/app/Console/Commands/RedisWorker.php`
    *   **Comando:** `php artisan habits:redis-worker`
    *   **Mecanismo:** Utiliza el comando `BRPOP` (Blocking Right Pop). Este comando le dice a Redis: *"Dame el siguiente elemento de la lista 'habits_queue', y si está vacía, mantén mi conexión abierta y bloqueada hasta que llegue algo"*.
*   **Procesamiento:** Una vez que `BRPOP` recibe el dato, el Worker lo decodifica y llama al servicio correspondiente (ej. `HabitService`) para guardar en la base de datos PostgreSQL.

### 2. Flujo de Salida: Laravel → Redis → Node.js (Pub/Sub)

Una vez que Laravel ha terminado de procesar la acción (ej. el hábito ya está guardado en la DB), debe avisar al usuario.

*   **En Laravel (El Emisor):** Laravel utiliza un servicio dedicado para enviar el feedback.
    *   **Archivo:** `backend-laravel/app/Services/RedisFeedbackService.php`
    *   **Mecanismo:** Utiliza `Redis::publish('feedback_channel', json_encode($payload))`.
    *   **Acción:** Laravel envía un mensaje al canal `feedback_channel` de Redis. Es como una emisora de radio transmitiendo en una frecuencia específica.
*   **En Redis:** Redis recibe el mensaje y lo distribuye instantáneamente a todos los que estén "escuchando" ese canal.
*   **En Node.js:** Node.js tiene un suscriptor (`SUBSCRIBE`) escuchando ese mismo canal. Al recibir el mensaje de Laravel, Node lo reenvía al navegador del usuario mediante Socket.io.

---

## Resumen de Responsabilidades

| Componente | Acción | Comando Redis | Archivo Clave |
| :--- | :--- | :--- | :--- |
| **Laravel Worker** | Escuchar acciones pendientes | `BRPOP` | `RedisWorker.php` |
| **Laravel Service** | Notificar resultados | `PUBLISH` | `RedisFeedbackService.php` |
| **Node.js Producer** | Enviar acción a Laravel | `LPUSH` | `habitQueue.js` |
| **Node.js Subscriber** | Recibir feedback de Laravel | `SUBSCRIBE` | `feedbackSubscriber.js` |

---

## ¿Por qué hacerlo así?

1.  **Desacoplamiento:** Laravel no necesita saber nada de Sockets ni de Node.js. Solo lee y escribe en Redis.
2.  **Escalabilidad:** Podemos tener varios Workers de Laravel escuchando la misma cola si hay mucho tráfico.
3.  **Fiabilidad:** Si Laravel se cae, las acciones se acumulan en la cola de Redis y se procesan en cuanto Laravel vuelve a estar online (no se pierde ninguna acción).
4.  **Tiempo Real:** El sistema Pub/Sub de Redis es extremadamente rápido, permitiendo que la confirmación llegue al usuario en milisegundos.

## Comandos Útiles para Depuración

Si quieres ver qué está pasando dentro de Redis en tiempo real, puedes entrar al contenedor de Redis y ejecutar:

```bash
# Ver todos los mensajes que pasan por Redis
redis-cli monitor

# Ver cuántos elementos hay en la cola de hábitos
redis-cli llen habits_queue
```
