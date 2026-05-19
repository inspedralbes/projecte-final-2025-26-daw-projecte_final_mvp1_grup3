/**
 * Modul JavaScript ES5: require-onboarding.global.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

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
