/**
 * Modul JavaScript ES5: useClanFeedback.
 * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md
 * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.
 */

import { useClanStore } from '~/stores/useClanStore.js';

/**
 * Registra feedback de clans.
 */
export function registrarClanFeedback(socket) {
  if (!socket || socket._loopyClanFeedbackRegistrat) {
    return;
  }
  socket._loopyClanFeedbackRegistrat = true;

  socket.on('clan_member_joined', function (data) {
    console.log('[Socket] Clan member joined:', data);
    var clanStore = useClanStore();
    if (clanStore && data && data.clan_id) {
      clanStore.fetchMembers(data.clan_id);
    }
  });

  socket.on('clan_request_received', function (data) {
    console.log('[Socket] Nova sol·licitud de clan rebuda:', data);
    var clanStore = useClanStore();
    if (clanStore && data && data.clan_id) {
      clanStore.fetchPendingRequests(data.clan_id);
    }
  });

  socket.on('clan_request_accepted', function (data) {
    console.log('[Socket] Clan request accepted:', data);
    var clanStore = useClanStore();
    if (clanStore && data && data.clan_id) {
      clanStore.fetchMembers(data.clan_id);
    }
  });

  socket.on('clan_member_left', function (data) {
    console.log('[Socket] Clan member left:', data);
    var clanStore = useClanStore();
    if (clanStore && data && data.clan_id) {
      clanStore.fetchMembers(data.clan_id);
    }
  });
}
