'use strict';


/**
 * Modul JavaScript ES5: audit-es5.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */


/**
 * Auditoria ES5: falla si troba const, let, arrow functions o ternaris.
 */
var fs = require('fs');
var path = require('path');

var srcDir = path.join(__dirname, '..', 'src');
var errors = [];

var patterns = [
  { name: 'const', regex: /\bconst\b/ },
  { name: 'let', regex: /\blet\b/ },
  { name: 'arrow', regex: /=>/ },
  { name: 'ternary', regex: /\?[^?]*:/ }
];

function revisarFitxer(filePath) {
  var content = fs.readFileSync(filePath, 'utf8');
  var lines = content.split('\n');
  var i;
  for (i = 0; i < lines.length; i++) {
    var line = lines[i];
    var p;
    for (p = 0; p < patterns.length; p++) {
      if (patterns[p].regex.test(line)) {
        errors.push(filePath + ':' + (i + 1) + ' [' + patterns[p].name + '] ' + line.trim());
      }
    }
  }
}

function recorrer(directori) {
  var entries = fs.readdirSync(directori);
  var i;
  for (i = 0; i < entries.length; i++) {
    var full = path.join(directori, entries[i]);
    var stat = fs.statSync(full);
    if (stat.isDirectory()) {
      recorrer(full);
    } else if (full.slice(-3) === '.js') {
      revisarFitxer(full);
    }
  }
}

recorrer(srcDir);

if (errors.length > 0) {
  console.error('ES5 audit FAILED (' + errors.length + ' issues):');
  errors.forEach(function (e) {
    console.error(e);
  });
  process.exit(1);
}

console.log('ES5 audit OK');
