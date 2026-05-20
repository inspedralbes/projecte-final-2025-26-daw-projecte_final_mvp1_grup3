/* Genera linies VALUES per DAILY_SNAPSHOTS (Pep). Executar: node gen_pep_snapshots.js */
var fs = require("fs");
var path = require("path");
var types = ["MV", "MR", "ML", "MA"];

function masc(i, nl, xt, tip, skin, fons, teG, teF) {
  return JSON.stringify({
    nivell: nl,
    xp_total: xt,
    xp_actual_nivel: 20 + ((i * 47) % 220),
    xp_objetivo_nivel: 300 + Math.max(0, nl - 3) * 100,
    monstre_tipus: tip,
    ratxa: 5 + (i % 9),
    monedes: 400,
    skin_key: skin,
    fons_key: fons,
    te_gorra: teG,
    te_fons: teF,
  });
}

var H = {
  1: { t: "Levantamiento de pesas", i: "🏃", c: "#65A30D", d: "dificil", cat: 1 },
  2: { t: "Caminar 30 min", i: "🏃", c: "#65A30D", d: "facil", cat: 1 },
  4: { t: "Beber 2L agua", i: "🥗", c: "#3B82F6", d: "facil", cat: 2 },
  6: { t: "Evitar ultraprocesados", i: "🥗", c: "#3B82F6", d: "dificil", cat: 2 },
  7: { t: "Repasar apuntes", i: "📚", c: "#A855F7", d: "media", cat: 3 },
  10: { t: "Leer 10 páginas", i: "📖", c: "#F97316", d: "facil", cat: 4 },
  12: { t: "Terminar capítulo", i: "📖", c: "#F97316", d: "media", cat: 4 },
  13: { t: "Meditación mañana", i: "🧘", c: "#EC4899", d: "facil", cat: 5 },
};

function habit(id, done, focus, mode) {
  var h = H[id];
  return {
    id: id,
    titol: h.t,
    icona: h.i,
    color: h.c,
    dificultat: h.d,
    categoria_id: h.cat,
    metadata: null,
    acabado: done,
    completed_with_focus: focus || false,
    predominant_focus_mode: mode || null,
  };
}

function j(arr) {
  return JSON.stringify(arr);
}

var sets = [
  j([habit(1, true, false, null), habit(7, true, false, null), habit(10, true, false, null)]),
  j([habit(2, true, true, "25_5"), habit(4, true, false, null), habit(13, true, false, null)]),
  j([habit(1, false, false, null), habit(12, true, false, null), habit(6, true, false, null)]),
  j([habit(7, true, true, "50_10"), habit(10, true, false, null), habit(1, true, false, null)]),
  j([habit(4, true, false, null), habit(13, false, false, null), habit(2, true, false, null)]),
  j([habit(1, true, false, null), habit(2, true, false, null), habit(6, true, false, null), habit(7, true, false, null)]),
  j([
    habit(10, true, true, "25_5"),
    habit(12, true, false, null),
    habit(4, true, false, null),
    habit(1, true, true, "50_10"),
  ]),
  j([habit(2, true, false, null), habit(13, true, false, null)]),
  j([habit(1, true, false, null), habit(7, false, false, null), habit(10, true, false, null), habit(6, true, false, null)]),
  j([habit(4, true, false, null), habit(1, true, false, null), habit(13, true, false, null), habit(12, true, false, null)]),
  j([habit(7, true, false, null), habit(10, true, false, null), habit(2, true, false, null)]),
  j([habit(1, true, true, "25_5"), habit(6, true, false, null), habit(13, true, false, null)]),
  j([habit(12, true, false, null)]),
  j([habit(2, true, false, null), habit(4, true, false, null), habit(7, true, true, "50_10")]),
  j([habit(1, false, false, null), habit(10, true, false, null), habit(13, true, false, null)]),
  j([
    habit(1, true, false, null),
    habit(2, true, false, null),
    habit(4, true, false, null),
    habit(7, true, false, null),
    habit(10, true, false, null),
  ]),
  j([habit(6, true, false, null), habit(12, true, false, null)]),
  j([habit(13, true, false, null), habit(1, true, true, "50_10"), habit(4, true, false, null)]),
  j([habit(7, true, false, null), habit(10, true, false, null), habit(6, false, false, null)]),
  j([habit(1, true, false, null), habit(2, true, false, null), habit(13, true, false, null)]),
  j([habit(10, true, false, null), habit(12, true, false, null), habit(2, true, false, null)]),
  j([habit(4, true, false, null), habit(7, true, false, null), habit(13, true, false, null)]),
  j([habit(1, true, true, "25_5"), habit(6, true, false, null), habit(10, true, false, null)]),
  j([habit(2, true, false, null), habit(7, true, false, null)]),
  j([habit(1, true, false, null), habit(4, true, false, null), habit(10, true, false, null), habit(13, true, true, "25_5")]),
];

function econ(xp, m) {
  return JSON.stringify({ xp_guanyada_avui: xp, monedes_guanyades_avui: m });
}

var econs = sets.map(function (_, i) {
  return econ(100 + ((i * 37) % 400), 5 + (i % 5) * 4);
});

var lines = [];
for (var k = 25; k >= 1; k--) {
  var idx = 25 - k;
  var tip = types[idx % 4];
  var nl = 3 + Math.min(5, Math.floor(idx / 5));
  var xt = 520 + idx * 95;
  var skin = null;
  var fons = null;
  var teG = false;
  var teF = false;
  if (idx % 5 === 1) {
    fons = "fons_platja";
    teF = true;
  } else if (idx % 5 === 2) {
    fons = "fons_casa";
    teF = true;
  } else if (idx % 5 === 3) {
    fons = "fons_platja";
    teF = true;
    teG = true;
    skin = "gorra_monster";
  } else if (idx % 5 === 4) {
    fons = "fons_casa";
    teF = true;
    teG = true;
    skin = "gorra_monster";
  }
  var mj = masc(idx, nl, xt, tip, skin, fons, teG, teF);
  var intv =
    k === 25 ? "'25 days'" : k === 1 ? "'1 day'" : "'" + String(k) + " days'";
  var sqlEsc = function (s) {
    return String(s).replace(/'/g, "''");
  };
  lines.push(
    "(5, CURRENT_DATE - INTERVAL " +
      intv +
      ", '" +
      sqlEsc(mj) +
      "', '" +
      sqlEsc(sets[idx]) +
      "', '" +
      sqlEsc(econs[idx]) +
      "')"
  );
}

var out = lines.join(",\n") + "\n";
fs.writeFileSync(path.join(__dirname, "pep_snapshots_values.sql"), out, "utf8");
console.log("Written pep_snapshots_values.sql (" + lines.length + " rows)");