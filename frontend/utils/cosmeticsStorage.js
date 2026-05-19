/**
 * Persistència local de skin/fons equipats (cache per evitar flash al refresh).
 * La font de veritat és Laravel (game_state); això només accelera la UI.
 */

var STORAGE_KEY = "loopy_cosmetics";

function llegirRaw() {
  if (typeof window === "undefined") {
    return null;
  }
  try {
    var raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return null;
    }
    return JSON.parse(raw);
  } catch (_) {
    return null;
  }
}

function guardarRaw(dades) {
  if (typeof window === "undefined") {
    return;
  }
  try {
    if (!dades || dades.usuariId == null) {
      localStorage.removeItem(STORAGE_KEY);
      return;
    }
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dades));
  } catch (_) {}
}

/**
 * Carrega cosmetics cachejats per un usuari concret.
 * @param {number|string} usuariId
 * @returns {{ skinKey: string|null, fonsKey: string|null }|null}
 */
export function carregarCosmeticsDesDeStorage(usuariId) {
  var parsed = llegirRaw();
  if (!parsed || parsed.usuariId == null) {
    return null;
  }
  if (String(parsed.usuariId) !== String(usuariId)) {
    return null;
  }
  return {
    skinKey: parsed.skinKey != null ? parsed.skinKey : null,
    fonsKey: parsed.fonsKey != null ? parsed.fonsKey : null,
  };
}

/**
 * Desa skin/fons al localStorage (associat a usuariId).
 */
export function desarCosmeticsAStorage(usuariId, skinKey, fonsKey) {
  if (usuariId == null) {
    return;
  }
  guardarRaw({
    usuariId: usuariId,
    skinKey: skinKey != null ? skinKey : null,
    fonsKey: fonsKey != null ? fonsKey : null,
  });
}

export function esborrarCosmeticsStorage() {
  guardarRaw(null);
}
