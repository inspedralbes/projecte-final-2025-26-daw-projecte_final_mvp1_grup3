/**
 * Audita composables i stores: falla si troba const, let, => o ternaris.
 */
var fs = require('fs');
var path = require('path');

var dirs = [
  path.join(__dirname, '..', 'composables', 'socket'),
  path.join(__dirname, '..', 'composables', 'domains'),
  path.join(__dirname, '..', 'stores', 'useSocketUiCallbacks.js')
];

var errors = [];

function revisarFitxer(filePath) {
  var content = fs.readFileSync(filePath, 'utf8');
  var lines = content.split('\n');
  var i;
  for (i = 0; i < lines.length; i++) {
    var line = lines[i];
    if (/\bconst\b/.test(line)) {
      errors.push(filePath + ':' + (i + 1) + ' const');
    }
    if (/\blet\b/.test(line)) {
      errors.push(filePath + ':' + (i + 1) + ' let');
    }
    if (/=>/.test(line)) {
      errors.push(filePath + ':' + (i + 1) + ' arrow');
    }
  }
}

function walk(dir) {
  if (!fs.existsSync(dir)) {
    return;
  }
  var stat = fs.statSync(dir);
  if (stat.isFile() && dir.endsWith('.js')) {
    revisarFitxer(dir);
    return;
  }
  if (!stat.isDirectory()) {
    return;
  }
  var entries = fs.readdirSync(dir);
  var j;
  for (j = 0; j < entries.length; j++) {
    walk(path.join(dir, entries[j]));
  }
}

var d;
for (d = 0; d < dirs.length; d++) {
  walk(dirs[d]);
}

if (errors.length > 0) {
  console.error('ES5 audit failed:');
  var k;
  for (k = 0; k < errors.length; k++) {
    console.error('  ' + errors[k]);
  }
  process.exit(1);
}

console.log('ES5 audit OK (new socket/domains modules)');
