/**
 * Modul JavaScript ES5: useRouletteDailySpin.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { ref } from 'vue';
import { useGameStore } from '~/stores/gameStore.js';
import { useSocketUiCallbacks } from '~/stores/useSocketUiCallbacks.js';
import { useSocketBridge } from '~/composables/socket/useSocketBridge.js';

export const RULETA_VIDEO_URL = '/video/ruleta-diaria.mp4';

function formatPremiRuleta(data) {
  if (!data || typeof data !== 'object') {
    return { label: 'Premi', type: null, amount: 0 };
  }

  var tipus = data.type || '';
  var quantitat = data.amount;

  if (tipus === 'xp' && quantitat !== undefined && quantitat !== null) {
    return { label: String(quantitat) + ' XP', type: 'xp', amount: Number(quantitat) };
  }

  if (tipus === 'coins' && quantitat !== undefined && quantitat !== null) {
    var n = Number(quantitat);
    var txt = n === 1 ? '1 moneda' : String(quantitat) + ' monedes';
    return { label: txt, type: 'coins', amount: n };
  }

  var label = data.label || data.premi_text || data.premi_valor;
  if (label) {
    return { label: String(label), type: null, amount: 0 };
  }

  return { label: 'Premi', type: null, amount: 0 };
}

/**
 * Flux de tirada diària: vídeo en pantalla completa → modal custom amb la recompensa.
 */
export function useRouletteDailySpin() {
  const gameStore = useGameStore();
  const socketBridge = useSocketBridge();
  const $socket = socketBridge.obtenirSocket();
  const { t } = useI18n();

  const isSpinning = ref(false);
  const mostraVideoRuleta = ref(false);
  const videoAcabat = ref(false);
  const resultatPendent = ref(null);

  const modalObert = ref(false);
  const modalTitol = ref('');
  const modalText = ref('');
  const modalTipus = ref('success');
  const modalPremiType = ref(null);
  const modalPremiAmount = ref(0);

  let socketHandler = null;

  function reiniciarFluxTirada() {
    mostraVideoRuleta.value = false;
    videoAcabat.value = false;
    resultatPendent.value = null;
    isSpinning.value = false;
    gameStore.finalitzarAnimacioRuleta();
  }

  function tancarModal() {
    modalObert.value = false;
  }

  function mostrarModal(titol, text, tipus, premiType, premiAmount) {
    modalTitol.value = titol;
    modalText.value = text;
    modalTipus.value = tipus || 'success';
    modalPremiType.value = premiType || null;
    modalPremiAmount.value = premiAmount || 0;
    modalObert.value = true;
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
      mostrarModal('Error', data.error, 'error', null, 0);
      return;
    }

    gameStore.canSpinRoulette = false;
    if (data.ruleta_ultima_tirada !== undefined) {
      gameStore.ruletaUltimaTirada = data.ruleta_ultima_tirada;
    }
    gameStore.obtenirEstatJoc();
    gameStore.finalitzarAnimacioRuleta();

    var premi = formatPremiRuleta(data);
    mostrarModal(
      t('home.roulette_won_title') || 'Enhorabona!',
      t('home.roulette_won_text', { premi: premi.label }) || ('Has guanyat ' + premi.label + '!'),
      'success',
      premi.type,
      premi.amount
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
      mostrarModal(
        'Sense connexió',
        'No s\'ha pogut connectar amb el servidor per girar la ruleta.',
        'error',
        null,
        0
      );
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
    if (socketHandler) {
      return;
    }
    socketHandler = gestionarResultatServidor;
    useSocketUiCallbacks().registrarRouletteResult(socketHandler);
  }

  function desregistrarSocket() {
    if (socketHandler) {
      useSocketUiCallbacks().eliminarRouletteResult(socketHandler);
      socketHandler = null;
    }
  }

  return {
    videoRuletaUrl: RULETA_VIDEO_URL,
    isSpinning,
    mostraVideoRuleta,
    modalObert,
    modalTitol,
    modalText,
    modalTipus,
    modalPremiType,
    modalPremiAmount,
    tancarModal,
    iniciarTirada,
    onVideoRuletaAcabat,
    reiniciarFluxTirada,
    registrarSocket,
    desregistrarSocket,
  };
}
