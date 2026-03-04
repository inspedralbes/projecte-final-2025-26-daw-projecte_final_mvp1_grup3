# Checklist de funcionalidades (doc vs app)

Leyenda:
- [x] HECHA
- [ ] FALTA
- [x] DIFERENTE (implementada, pero no igual que la doc)
- [x] NUEVA (no aparece en la doc)

## 4.1 Onboarding de usuario y registro
- [ ] (FALTA) Chat IA con preguntas abiertas (Gemini).
  - **JSON sockets/Redis:** N/A (no implementado).
  - **Por qué falta:** no hay flujo IA ni endpoint Gemini.
  - **Óptimo:** Front `Registre` → `POST /api/onboarding/ia` (Laravel) → Node/Gemini → Laravel guarda propuesta.
  - **Archivos a tocar:** `backend-node/src` (Gemini), `backend-laravel/routes/api.php`, `backend-laravel/app/Http/Controllers/Api/OnboardingController.php`, `frontend/pages/Registre.vue`.
- [ ] (FALTA) IA genera 3–5 hábitos concretos.
  - **JSON sockets/Redis:** N/A (no implementado).
  - **Por qué falta:** no existe servicio Gemini ni payload de hábitos.
  - **Óptimo:** respuesta JSON con `habits[]` → front muestra lista editable.
  - **Archivos:** `backend-node/src` (Gemini), `backend-laravel/app/Services/OnboardingService.php` (nuevo), `frontend/pages/Registre.vue`.
- [ ] (FALTA) Selección de mascota (nombre y color).
  - **JSON sockets/Redis:** N/A (no implementado).
  - **Por qué falta:** no hay modelo/tabla ni UI de mascota.
  - **Óptimo:** tabla `mascotes` o campos en `usuaris` y UI en registro/perfil.
  - **Archivos:** `database/init.sql`, `database/insert.sql`, `frontend/pages/Registre.vue`, `frontend/pages/perfil.vue`.
- [ ] (FALTA) Validar/editar hábitos propuestos por la IA.
  - **JSON sockets/Redis (óptimo):** Front `{"action":"CREATE","habit_data":{...}}` → Redis `{"action":"CREATE","user_id":1,"habit_data":{...}}` → feedback `{"action":"CREATE","success":true,"habit":{...}}`.
  - **Por qué falta:** no hay propuesta IA.
  - **Óptimo:** UI editable + confirmación → socket CUD.
  - **Archivos:** `frontend/pages/Registre.vue`, `backend-node/src/socketHandler.js`, `backend-laravel/app/Services/HabitService.php`.
- [x] (HECHA) Registro y creación de cuenta.
  - **JSON sockets/Redis:** N/A (API directa).
  - **Flujo:** `frontend/pages/Registre.vue` → `POST /api/auth/register` → `backend-laravel/app/Http/Controllers/Api/AuthController.php`.
  - **Read/JSON:** respuesta `{ token, user }` guardada en localStorage.
- [x] (NUEVA) Onboarding por cuestionario de categorías (sin IA).
  - **Read JSON:** `GET /api/onboarding/questions` → `{"success":true,"preguntes":[...]}`.
  - **Flujo:** `frontend/pages/Registre.vue` → `GET /api/onboarding/questions` (`OnboardingController`) y `GET /api/preguntes-registre/{categoria}`.

## 4.2 Gestión y cumplimiento de hábitos
- [x] (HECHA) Creación manual con título, meta numérica, unidad, categoría, dificultad y color.
  - **JSON Front→Node:** `{"action":"CREATE","habit_data":{"titol":"...","dificultat":"facil","frequencia_tipus":"diaria","dies_setmana":[true,false,...],"objectiu_vegades":3,"unitat":"vegades"}}`
  - **JSON Node→Redis:** `{"action":"CREATE","user_id":1,"habit_id":null,"habit_data":{...}}`
  - **JSON Redis→Node:** `{"action":"CREATE","success":true,"habit":{...}}`
- [x] (HECHA) El backend filtra y solo devuelve los hábitos del día actual según `dies_setmana`.
  - **Read JSON:** `GET /api/habits` → `[{"id":1,"titol":"...","objectiu_vegades":3,"unitat":"vegades","completat":false}]`
- [x] (HECHA) Asignación de plantilla para añadir hábitos en bloque desde el perfil de usuario.
  - **JSON sockets/Redis (óptimo):** Front `{"action":"ASSIGN","plantilla_id":5}` → Redis `{"type":"PLANTILLA","action":"ASSIGN","user_id":1,"plantilla_id":5}` → feedback `{"success":true,"plantilla":{...}}`.
