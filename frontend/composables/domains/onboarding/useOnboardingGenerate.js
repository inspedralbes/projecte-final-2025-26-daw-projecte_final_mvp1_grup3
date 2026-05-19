/**
 * Modul JavaScript ES5: useOnboardingGenerate.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * POST /api/onboarding/generate al servidor Node (Gemini).
 */
export async function generarHabitsOnboarding(perfil) {
  var config = useRuntimeConfig();
  var nodeBase = config.public.socketUrl || 'http://localhost:3001';
  var url = nodeBase + '/api/onboarding/generate';
  var resposta = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(perfil)
  });
  return await resposta.json();
}
