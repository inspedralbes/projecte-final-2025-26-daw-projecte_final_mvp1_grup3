import m11 from '~/assets/img/Monstres/1.1-MonstrePetit.png';
import m12 from '~/assets/img/Monstres/1.2-MonstreMitja.png';
import m13 from '~/assets/img/Monstres/1.3-MonstreGran.png';
import m14 from '~/assets/img/Monstres/1.4-MonstreFort.png';
import m21 from '~/assets/img/Monstres/2.1-MonstrePetit.png';
import m22 from '~/assets/img/Monstres/2.2-MonstreMitja.png';
import m23 from '~/assets/img/Monstres/2.3-MonstreGran.png';
import m24 from '~/assets/img/Monstres/2.4-MonstreFort.png';
import m31 from '~/assets/img/Monstres/3.1-MonstrePetit.png';
import m32 from '~/assets/img/Monstres/3.2-MonstreMitja.png';
import m33 from '~/assets/img/Monstres/3.3-MonstreGran.png';
import m34 from '~/assets/img/Monstres/3.4-MonstreFort.png';
import m41 from '~/assets/img/Monstres/4.1-MonstrePetit.png';
import m42 from '~/assets/img/Monstres/4.2-MonstreMitja.png';
import m43 from '~/assets/img/Monstres/4.3-MonstreGran.png';
import m44 from '~/assets/img/Monstres/4.4-MonstreFort.png';

import mg11 from '~/assets/img/Monstres/gorra/1.1-MonstrePetitGorra.png';
import mg12 from '~/assets/img/Monstres/gorra/1.2-MonstreMitjaGorra.png';
import mg13 from '~/assets/img/Monstres/gorra/1.3-MonstreGranGorra.png';
import mg14 from '~/assets/img/Monstres/gorra/1.4-MonstreFortGorra.png';
import mg21 from '~/assets/img/Monstres/gorra/2.1-MonstrePetitGorra.png';
import mg22 from '~/assets/img/Monstres/gorra/2.2-MonstreMitjaGorra.png';
import mg23 from '~/assets/img/Monstres/gorra/2.3-MonstreGranGorra.png';
import mg24 from '~/assets/img/Monstres/gorra/2.4-MonstreFortGorra.png';
import mg31 from '~/assets/img/Monstres/gorra/3.1-MonstrePetitGorra.png';
import mg32 from '~/assets/img/Monstres/gorra/3.2-MonstreMitjaGorra.png';
import mg33 from '~/assets/img/Monstres/gorra/3.3-MonstreGranGorra.png';
import mg34 from '~/assets/img/Monstres/gorra/3.4-MonstreFortGorra.png';
import mg41 from '~/assets/img/Monstres/gorra/4.1-MonstrePetitGorra.png';
import mg42 from '~/assets/img/Monstres/gorra/4.2-MonstreMitjaGorra.png';
import mg43 from '~/assets/img/Monstres/gorra/4.3-MonstreGranGorra.png';
import mg44 from '~/assets/img/Monstres/gorra/4.4-MonstreFortGorra.png';

import egg1 from '~/assets/img/Monstres/huevos/Huevo_1.png';
import egg2 from '~/assets/img/Monstres/huevos/Huevo_2.png';
import egg3 from '~/assets/img/Monstres/huevos/Huevo_3.png';
import egg4 from '~/assets/img/Monstres/huevos/Huevo_4.png';

import egg1Open from '~/assets/img/Monstres/huevos/Huevo_1_abierto.png';
import egg2Open from '~/assets/img/Monstres/huevos/Huevo_2_abierto.png';
import egg3Open from '~/assets/img/Monstres/huevos/Huevo_3_abierto.png';
import egg4Open from '~/assets/img/Monstres/huevos/Huevo_4_abierto.png';

var COLOR_MAP = { V: '1', R: '2', L: '3', A: '4' };
var ETAPA_MAP = { B: '1', N: '2', A: '3', M: '4' };
var ETAPA_NAME = { B: 'PetitFocus', N: 'MitjaFocus', A: 'GranFocus', M: 'FortFocus' };

