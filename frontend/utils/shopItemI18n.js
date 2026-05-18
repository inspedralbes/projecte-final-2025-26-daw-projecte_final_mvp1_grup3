/**
 * Clau i18n per a productes de la botiga (BOTIGA_ITEMS).
 * Es deriva de metadata.skin_key, metadata.effect o metadata.i18n_key.
 */
export function metadataProducteBotiga(item) {
  if (!item) {
    return null;
  }
  var meta = item.metadata;
  if (typeof meta === 'string') {
    try {
      meta = JSON.parse(meta);
    } catch (e) {
      meta = null;
    }
  }
  return meta && typeof meta === 'object' ? meta : null;
}

export function clauProducteBotiga(item) {
  if (!item) {
    return null;
  }
  var meta = metadataProducteBotiga(item);
  if (meta && meta.i18n_key) {
    return meta.i18n_key;
  }
  if (meta && meta.skin_key) {
    return meta.skin_key;
  }
  if (meta && meta.effect === 'restore_streak') {
    return 'recuperador_racha';
  }
  return null;
}

/**
 * Nom traduït del producte; si no hi ha clau, retorna item.nom del backend.
 */
export function nomProducteBotiga(item, t, te) {
  var key = clauProducteBotiga(item);
  var path = key ? 'shop.items.' + key + '.name' : '';
  if (key && te && te(path)) {
    return t(path);
  }
  return (item && item.nom) ? item.nom : '';
}

/**
 * Categoria de botiga per als separadors: fons | skin | inventari
 */
export function categoriaProducteBotiga(item) {
  if (!item) {
    return 'skin';
  }
  if (item.tipus === 'consumible') {
    return 'inventari';
  }
  var meta = metadataProducteBotiga(item);
  if (meta && meta.slot === 'fons') {
    return 'fons';
  }
  if (item.tipus === 'skin') {
    return 'skin';
  }
  return 'skin';
}