- [x] (HECHA) Marcar cumplimiento con contador +1 y overachieving sin XP extra.
  - **JSON Front→Node:** `{"habit_id":1,"valor":1}` / `{"habit_id":1,"valor":-1}`
  - **JSON Node→Redis:** `{"action":"PROGRESS","user_id":1,"habit_id":1,"valor":1}`
  - **JSON Redis→Node:** `{"action":"PROGRESS","success":true,"progress":2,"completed_today":false}`
- [x] (HECHA) Feedback al 100%: XP + monedes al completar.
  - **JSON Front→Node:** `{"habit_id":1,"data":"2026-02-28T12:00:00Z"}`
  - **JSON Node→Redis:** `{"action":"COMPLETE","user_id":1,"habit_id":1,"data":"..."}`
  - **JSON Redis→Node:** `{"action":"COMPLETE","success":true,"xp_update":{"xp_total":1200,"monedes":25,"ratxa_actual":3}}`
  - **Creación/edición (HECHA):** `frontend/pages/habits.vue` → socket `habit_action` → `backend-node/src/socketHandler.js` → Redis `habits_queue` → `backend-laravel/app/Services/HabitService.php` → feedback `habit_action_confirmed`.
  - **Read hábitos (HECHA):** `GET /api/habits` en `backend-laravel/app/Http/Controllers/Api/HabitController.php` filtra por día.
  - **Progreso +/− (HECHA):** `habit_progress` → Redis → `HabitService` (PROGRESS) → feedback `progress`.
  - **Completar (HECHA):** `habit_complete` → Redis → `HabitService` (COMPLETE) → `xp_update` + `mission_completed`.
  - **FALTA Plantillas:** usar `usePlantillaStore` + socket `plantilla_action` → `PlantillaService` → tabla `usuaris_habits`.

## 4.3 Sistema de progresión y recompensas
- [x] (HECHA) Acumulación de XP al completar hábitos.
  - **Socket Node→Front:** `update_xp` con `{"xp_total":1200,"ratxa_actual":3,"ratxa_maxima":5,"monedes":25,"nivell":2,"xp_actual_nivel":200,"xp_objetivo_nivel":1200}`.
- [x] (HECHA) Subida automática de nivel según umbrales de XP.
  - **Socket Node→Front:** `level_up` con `{"nivell":3,"bonus_monedes":10,"xp_total":2400,"monedes":120}`.
  - **BD:** `usuaris.xp_actual_nivel`, `usuaris.xp_objetivo_nivel`.
- [x] (HECHA) Logros/insignias con condiciones y desbloqueo.
  - **Read JSON:** `GET /api/logros` → `[{"id":1,"nom":"...","obtingut":true}]`
  - **XP (HECHA):** `HabitService::processarConfirmacioHabit` suma XP y monedes; feedback `update_xp`.
  - **Logros (HECHA):** `LogroService` tras completar; `GET /api/logros`.
  - **Nivel (FALTA):** añadir en `HabitService` o `GamificationService` actualización de `usuaris.nivell`.

## 4.4 Ratxes y fallos
- [x] (HECHA) Ratxa: se actualiza al completar hábitos y reset diario automático.
  - **Socket Node→Front:** `update_xp` incluye `ratxa_actual`/`ratxa_maxima`.
- [x] (HECHA) Trencamiento de ratxa por inactividad diaria al finalizar el día.
  - **Redis→Node→Front:** evento `streak_broken` con `{"ratxa_anterior":5,"ratxa_actual":0,"data":"2026-03-01"}`.
- [ ] (FALTA) Impacto visual de la mascota por inactividad.
  - **JSON sockets/Redis:** N/A.
  - **Ratxa actual (HECHA):** `HabitService::actualitzarRatxa`.
  - **Reset diario (FALTA):** comando scheduler Laravel.
  - **Mascota (FALTA):** UI + estado backend.

## 5. Reglas de negocio
- [x] (HECHA) XP por dificultad (100/250/400).
  - **Socket Node→Front:** `update_xp` con XP/monedes.
- [x] (HECHA) Frecuencia diaria por defecto (UI).
  - **JSON sockets/Redis:** N/A.
- [x] (HECHA) Persistencia ratxa volátil con reset diario automático.
  - **JSON sockets/Redis:** N/A.
- [x] (HECHA) Lógica de overachieving y cap diario de XP (no XP extra por encima del 100%).
  - **JSON sockets/Redis:** ver PROGRESS/COMPLETE en 4.2.
