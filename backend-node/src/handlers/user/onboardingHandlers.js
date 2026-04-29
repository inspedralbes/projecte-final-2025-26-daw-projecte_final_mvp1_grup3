'use strict';

const { GoogleGenerativeAI } = require('@google/generative-ai');

const GEMINI_MODEL = process.env.GEMINI_MODEL || 'gemini-1.5-flash';
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

function normalitzarText(value) {
  if (!value) {
    return '';
  }
  return String(value).trim().toLowerCase();
}

function obtenirContextCategoria(categoria) {
  var categoriaNorm = normalitzarText(categoria);
  if (categoriaNorm === 'productivitat') {
    return {
      area: 'productivitat i focus',
      idees: ['planificar 3 tasques', 'bloquejar distraccions', 'tancar el dia revisant objectius'],
    };
  }
  if (categoriaNorm === 'ment') {
    return {
      area: 'benestar mental',
      idees: ['respiració guiada', 'journal curt', 'pausa de desconnexió'],
    };
  }
  if (categoriaNorm === 'aprenentatge') {
    return {
      area: 'aprenentatge i estudi',
      idees: ['repàs actiu', 'mini sessió de lectura', 'resum de conceptes'],
    };
  }
  return {
    area: 'salut i energia',
    idees: ['hidratar-se', 'moviment suau', 'hàbit de son'],
  };
}

function obtenirNivellDificultat(obstacle) {
  var obstacleNorm = normalitzarText(obstacle);
  if (obstacleNorm === 'estress') {
    return 'facil';
  }
  if (obstacleNorm === 'temps') {
    return 'facil';
  }
  return 'media';
}

function generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps) {
  var ctx = obtenirContextCategoria(categoria);
  var nivell = obtenirNivellDificultat(dificultat);
  var senyalNorm = normalitzarText(senyal) || 'mati';
  var tempsNorm = normalitzarText(temps) || '15min';
  var objectiuTemps = '2-5 minuts';
  if (tempsNorm === '30min') {
    objectiuTemps = '10-15 minuts';
  } else if (tempsNorm === '1h') {
    objectiuTemps = '15-20 minuts';
  } else if (tempsNorm === '1h+') {
    objectiuTemps = '20-30 minuts';
  }

  return [
    {
      titol: 'Micro pas de ' + ctx.area,
      categoria: categoria,
      senyal: senyalNorm,
      rutina: 'Quan arribi el moment de ' + senyalNorm + ', fes una acció de ' + ctx.idees[0] + ' durant ' + objectiuTemps + '.',
      recompensa: 'Progresses amb dificultat ' + nivell + ' i guanyes confiança.',
    },
    {
      titol: 'Hàbit guia contra ' + dificultat,
      categoria: categoria,
      senyal: senyalNorm,
      rutina: 'Abans de començar, prepara un pas simple de ' + ctx.idees[1] + ' per evitar bloqueig i mantindre consistència.',
      recompensa: 'Redueixes la fricció del teu obstacle principal.',
    },
    {
      titol: 'Tancament relacional de ' + ctx.area,
      categoria: categoria,
      senyal: senyalNorm,
      rutina: 'Al final del bloc de ' + tempsNorm + ', completa un mini tancament amb ' + ctx.idees[2] + ' per reforçar el progrés.',
      recompensa: 'El cervell associa el senyal amb una victòria repetible.',
    },
  ];
}

function validarHabitsGenerats(habits, categoria, senyal, dificultat, temps) {
  var origen = habits;
  if (!Array.isArray(origen) || origen.length === 0) {
    origen = generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps);
  }

  var resultat = [];
  var titolsJaUsats = {};
  var i;
  for (i = 0; i < origen.length && resultat.length < 3; i++) {
    var h = origen[i] || {};
    var titol = (h.titol || '').trim();
    if (!titol) {
      titol = 'Hàbit personalitzat ' + String(resultat.length + 1);
    }
    var clau = normalitzarText(titol);
    if (titolsJaUsats[clau]) {
      continue;
    }
    titolsJaUsats[clau] = true;
    resultat.push({
      titol: titol,
      categoria: (h.categoria || categoria || 'salut'),
      senyal: (h.senyal || h.senal || senyal || 'mati'),
      rutina: (h.rutina || 'Fer una micro acció sostenible alineada amb la teva resposta.'),
      recompensa: (h.recompensa || 'Consolidar el teu progrés amb dificultat facil o media.'),
    });
  }

  if (resultat.length < 3) {
    var fallback = generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps);
    for (i = 0; i < fallback.length && resultat.length < 3; i++) {
      if (!titolsJaUsats[normalitzarText(fallback[i].titol)]) {
        resultat.push(fallback[i]);
      }
    }
  }

  return resultat;
}

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
        var habitsFallbackPerfil = generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps);
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          success: true,
          habits: habitsFallbackPerfil,
          fallback: true,
        }));
        return;
      }

      const prompt = `Eres un assistent IA d'una app d'hàbits anomenada Loopy.
Genera 3 hàbits ALTAMENT personalitzats segons la combinació exacta de respostes.

Perfil de l'usuari:
- Vol millorar en: ${categoria}
- Té energia al: ${senyal}
- El seu obstacle principal és: ${dificultat}
- Té temps disponible: ${temps}

Regles obligatòries:
1) Els hàbits han de ser relacionals amb aquest perfil concret (no genèrics).
2) Han de ser aplicables a nivell "facil" o "media" (mai "dificil").
3) Cada hàbit ha de ser diferent i accionable.
4) Inclou trigger temporal coherent amb el senyal.

Retorna ÚNICAMENT un array JSON vàlid amb EXACTAMENT 3 objectes, cada un amb:
- titol (string): títol curt de l'hàbit
- categoria (string): categoria de l'hàbit
- senyal (string): el senyal o trigger de l'hàbit
- rutina (string): rutina detallada
- recompensa (string): recompensa per fer l'hàbit

Exemple de format:
[
  {"titol": "...", "categoria": "...", "senyal": "...", "rutina": "...", "recompensa": "..."},
  {"titol": "...", "categoria": "...", "senyal": "...", "rutina": "...", "recompensa": "..."},
  {"titol": "...", "categoria": "...", "senyal": "...", "rutina": "...", "recompensa": "..."}
]`;

      const model = genAI.getGenerativeModel({
        model: GEMINI_MODEL,
        generationConfig: {
          temperature: 1.0,
          topP: 0.95,
          topK: 40,
        },
      });
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
        habits = generarHabitsFallbackPerPerfil(categoria, senyal, dificultat, temps);
      }

      const validatedHabits = validarHabitsGenerats(habits, categoria, senyal, dificultat, temps);

      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({
        success: true,
        habits: validatedHabits,
      }));
    } catch (error) {
      console.error('Error in onboarding generate:', error.message);
      var body = req.body || {};
      var habitsPerfil = generarHabitsFallbackPerPerfil(body.categoria, body.senyal, body.dificultat, body.temps);
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({
        success: true,
        habits: habitsPerfil,
        fallback: true,
        message: 'Error generant hàbits; s\'han retornat suggeriments per defecte.',
      }));
    }
  };
}

module.exports = {
  getOnboardingGenerateHandler,
  FALLBACK_HABITS,
  generarHabitsFallbackPerPerfil,
  validarHabitsGenerats,
};
