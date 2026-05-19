/**
 * Modul JavaScript ES5: useCalendar.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

/**
 * Composable amb helpers de navegació temporal pel calendari.
 */
export function useCalendar() {
  var DIES_SETMANA_CAT = ["Dll", "Dm", "Dc", "Dj", "Dv", "Ds", "Dg"];
  /** Abreviatures per la capçalera del calendari (graella setmanal). */
  var DIES_SETMANA_GRID_CA = ["Dl", "Dt", "Dmc", "Dj", "Dv", "Ds", "Dg"];
  var DIES_SETMANA_ES = ["Lun", "Mar", "Mie", "Jue", "Vie", "Sab", "Dom"];
  var DIES_SETMANA_EN = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
  var MESOS_CAT = [
    "Gener", "Febrer", "Març", "Abril", "Maig", "Juny",
    "Juliol", "Agost", "Setembre", "Octubre", "Novembre", "Desembre"
  ];

  function prevMonth(year, month) {
    if (month === 1) {
      return { year: year - 1, month: 12 };
    }
    return { year: year, month: month - 1 };
  }

  function nextMonth(year, month) {
    if (month === 12) {
      return { year: year + 1, month: 1 };
    }
    return { year: year, month: month + 1 };
  }

  function getWeekStart(date) {
    var d = new Date(date);
    var day = d.getDay();
    var diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
  }

  function prevWeek(weekStartDate) {
    var d = new Date(weekStartDate);
    d.setDate(d.getDate() - 7);
    return d;
  }

  function nextWeek(weekStartDate) {
    var d = new Date(weekStartDate);
    d.setDate(d.getDate() + 7);
    return d;
  }

  function formatDayHeader(dateStr) {
    var d = new Date(dateStr);
    var diaNom = DIES_SETMANA_CAT[d.getDay() === 0 ? 6 : d.getDay() - 1];
    var diaNum = d.getDate();
    var mesNom = MESOS_CAT[d.getMonth()];
    return diaNom + ", " + diaNum + " de " + mesNom;
  }

  function normalitzarLocale(localeCode) {
    if (!localeCode) return "ca";
    var base = String(localeCode).toLowerCase().split("-")[0];
    if (base === "es" || base === "en" || base === "ca") return base;
    return "ca";
  }

  function diesEntre(dateA, dateB) {
    var msPerDia = 24 * 60 * 60 * 1000;
    var a = new Date(dateA.getFullYear(), dateA.getMonth(), dateA.getDate());
    var b = new Date(dateB.getFullYear(), dateB.getMonth(), dateB.getDate());
    return Math.round((a.getTime() - b.getTime()) / msPerDia);
  }

  function getWeekdayShort(dateObj, localeCode) {
    var idx = dateObj.getDay() === 0 ? 6 : dateObj.getDay() - 1;
    var locale = normalitzarLocale(localeCode);
    if (locale === "es") return DIES_SETMANA_ES[idx];
    if (locale === "en") return DIES_SETMANA_EN[idx];
    return DIES_SETMANA_CAT[idx];
  }

  function formatRelativeDayLabel(dateStr, localeCode) {
    var d = parseDate(dateStr);
    if (!d) return "";

    var avui = new Date();
    var diff = diesEntre(avui, d);
    var locale = normalitzarLocale(localeCode);

    if (diff === 0) {
      if (locale === "es") return "Hoy";
      if (locale === "en") return "Today";
      return "Avui";
    }

    if (diff === 1) {
      if (locale === "es") return "Ayer";
      if (locale === "en") return "Yesterday";
      return "Ahir";
    }

    var diaNomCurt = getWeekdayShort(d, locale);
    return diaNomCurt + " " + d.getDate() + "/" + (d.getMonth() + 1);
  }

  function formatMonthHeader(year, month) {
    return MESOS_CAT[month - 1] + " " + year;
  }

  function getCompletionRate(habitsJson) {
    if (!habitsJson || !Array.isArray(habitsJson) || habitsJson.length === 0) {
      return 0;
    }
    var completats = 0;
    for (var i = 0; i < habitsJson.length; i++) {
      if (habitsJson[i].acabado === true) {
        completats = completats + 1;
      }
    }
    return (completats / habitsJson.length) * 100;
  }

  function formatDate(year, month, day) {
    return year + "-" + String(month).padStart(2, "0") + "-" + String(day).padStart(2, "0");
  }

  function parseDate(dateStr) {
    if (!dateStr) return null;
    var parts = String(dateStr).split("-");
    if (parts.length !== 3) return null;
    var year = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10) - 1;
    var day = parseInt(parts[2], 10);
    if (isNaN(year) || isNaN(month) || isNaN(day)) return null;
    return new Date(year, month, day);
  }

  function toDateStr(date) {
    if (!(date instanceof Date)) return "";
    return formatDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  function addDays(dateStr, days) {
    var d = parseDate(dateStr);
    if (!d) return "";
    d.setDate(d.getDate() + days);
    return toDateStr(d);
  }

  function isAfterToday(dateStr) {
    var d = parseDate(dateStr);
    if (!d) return false;
    var avui = new Date();
    var avuiSenseHora = new Date(avui.getFullYear(), avui.getMonth(), avui.getDate());
    return d.getTime() > avuiSenseHora.getTime();
  }

  function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
  }

  function getFirstDayOfMonth(year, month) {
    var day = new Date(year, month - 1, 1).getDay();
    if (day === 0) { return 7; }
    return day;
  }

  return {
    DIES_SETMANA_CAT: DIES_SETMANA_CAT,
    DIES_SETMANA_GRID_CA: DIES_SETMANA_GRID_CA,
    MESOS_CAT: MESOS_CAT,
    prevMonth: prevMonth,
    nextMonth: nextMonth,
    getWeekStart: getWeekStart,
    prevWeek: prevWeek,
    nextWeek: nextWeek,
    formatDayHeader: formatDayHeader,
    formatRelativeDayLabel: formatRelativeDayLabel,
    formatMonthHeader: formatMonthHeader,
    getCompletionRate: getCompletionRate,
    formatDate: formatDate,
    parseDate: parseDate,
    toDateStr: toDateStr,
    addDays: addDays,
    isAfterToday: isAfterToday,
    getDaysInMonth: getDaysInMonth,
    getFirstDayOfMonth: getFirstDayOfMonth
  };
}
