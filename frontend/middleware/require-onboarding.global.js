/**
 * Middleware global: redirigeix a onboarding només quan hi ha onboarding pendent.
 * Exclou les rutes d'onboarding i auth.
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

  authStore.sincronitzarOnboardingRequeritDesDeStorage();
  if (authStore.requiresOnboarding) {
    return navigateTo('/onboarding');
  }
});
