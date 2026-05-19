# Fix: Home no cargaba datos correctamente

## Problema identificado

La página **Home** del frontend no mostraba hábitos, misiones, racha ni logros, mientras que el flujo RAD (crear hábitos/plantillas) sí funcionaba.

## Causas raíz

1. **`user` indefinido en el template**: El template usaba `{{ user ? user.nom : $t('home.user_name') }}` pero no existía ningún computed `user`, lo que provocaba un valor `undefined` y potenciales problemas de hydration (SSR vs cliente).

2. **Falta de inicialización de auth antes del fetch**: Aunque el plugin `0.auth-init.client.js` carga el token, en algunos casos (navegación directa, race conditions) el token podía no estar disponible cuando se ejecutaba la primera petición a `/api/user/home`.

3. **authFetch no actualizaba headers en el reintento**: Tras un 401 y un refresh correcto del token, el reintento de la petición original usaba los headers antiguos (sin el nuevo token), provocando otro 401.

4. **GameStateResource incompleto**: El recurso no incluía `can_spin_roulette` ni `ruleta_ultima_tirada`, que el frontend esperaba para la ruleta diaria.

5. **HomeDataService sin imports**: Faltaban `use App\Services\GamificationService` y `use App\Services\LogroService` en HomeDataService.

6. **Sin fallback ante fallo del endpoint consolidado**: Si `/api/user/home` fallaba, Home no intentaba cargar datos por endpoints alternativos.

7. **Posible wrapper `data` en respuestas**: El gameStore no contemplaba respuestas donde Laravel envuelve el payload en `{ data: ... }`.

## Archivos modificados

### Frontend

| Archivo | Cambio |
|---------|--------|
| `frontend/pages/home.vue` | Añadido `useAuthStore`, `definePageMeta({ ssr: false })`, computed `user`, `authStore.loadFromStorage()` en mounted, método `carregarDadesFallback()` y fallback en catch de carregarDadesHome |
| `frontend/stores/gameStore.js` | Manejo de respuesta envuelta en `data` |
| `frontend/utils/authFetch.js` | Reconstrucción de headers con el token actualizado antes del reintento tras refresh |

### Backend

| Archivo | Cambio |
|---------|--------|
| `backend-laravel/app/Services/HomeDataService.php` | Añadidos imports de GamificationService y LogroService |
| `backend-laravel/app/Http/Resources/GameStateResource.php` | Añadidos `can_spin_roulette` y `ruleta_ultima_tirada` al array de salida |

## Resumen de correcciones

1. **Computed `user`**: `user` se obtiene de `useAuthStore().user`, de modo que el nombre del usuario se renderiza correctamente.

2. **`definePageMeta({ ssr: false })`**: Home se renderiza solo en cliente, evitando hydration mismatch entre servidor (sin localStorage) y cliente (con token/user).

3. **`loadFromStorage()` en mounted**: Se asegura que el token esté cargado antes de la primera petición API.

4. **authFetch retry con headers actualizados**: Tras un refresh exitoso, los headers se regeneran con `authStore.getAuthHeaders()` antes de reintentar.

5. **GameStateResource completo**: Incluye todos los campos que el frontend espera para la ruleta y el estado del juego.

6. **Fallback en Home**: Si `carregarDadesHome` falla, se llaman `obtenirHabitos`, `obtenirEstatJoc`, `obtenirProgresHabits` y `logroStore.carregarLogros` para recuperar datos.

7. **Manejo de wrapper `data`**: El gameStore acepta respuestas con estructura `{ data: { game_state, habits, ... } }`.

## Validación

Para comprobar que Home funciona:

1. Iniciar sesión como usuario.
2. Navegar a `/home`.
3. Verificar que se muestran: hábitos, misiones diarias, racha, XP, monedes, logros y ruleta.
4. Comprobar que no hay errores en la consola del navegador.
5. Comprobar que no hay errores HTTP en la pestaña Network (todas las peticiones con 200).
