export function getEtapa(nivell) {
  var n = Number(nivell) || 1;
  if (n >= 30) return 'M';
  if (n >= 15) return 'A';
  if (n >= 5) return 'N';
  return 'B';
}

export function getMonsterImage(tipus, nivell) {
  if (!tipus || tipus.length < 2) {
    return null;
  }
  
  // tipus es VV, VR, VL, VA
  // El colorCode es la segunda letra (V, R, L, A)
  var colorCode = tipus.charAt(1).toUpperCase();
  var etapa = getEtapa(nivell);
  
  // El nombre del archivo es M + Color + Etapa + " 1.png"
  // Ejemplo: MVB 1.png
  var filename = 'M' + colorCode + etapa + ' 1.png';
  var path = '/img/monsters/' + filename;
  
  return path;
}

export function getMonsterImageFromUser(user) {
  if (!user) {
    console.log('getMonsterImageFromUser: no user');
    return null;
  }
  var tipus = user.monstre_tipus;
  var nivell = user.nivell;
  
  if (!tipus) return null;
  return getMonsterImage(tipus, nivell);
}
