# Recuperar funcionalidades de onboarding en dev

## Contexto
- El merge de `onboarding_ia` en `dev` (commit `384818c`) fue revertido con el commit `09a94d2`
- La rama `origin/onboarding_ia` sigue intacta con todos sus commits
- Actualmente `dev` NO tiene las funcionalidades de onboarding

## Opción 1: Revertir el revert (Recomendada)

Deshace el commit de revertido, recuperando todos los cambios originales:

```bash
git revert 09a94d2 --no-edit
```

Esto crea un nuevo commit que deshace el revertido, recuperando:
- `frontend/pages/onboarding.vue`
- Controladores de onboarding en Laravel
- Handlers en backend-node
- Tests correspondientes
- Configuraciones de onboarding

## Opción 2: Merge de la rama remota

Como el merge original fue revertido, git puede no permitir un merge simple. Si la opción 1 falla:

```bash
git merge origin/onboarding_ia --no-edit
```

Si hay conflictos, resolverlos manualmente y hacer commit.

## Verificación después de aplicar

```bash
# Verificar que los archivos existen
ls frontend/pages/onboarding.vue
ls backend-laravel/app/Http/Controllers/Api/OnboardingHabitAssignController.php
ls backend-node/src/handlers/user/onboardingHandlers.js

# Ver historial
git log --oneline -5
```

## Problema detectado en la implementación actual

**Bug:** Cada vez que el usuario hace login se le muestran las preguntas del onboarding.

**Comportamiento esperado:** El onboarding debe mostrarse **solo la primera vez** que el usuario accede a la aplicación, no en cada login.

**Tareas pendientes:**
1. Revisar la lógica de redirección al onboarding en `frontend/middleware/require-onboarding.global.js`
2. Verificar que el backend marque cuando un usuario ya completó el onboarding (campo `onboarding_completed_at` o similar en la base de datos)
3. Comprobar que el middleware no redirija si el usuario ya tiene el onboarding completado
4. Revisar `backend-node/src/handlers/user/onboardingHandlers.js` para asegurar que se valida el estado del usuario

## Nota importante

La rama `origin/onboarding_ia` contiene estos commits:
- `b6cadf8` - Refactor onboarding habit assignment
- `d181dc2` - Implement onboarding flow with habit selection
- `e8d273b` - onboarding comencat

Todos estos cambios están disponibles y pueden recuperarse en `dev`.
