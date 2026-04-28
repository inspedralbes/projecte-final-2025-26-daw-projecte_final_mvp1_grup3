/**
 * Middleware global: Si l'usuari no té hàbits, redirigeix a onboarding.
 * Excloou les rutes d'onboarding i auth.
 */
export default defineNuxtRouteMiddleware(async function (to, from) {
  const excludedRoutes = ['/onboarding', '/auth/login', '/auth/registre', '/'];
  const onboardingDoneCookie = useCookie('loopy_onboarding_done');
  let onboardingDoneLocal = false;

  if (typeof window !== 'undefined') {
    onboardingDoneLocal = localStorage.getItem('loopy_onboarding_done') === '1';
  }

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

  var onboardingMarcat =
    onboardingDoneCookie.value === '1' || onboardingDoneLocal;
  var onboardingValiPerUsuari = onboardingMarcat;
  if (onboardingMarcat && typeof window !== 'undefined' && authStore.user && authStore.user.id != null) {
    var uid = String(authStore.user.id);
    var perUser = localStorage.getItem('loopy_onboarding_user_id');
    if (perUser && perUser !== uid) {
      onboardingValiPerUsuari = false;
    }
  }

  if (habitStore.habits.length === 0 && onboardingValiPerUsuari) {
    return;
  }

  if (habitStore.habits.length === 0) {
    return navigateTo('/onboarding');
  }
});