- [x] (HECHA) Sincronización en tiempo real (sockets + Redis).
  - **JSON sockets/Redis:** ver 4.2 y 4.3.
  - **XP/Monedes (HECHA):** `HabitService` (XP 100/250/400; monedes 2/5/10).
  - **Overachieving (HECHA):** `PROGRESS` > objetivo; `COMPLETE` una vez.
  - **Real-time (HECHA):** Node + Redis + feedback.

## 6. Onboarding con IA (Google Gemini)
- [ ] (FALTA) Entrada de texto libre y procesamiento IA → JSON de hábitos.
- [ ] (FALTA) Plan semanal personalizado de 3–5 hábitos.
- [ ] (FALTA) Fallback a plantillas populares ante error IA.
  - **IA (FALTA):** Node/Gemini + endpoint Laravel.
  - **Fallback (FALTA):** `GET /api/plantilles` + UI de selección.
  - **JSON sockets/Redis:** N/A.

## 7. Datos funcionales del producto
- [x] (HECHA) Usuario: ID, nombre, email, rol, registro.
  - **Read JSON:** `GET /api/user/profile` → `{id,nom,email,nivell,xp_total,monedes,ratxa_actual,...}`.
- [x] (HECHA) Hábitos: título, dificultad, frecuencia, color, categoría.
  - **Read JSON:** `GET /api/habits` (ver 4.2).
- [x] (HECHA) Progreso: XP, monedas, ratxa; logs diarios visibles en UI.
  - **Read JSON:** `GET /api/habits/progress` → `[{"habit_id":1,"progress":2}]`; `GET /api/habits/logs` → `[{"dia":"...","progreso_diario":2,...}]`.
- [ ] (FALTA) Catálogo de recompensas y historial de canjes.
  - **JSON sockets/Redis (óptimo):** CUD por socket `reward_action` y read por API.
- [x] (HECHA) Logros: catálogo e histórico por usuario.
  - **Read JSON:** `GET /api/logros` y `GET /api/user/profile`.
  - **Progreso/logs (HECHA):** `HabitProgressController` + `frontend/pages/perfil.vue`.
  - **Recompensas (FALTA):** tablas + endpoints + UI.

## 8. Casos límite y escenarios especiales
- [ ] (FALTA) Mascota cambia estado por inactividad.
  - **JSON sockets/Redis:** N/A.
- [x] (HECHA) XP proporcional en hábitos incompletos (job diario).
  - **JSON sockets/Redis:** feedback `update_xp` tras aplicar proporcional.
- [ ] (FALTA) Fallback a plantillas si IA falla.
  - **JSON sockets/Redis:** N/A.
  - **Mascota (FALTA):** estado backend + UI.
  - **XP proporcional (FALTA):** lógica en `HabitService`.
  - **JSON sockets/Redis:** N/A.

## 9. Fuera de alcance (MVP)
- [ ] (FALTA) Chat entre usuarios.
  - **JSON sockets/Redis:** N/A.
- [ ] (FALTA) Tienda de cosméticos/objetos.
  - **JSON sockets/Redis:** N/A.
- [ ] (FALTA) Multijugador/rankings competitivos (usuario final).
  - **JSON sockets/Redis:** N/A.
- [ ] (FALTA) Integraciones con wearables.
  - **JSON sockets/Redis:** N/A.
- [ ] (FALTA) Frecuencias no diarias (tipo “cada 2 martes”).
  - **JSON sockets/Redis:** N/A.

## 10. Puntos abiertos / decisiones pendientes (doc)
- [ ] (PENDIENTE) Contradicción social: “Faro” vs chat excluido.
  - **JSON sockets/Redis:** N/A.
- [ ] (PENDIENTE) Valor de las monedas (qué se compra).
  - **JSON sockets/Redis:** N/A.
- [ ] (PENDIENTE) Penalizaciones de mascota.
  - **JSON sockets/Redis:** N/A.
- [ ] (PENDIENTE) Recuperación de ratxas con monedas.
  - **JSON sockets/Redis:** N/A.
- [ ] (PENDIENTE) Lógica de misiones semanales.
  - **JSON sockets/Redis:** N/A.

## Nuevas funcionalidades detectadas (no en la doc)
- [x] (NUEVA) Ruleta diaria (UI en home; backend no implementado).
  - **JSON sockets/Redis:** N/A (backend no implementado).
- [x] (NUEVA) Panel de administración con módulos (dashboard, plantillas, logros, hábitos, usuarios).
  - **JSON sockets/Redis:** N/A (panel admin usa API directa).
