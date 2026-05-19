'use strict';

//==============================================================================
//================================ VARIABLES ===================================
//==============================================================================

var FALLBACK_HABITS = [
  {
    titol: 'Començar el dia amb hydratació',
    categoria: 'salut',
    senyal: 'matí',
    rutina: 'Beure un got d\'aigua en despertar',
    recompensa: 'Sentir-te més energètic'
  },
  {
    titol: 'Fer 10 minuts d\'exercici',
    categoria: 'salut',
    senyal: 'mati',
    rutina: 'Fer stretching o exercici lleuger',
    recompensa: 'Millora el teu estat d\'ànim'
  },
  {
    titol: 'Escriure 3 coses positives',
    categoria: 'ment',
    senyal: 'nit',
    rutina: 'Escriure al journal abans de dormir',
    recompensa: 'augmentar la gratitud'
  }
];

//==============================================================================
//================================ FUNCIONS ====================================
//==============================================================================

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
      idees: ['planificar 3 tasques', 'bloquejar distraccions', 'tancar el dia revisant objectius']
    };
  }
  if (categoriaNorm === 'ment') {
    return {
      area: 'benestar mental',
      idees: ['respiració guiada', 'journal curt', 'pausa de desconnexió']
    };
  }
  if (categoriaNorm === 'aprenentatge') {
    return {
      area: 'aprenentatge i estudi',
      idees: ['repàs actiu', 'mini sessió de lectura', 'resum de conceptes']
    };
  }
  return {
    area: 'salut i energia',
    idees: ['hidratar-se', 'moviment suau', 'hàbit de son']
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
  var senyalNorm = normalitzarText(senyal);
  if (!senyalNorm) {
    senyalNorm = 'mati';
  }
  var tempsNorm = normalitzarText(temps);
  if (!tempsNorm) {
    tempsNorm = '15min';
  }
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
      recompensa: 'Progresses amb dificultat ' + nivell + ' i guanyes confiança.'
    },
    {
      titol: 'Hàbit guia contra ' + dificultat,
      categoria: categoria,
      senyal: senyalNorm,
      rutina: 'Abans de començar, prepara un pas simple de ' + ctx.idees[1] + ' per evitar bloqueig i mantindre consistència.',
      recompensa: 'Redueixes la fricció del teu obstacle principal.'
    },
    {
      titol: 'Tancament relacional de ' + ctx.area,
      categoria: categoria,
      senyal: senyalNorm,
      rutina: 'Al final del bloc de ' + tempsNorm + ', completa un mini tancament amb ' + ctx.idees[2] + ' per reforçar el progrés.',
      recompensa: 'El cervell associa el senyal amb una victòria repetible.'
    }
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
    var senyalHabit = h.senyal || h.senal || senyal || 'mati';
    resultat.push({
      titol: titol,
      categoria: h.categoria || categoria || 'salut',
      senyal: senyalHabit,
      rutina: h.rutina || 'Fer una micro acció sostenible alineada amb la teva resposta.',
      recompensa: h.recompensa || 'Consolidar el teu progrés amb dificultat facil o media.'
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

//==============================================================================
//================================ EXPORTS =====================================
//==============================================================================

module.exports = {
  FALLBACK_HABITS: FALLBACK_HABITS,
  generarHabitsFallbackPerPerfil: generarHabitsFallbackPerPerfil,
  validarHabitsGenerats: validarHabitsGenerats
};
