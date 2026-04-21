/**
 * Middleware global: Si l'usuari no té hàbits, redirigeix a onboarding.
 * Excloou les rutes d'onboarding i auth.
 */
export default defineNuxtRouteMiddleware(async function (to, from) {
  const excludedRoutes = ['/onboarding', '/auth/login', '/auth/registre', '/'];

  if (excludedRoutes.includes(to.path)) {
    return;
  }

  const authStore = useAuthStore();
  authStore.loadFromStorage();

  const roleCookie = useCookie('loopy_role');
  const role = authStore.role || roleCookie?.value;

  if (role !== 'user') {
    return;
  }

  const habitStore = useHabitStore();

  if (habitStore.habits.length === 0) {
    try {
      await habitStore.obtenirHabitsDesDeApi();
    } catch (e) {
      console.error('Error loading habits:', e);
    }
  }

  if (habitStore.habits.length === 0) {
    return navigateTo('/onboarding');
  }
});