var MONSTER_MAP = {
  '1.1': m11, '1.2': m12, '1.3': m13, '1.4': m14,
  '2.1': m21, '2.2': m22, '2.3': m23, '2.4': m24,
  '3.1': m31, '3.2': m32, '3.3': m33, '3.4': m34,
  '4.1': m41, '4.2': m42, '4.3': m43, '4.4': m44
};

var MONSTER_GORRA_MAP = {
  '1.1': mg11, '1.2': mg12, '1.3': mg13, '1.4': mg14,
  '2.1': mg21, '2.2': mg22, '2.3': mg23, '2.4': mg24,
  '3.1': mg31, '3.2': mg32, '3.3': mg33, '3.4': mg34,
  '4.1': mg41, '4.2': mg42, '4.3': mg43, '4.4': mg44
};

var EGG_MAP = { '1': egg1, '2': egg2, '3': egg3, '4': egg4 };
var EGG_COLOR_MAP = { V: egg1, R: egg2, L: egg3, A: egg4 };
var EGG_OPEN_MAP = { '1': egg1Open, '2': egg2Open, '3': egg3Open, '4': egg4Open };
var EGG_OPEN_COLOR_MAP = { V: egg1Open, R: egg2Open, L: egg3Open, A: egg4Open };

export function getEtapa(nivell) {
  var n = Number(nivell) || 1;
  if (n >= 30) return 'M';
  if (n >= 15) return 'A';
  if (n >= 5) return 'N';
  return 'B';
}

export function getMonsterKey(tipus, nivell) {
  if (!tipus || tipus.length < 2) return '1.1';
  var colorCode = tipus.charAt(1).toUpperCase();
  var etapa = getEtapa(nivell);
  var colorNum = COLOR_MAP[colorCode] || '1';
  var etapaNum = ETAPA_MAP[etapa] || '1';
  return colorNum + '.' + etapaNum;
}

export function getMonsterImage(tipus, nivell) {
  var key = getMonsterKey(tipus, nivell);
  return MONSTER_MAP[key] || m11;
}

export function getMonsterGorraImage(tipus, nivell) {
  var key = getMonsterKey(tipus, nivell);
  return MONSTER_GORRA_MAP[key] || mg11;
}

export function getMonsterImageFromUser(user, skinKey) {
  if (!user) return null;
  var tipus = user.monstre_tipus;
  var nivell = user.nivell;
  if (!tipus) return null;
  if (skinKey === 'gorra_monster') {
    return getMonsterGorraImage(tipus, nivell);
  }
  return getMonsterImage(tipus, nivell);
}

export function getEggImage(colorLetter) {
  return EGG_COLOR_MAP[colorLetter] || egg1;
}

export function getEggByNumber(num) {
  return EGG_MAP[String(num)] || egg1;
}

export function getEggOpenImage(colorLetter) {
  return EGG_OPEN_COLOR_MAP[colorLetter] || egg1Open;
}

export function getEggOpenByNumber(num) {
  return EGG_OPEN_MAP[String(num)] || egg1Open;
}

export function getFocusMonsterKey(tipus, nivell) {
  if (!tipus || tipus.length < 2) return null;
  var colorCode = tipus.charAt(1).toUpperCase();
  var etapa = getEtapa(nivell);
  var colorNum = COLOR_MAP[colorCode] || '1';
  var etapaNum = ETAPA_MAP[etapa] || '1';
  return colorNum + '.' + etapaNum;
}

export function getFocusMonsterFilename(tipus, nivell) {
  if (!tipus || tipus.length < 2) return null;
  var colorCode = tipus.charAt(1).toUpperCase();
  var etapa = getEtapa(nivell);
  var colorNum = COLOR_MAP[colorCode] || '1';
  var etapaNum = ETAPA_MAP[etapa] || '1';
  var etapaName = ETAPA_NAME[etapa] || 'PetitFocus';
  return colorNum + '.' + etapaNum + '-Monstre' + etapaName + '.png';
}

export { MONSTER_MAP, MONSTER_GORRA_MAP, EGG_MAP, EGG_COLOR_MAP, EGG_OPEN_MAP, EGG_OPEN_COLOR_MAP };
