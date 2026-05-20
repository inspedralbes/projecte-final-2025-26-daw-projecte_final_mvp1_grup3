var fs = require("fs");
var path = require("path");

var dir = __dirname;
var insertPath = path.join(dir, "insert.sql");
var valuesPath = path.join(dir, "pep_snapshots_values.sql");

var insert = fs.readFileSync(insertPath, "utf8");
var values = fs.readFileSync(valuesPath, "utf8").trim();
var body = values + ";\n";

var marker = "-- 10. DAILY_SNAPSHOTS";
var start = insert.indexOf(marker);
if (start === -1) {
  console.error("No s'ha trobat la secció DAILY_SNAPSHOTS.");
  process.exit(1);
}

var friendsNeedle = "(10, 5, 'pending');\n";
var extraHabits =
  "\n-- 9.2 Hàbits addicionals actius per Pep (id=5): categories 2-5 als snapshots del calendari\n" +
  "INSERT INTO USUARIS_HABITS (usuari_id, habit_id, objetiu_vegades_personalitzat)\n" +
  "SELECT 5, id, 1 FROM HABITS WHERE id BETWEEN 6 AND 12;\n";

if (insert.indexOf("-- 9.2 Hàbits addicionals actius per Pep") === -1) {
  insert = insert.replace(friendsNeedle, friendsNeedle + extraHabits);
  start = insert.indexOf(marker);
}

var replacement =
  "-- 10. DAILY_SNAPSHOTS per Pep (id=5): últims 25 dies (demo variada; regenerar amb node gen_pep_snapshots.js)\n" +
  "INSERT INTO DAILY_SNAPSHOTS (usuari_id, data, mascota_json, habits_json, economia_json) VALUES\n" +
  body;

insert = insert.slice(0, start) + replacement;
fs.writeFileSync(insertPath, insert, "utf8");
console.log("insert.sql actualitzat.");
