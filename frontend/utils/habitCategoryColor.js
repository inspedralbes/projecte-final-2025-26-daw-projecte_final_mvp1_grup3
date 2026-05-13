/**
 * Color per defecte associat a cada categoria de l'app (id 1–8),
 * alineat amb la llista de colors del formulari d'hàbits.
 */
import { normalizeHex as normalizeHexFromSpace } from './colorSpace.js'

export var COLORS_BY_CATEGORY_ID = {
  1: '#10B981',
  2: '#3B82F6',
  3: '#F59E0B',
  4: '#EF4444',
  5: '#8B5CF6',
  6: '#EC4899',
  7: '#06B6D4',
  8: '#1F2937'
}

/** Clau i18n habits.* per cada id (colors del formulari). */
export var SWATCH_I18N_KEY_BY_CATEGORY_ID = {
  1: 'swatch_emerald',
  2: 'swatch_blue',
  3: 'swatch_amber',
  4: 'swatch_red',
  5: 'swatch_violet',
  6: 'swatch_pink',
  7: 'swatch_cyan',
  8: 'swatch_slate'
}

export function getDefaultColorForCategoryId (id) {
  var n = parseInt(String(id), 10)
  if (Number.isNaN(n) || n < 1) {
    return '#10B981'
  }
  return COLORS_BY_CATEGORY_ID[n] || '#10B981'
}

function parseHexChannels (hex) {
  var h = String(hex || '').replace('#', '').trim()
  if (h.length === 3) {
    h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]
  }
  if (h.length !== 6) {
    return null
  }
  var n = parseInt(h, 16)
  if (Number.isNaN(n)) {
    return null
  }
  return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 }
}

/** Fons suau per botons de categoria seleccionada (mateix to que abans amb verd). */
export function hexToRgba (hex, alpha) {
  var rgb = parseHexChannels(hex)
  if (!rgb) {
    return 'transparent'
  }
  var a = typeof alpha === 'number' ? alpha : 1
  return 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + a + ')'
}

export function getCategorySelectionSurfaceStyle (categoryId) {
  var c = getDefaultColorForCategoryId(categoryId)
  return {
    backgroundColor: '#ffffff',
    border: '2px solid #E5E7EB',
    boxShadow: 'inset 6px 0 0 0 ' + c
  }
}

export function getSurfaceStyleForHex (hex) {
  var c = normalizeHexFromSpace(hex)
  return {
    backgroundColor: '#ffffff',
    border: '2px solid #E5E7EB',
    boxShadow: 'inset 6px 0 0 0 ' + c
  }
}

function rgbDist (a, b) {
  return Math.hypot(a.r - b.r, a.g - b.g, a.b - b.b)
}

/** Id 1–8 més proper al color (per baseCategoryId en categories d’usuari). */
export function nearestCategoryIdFromHex (hex) {
  var target = parseHexChannels(hex)
  if (!target) {
    return 8
  }
  var bestId = 8
  var best = Infinity
  Object.keys(COLORS_BY_CATEGORY_ID).forEach(function (id) {
    var c = parseHexChannels(COLORS_BY_CATEGORY_ID[id])
    if (!c) {
      return
    }
    var d = rgbDist(target, c)
    if (d < best) {
      best = d
      bestId = parseInt(id, 10)
    }
  })
  return bestId
}
