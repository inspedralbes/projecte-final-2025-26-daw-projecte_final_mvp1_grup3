/**
 * Helpers de cosmètics (gorra / fons) dins snapshots del calendari.
 */

/**
 * @param {object|null|undefined} mascotaJson
 * @returns {{ skin_key: string|null, fons_key: string|null, te_gorra: boolean, te_fons: boolean }}
 */
export function cosmeticsFromMascotaJson(mascotaJson) {
  if (!mascotaJson || typeof mascotaJson !== "object") {
    return {
      skin_key: null,
      fons_key: null,
      te_gorra: false,
      te_fons: false,
    };
  }

  var skinKey = mascotaJson.skin_key || null;
  var fonsKey = mascotaJson.fons_key || null;
  var teGorra =
    mascotaJson.te_gorra === true ||
    (typeof skinKey === "string" && skinKey.length > 0);
  var teFons =
    mascotaJson.te_fons === true ||
    (typeof fonsKey === "string" && fonsKey.length > 0);

  return {
    skin_key: skinKey,
    fons_key: fonsKey,
    te_gorra: teGorra,
    te_fons: teFons,
  };
}

/**
 * @param {object|null|undefined} daySummary
 * @returns {{ skin_key: string|null, fons_key: string|null, te_gorra: boolean, te_fons: boolean }}
 */
export function cosmeticsFromDaySummary(daySummary) {
  if (!daySummary || typeof daySummary !== "object") {
    return cosmeticsFromMascotaJson(null);
  }

  return {
    skin_key: daySummary.skin_key || null,
    fons_key: daySummary.fons_key || null,
    te_gorra: daySummary.te_gorra === true,
    te_fons: daySummary.te_fons === true,
  };
}

/**
 * @param {string|null|undefined} fonsKey
 * @returns {string}
 */
export function fonsClassFromKey(fonsKey) {
  if (fonsKey === "fons_platja") {
    return "fons-platja-bg";
  }
  if (fonsKey === "fons_casa") {
    return "fons-casa-bg";
  }
  return "";
}

/**
 * Classe CSS per al punt de fons al calendari.
 * @param {string|null|undefined} fonsKey
 * @returns {string}
 */
export function fonsDotClassFromKey(fonsKey) {
  if (fonsKey === "fons_platja") {
    return "calendar-day-cell__fons-dot--platja";
  }
  if (fonsKey === "fons_casa") {
    return "calendar-day-cell__fons-dot--casa";
  }
  return "";
}
