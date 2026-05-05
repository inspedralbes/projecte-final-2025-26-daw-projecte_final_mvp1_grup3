/**
 * Composable per obrir el modal de detalls d'un hàbit històric.
 * Utilitza SweetAlert2 ($swal) per mostrar la info del snapshot.
 */

var XP_PER_DIFICULTAT = {
  'facil': 100,
  'media': 250,
  'dificil': 400
};

var MONEDES_PER_DIFICULTAT = {
  'facil': 2,
  'media': 5,
  'dificil': 10
};

/**
 * Genera el HTML del bloc de metadata (API externa).
 * Mostra portades, miniatures i títols segons el contingut.
 */
function renderMetadata(metadata) {
  if (!metadata) {
    return '';
  }

  var html = '<div class="mt-4 p-3 rounded-2xl bg-gray-50 border border-gray-100">';
  html += '<p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Info externa</p>';

  if (metadata.cover || metadata.thumbnail || metadata.image) {
    var imgSrc = metadata.cover || metadata.thumbnail || metadata.image;
    html += '<div class="flex justify-center mb-2">';
    html += '<img src="' + imgSrc + '" alt="Metadata" class="rounded-xl max-h-32 object-cover shadow" />';
    html += '</div>';
  }

  if (metadata.title || metadata.titol) {
    var titolMeta = metadata.title || metadata.titol;
    html += '<p class="text-sm font-semibold text-gray-700 text-center">' + titolMeta + '</p>';
  }

  if (metadata.author || metadata.autor) {
    var autorMeta = metadata.author || metadata.autor;
    html += '<p class="text-xs text-gray-500 text-center">' + autorMeta + '</p>';
  }

  if (metadata.description || metadata.descripcio) {
    var descMeta = metadata.description || metadata.descripcio;
    html += '<p class="text-xs text-gray-400 text-center mt-1">' + descMeta + '</p>';
  }

  html += '</div>';
  return html;
}

/**
 * Genera tot el HTML del contingut del modal.
 */
function buildModalHtml(habit, date) {
  var acabado = !!habit.acabado;
  var dificultat = habit.dificultat || 'facil';
  var xp = 0;
  var monedes = 0;

  if (acabado) {
    xp = XP_PER_DIFICULTAT[dificultat] || 0;
    monedes = MONEDES_PER_DIFICULTAT[dificultat] || 0;
  }

  var colorFons = habit.color || '#10B981';
  var icona = habit.icona || '📋';
  var titol = habit.titol || 'Hàbit';

  var badgeClass = '';
  var badgeText = '';
  if (acabado) {
    badgeClass = 'bg-green-100 text-green-700 border-green-200';
    badgeText = '✅ Completat';
  } else {
    badgeClass = 'bg-red-100 text-red-500 border-red-200';
    badgeText = '❌ No completat';
  }

  var html = '';

  html += '<div class="flex flex-col items-center gap-3">';

  html += '<div style="background-color: ' + colorFons + ';" class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl text-white shadow-lg">';
  html += icona;
  html += '</div>';

  html += '<h3 class="text-lg font-black text-gray-800">' + titol + '</h3>';

  html += '<span class="inline-block px-3 py-1 rounded-full text-xs font-bold border ' + badgeClass + '">';
  html += badgeText;
  html += '</span>';

  html += '<div class="flex gap-4 mt-2">';

  html += '<div class="flex flex-col items-center bg-amber-50 rounded-2xl px-4 py-2 border border-amber-100">';
  html += '<span class="text-xl font-black text-amber-600">' + xp + '</span>';
  html += '<span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">XP</span>';
  html += '</div>';

  html += '<div class="flex flex-col items-center bg-yellow-50 rounded-2xl px-4 py-2 border border-yellow-100">';
  html += '<span class="text-xl font-black text-yellow-600">' + monedes + '</span>';
  html += '<span class="text-[10px] font-bold text-yellow-400 uppercase tracking-wider">Monedes</span>';
  html += '</div>';

  html += '</div>';

  html += '<p class="text-xs text-gray-400 mt-1">📅 ' + date + '</p>';

  html += renderMetadata(habit.metadata);

  html += '</div>';

  return html;
}

export function useHabitHistoryModal() {
  var nuxtApp = useNuxtApp();

  /**
   * Obre el modal SweetAlert2 amb els detalls de l'hàbit del snapshot.
   * @param {Object} habit - Objecte hàbit del habits_json del snapshot
   * @param {String} date - Data del snapshot (YYYY-MM-DD)
   */
  function openHabitHistoryModal(habit, date) {
    var swal = nuxtApp.$swal;

    swal.fire({
      html: buildModalHtml(habit, date),
      showConfirmButton: true,
      confirmButtonText: 'Tancar',
      confirmButtonColor: '#10B981',
      showCloseButton: true,
      customClass: {
        popup: 'rounded-3xl shadow-2xl border border-white/50 backdrop-blur-md',
        confirmButton: 'rounded-2xl font-bold px-6 py-2'
      },
      background: 'rgba(255, 255, 255, 0.97)',
      backdrop: 'rgba(0, 0, 0, 0.4)'
    });
  }

  return {
    openHabitHistoryModal: openHabitHistoryModal,
    XP_PER_DIFICULTAT: XP_PER_DIFICULTAT,
    MONEDES_PER_DIFICULTAT: MONEDES_PER_DIFICULTAT
  };
}
