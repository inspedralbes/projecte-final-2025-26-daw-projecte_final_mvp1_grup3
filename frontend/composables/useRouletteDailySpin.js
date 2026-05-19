import { ref } from 'vue';
import { useGameStore } from '~/stores/gameStore.js';

export const RULETA_VIDEO_URL = '/video/ruleta-diaria.mp4';

function formatPremiRuleta(data) {
  if (!data || typeof data !== 'object') {
    return 'Premi';
  }

  var tipus = data.type || '';
  var quantitat = data.amount;

  if (tipus === 'xp' && quantitat !== undefined && quantitat !== null) {
    return String(quantitat) + ' XP';
  }

  if (tipus === 'coins' && quantitat !== undefined && quantitat !== null) {
    var n = Number(quantitat);
    if (n === 1) {
      return '1 moneda';
    }
    return String(quantitat) + ' monedes';
  }

  var label = data.label || data.premi_text || data.premi_valor;
  if (label) {
    return String(label);
  }

  return 'Premi';
}

/**
 * Flux de tirada diària: vídeo en pantalla completa → SweetAlert amb la recompensa.
 */
export function useRouletteDailySpin() {
  const gameStore = useGameStore();
  const nuxtApp = useNuxtApp();
  const $swal = nuxtApp.$swal;
  const $socket = nuxtApp.$socket;
  const { t } = useI18n();

  const isSpinning = ref(false);
  const mostraVideoRuleta = ref(false);
  const videoAcabat = ref(false);
  const resultatPendent = ref(null);

  let socketHandler = null;

  function reiniciarFluxTirada() {
    mostraVideoRuleta.value = false;
    videoAcabat.value = false;
    resultatPendent.value = null;
    isSpinning.value = false;
    gameStore.finalitzarAnimacioRuleta();
  }

  function mostrarAlertaRuleta(titol, text, icona) {
    if (!$swal) {
      return;
    }
    $swal.fire({
      icon: icona || 'success',
      title: titol,
      text: text,
      confirmButtonColor: '#79d45d',
    });
  }

  function intentarMostrarRecompensa() {
    if (!videoAcabat.value || resultatPendent.value === null) {
      return;
    }

    var data = resultatPendent.value;
    resultatPendent.value = null;
    isSpinning.value = false;
    mostraVideoRuleta.value = false;

    if (!data) {
      gameStore.finalitzarAnimacioRuleta();
      return;
    }

    if (data.error) {
      gameStore.finalitzarAnimacioRuleta();
      mostrarAlertaRuleta('Error', data.error, 'error');
      return;
    }

    gameStore.canSpinRoulette = false;
    if (data.ruleta_ultima_tirada !== undefined) {
      gameStore.ruletaUltimaTirada = data.ruleta_ultima_tirada;
    }
    gameStore.obtenirEstatJoc();
    gameStore.finalitzarAnimacioRuleta();

    var premiLabel = formatPremiRuleta(data);
    mostrarAlertaRuleta(
      t('home.roulette_won_title') || 'Enhorabona!',
      t('home.roulette_won_text', { premi: premiLabel }) || ('Has guanyat ' + premiLabel + '! 🎉'),
      'success'
    );
  }

  function gestionarResultatServidor(data) {
    resultatPendent.value = data;
    intentarMostrarRecompensa();
  }

  function onVideoRuletaAcabat() {
    mostraVideoRuleta.value = false;
    videoAcabat.value = true;
    intentarMostrarRecompensa();
  }

  function iniciarTirada() {
    if (!gameStore.canSpinRoulette || isSpinning.value) {
      return false;
    }

    if (!$socket || !$socket.connected) {
      $swal.fire({
        icon: 'warning',
        title: 'Sense connexió',
        text: 'No s\'ha pogut connectar amb el servidor per girar la ruleta.',
        confirmButtonColor: '#79d45d',
      });
      return false;
    }

    isSpinning.value = true;
    videoAcabat.value = false;
    resultatPendent.value = null;
    gameStore.iniciarAnimacioRuleta();
    mostraVideoRuleta.value = true;
    $socket.emit('roulette_spin', {});
    return true;
  }

  function registrarSocket() {
    if (!$socket || socketHandler) {
      return;
    }
    socketHandler = gestionarResultatServidor;
    $socket.on('roulette_result', socketHandler);
  }

  function desregistrarSocket() {
    if ($socket && socketHandler) {
      $socket.off('roulette_result', socketHandler);
      socketHandler = null;
    }
  }

  return {
    videoRuletaUrl: RULETA_VIDEO_URL,
    isSpinning,
    mostraVideoRuleta,
    iniciarTirada,
    onVideoRuletaAcabat,
    reiniciarFluxTirada,
    registrarSocket,
    desregistrarSocket,
  };
}
