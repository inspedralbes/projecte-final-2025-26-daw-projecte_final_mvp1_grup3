'use strict';

const { GoogleGenerativeAI } = require('@google/generative-ai');

const GEMINI_MODEL = 'gemini-2.0-flash';
const FALLBACK_HABITS = [
  {
    titol: 'Començar el dia amb hydratació',
    categoria: 'salut',
    senyal: 'matí',
    rutina: 'Beure un got d\'aigua en despertar',
    recompensa: 'Sentir-te més energètic',
  },
  {
    titol: 'Fer 10 minuts d\'exercici',
    categoria: 'salut',
    senyal: 'mati',
    rutina: 'Fer stretching o exercici lleuger',
    recompensa: 'Millora el teu estat d\'ànim',
  },
  {
    titol: 'Escriure 3 coses positives',
    categoria: 'ment',
    senyal: 'nit',
    rutina: 'Escriure al journal abans de dormir',
    recompensa: 'augmentar la gratitud',
  },
];

function getOnboardingGenerateHandler(genAI) {
  return async function (req, res) {
    try {
      const { categoria, senyal, dificultat, temps } = req.body || {};

      if (!categoria || !senyal || !dificultat || !temps) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          success: false,
          message: 'Falten camps obligatoris: categoria, senyal, dificultat, temps',
        }));
        return;
      }

      if (!genAI) {
        console.warn('Onboarding generate: GEMINI_API_KEY no configurada; s\'usen hàbits per defecte.');
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          success: true,
          habits: FALLBACK_HABITS,
          fallback: true,
        }));
        return;
      }

      const prompt = `Eres un assistent IA d'una app d'hàbits anomenada Loopy. Genera 3 hàbits personalitzats per a un usuari que:
- Vol millorar en: ${categoria}
- Té energia al: ${senyal}
- El seu obstacle principal és: ${dificultat}
- Té temps disponible: ${temps}

Retorna ÚNICAMENT un array JSON vàlid amb exactlya 3 objectes, cada un amb:
- titol (string): títol curt de l'hàbit
- categoria (string): categoria de l'hàbit
- senyal (string): el senyal o trigger de l'hàbit
- rutina (string): rutina detallada
- recompensa (string): recompensa per fer l'hàbit

Exemple de format:
[
  {"titol": "...", "categoria": "...", "senal": "...", "rutina": "...", "recompensa": "..."},
  {"titol": "...", "categoria": "...", "senal": "...", "rutina": "...", "recompensa": "..."},
  {"titol": "...", "categoria": "...", "senal": "...", "rutina": "...", "recompensa": "..."}
]`;

      const model = genAI.getGenerativeModel({ model: GEMINI_MODEL });
      const result = await model.generateContent(prompt);
      const responseText = result.response.text();

      let habits;
      try {
        const jsonMatch = responseText.match(/\[[\s\S]*\]/);
        if (jsonMatch) {
          habits = JSON.parse(jsonMatch[0]);
        } else {
          throw new Error('No JSON found in response');
        }
      } catch (parseError) {
        console.error('Error parsing Gemini response:', parseError.message);
        habits = FALLBACK_HABITS;
      }

      if (!Array.isArray(habits) || habits.length === 0) {
        habits = FALLBACK_HABITS;
      }

      const validatedHabits = habits.slice(0, 4).map(h => ({
        titol: h.titol || 'Hàbit desconegut',
        categoria: h.categoria || categoria,
        senyal: h.senal || senyal,
        rutina: h.rutina || '',
        recompensa: h.recompensa || '',
      }));

      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({
        success: true,
        habits: validatedHabits,
      }));
    } catch (error) {
      console.error('Error in onboarding generate:', error.message);
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({
        success: true,
        habits: FALLBACK_HABITS,
        fallback: true,
        message: 'Error generant hàbits; s\'han retornat suggeriments per defecte.',
      }));
    }
  };
}

module.exports = {
  getOnboardingGenerateHandler,
  FALLBACK_HABITS,
};
