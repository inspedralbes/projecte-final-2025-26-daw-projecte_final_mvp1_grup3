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
