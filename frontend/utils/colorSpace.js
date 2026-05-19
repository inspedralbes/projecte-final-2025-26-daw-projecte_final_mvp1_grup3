/**
 * Modul JavaScript ES5: colorSpace.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/** Utilitats RGB/HSV/hex per al selector de color del formulari d'hàbits. */

export function normalizeHex (hex) {
  var h = String(hex || '').trim()
  if (!h) {
    return '#10B981'
  }
  if (h[0] !== '#') {
    h = '#' + h
  }
  h = h.toUpperCase()
  if (/^#[0-9A-F]{3}$/.test(h)) {
    h = '#' + h[1] + h[1] + h[2] + h[2] + h[3] + h[3]
  }
  if (!/^#[0-9A-F]{6}$/.test(h)) {
    return '#10B981'
  }
  return h
}

export function hexToRgb (hex) {
  var h = normalizeHex(hex).slice(1)
  var n = parseInt(h, 16)
  if (Number.isNaN(n)) {
    return { r: 16, g: 185, b: 129 }
  }
  return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 }
}

export function rgbToHex (r, g, b) {
  var to = function (x) {
    var s = Math.round(clamp(x, 0, 255)).toString(16)
    return s.length === 1 ? '0' + s : s
  }
  return '#' + to(r) + to(g) + to(b)
}

function clamp (v, lo, hi) {
  return Math.max(lo, Math.min(hi, v))
}

export function rgbToHsv (r, g, b) {
  r /= 255
  g /= 255
  b /= 255
  var max = Math.max(r, g, b)
  var min = Math.min(r, g, b)
  var d = max - min
  var h = 0
  if (d !== 0) {
    if (max === r) {
      h = ((g - b) / d + (g < b ? 6 : 0)) / 6
    } else if (max === g) {
      h = ((b - r) / d + 2) / 6
    } else {
      h = ((r - g) / d + 4) / 6
    }
  }
  var s = max === 0 ? 0 : d / max
  var v = max
  return { h: h * 360, s: s, v: v }
}

export function hsvToRgb (h, s, v) {
  h = ((h % 360) + 360) % 360
  s = clamp(s, 0, 1)
  v = clamp(v, 0, 1)
  var c = v * s
  var x = c * (1 - Math.abs(((h / 60) % 2) - 1))
  var m = v - c
  var rp = 0; var gp = 0; var bp = 0
  if (h < 60) {
    rp = c; gp = x; bp = 0
  } else if (h < 120) {
    rp = x; gp = c; bp = 0
  } else if (h < 180) {
    rp = 0; gp = c; bp = x
  } else if (h < 240) {
    rp = 0; gp = x; bp = c
  } else if (h < 300) {
    rp = x; gp = 0; bp = c
  } else {
    rp = c; gp = 0; bp = x
  }
  return {
    r: Math.round((rp + m) * 255),
    g: Math.round((gp + m) * 255),
    b: Math.round((bp + m) * 255)
  }
}

export function hexToHsv (hex) {
  var rgb = hexToRgb(hex)
  return rgbToHsv(rgb.r, rgb.g, rgb.b)
}

export function hsvToHex (h, s, v) {
  var rgb = hsvToRgb(h, s, v)
  return rgbToHex(rgb.r, rgb.g, rgb.b)
}

/** Contraste llegible sobre fons (retorna #1a1a1a o #ffffff). */
export function pickTextOnHexBackground (hex) {
  var rgb = hexToRgb(hex)
  var L = (0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b) / 255
  return L > 0.62 ? '#1a1a1a' : '#ffffff'
}
