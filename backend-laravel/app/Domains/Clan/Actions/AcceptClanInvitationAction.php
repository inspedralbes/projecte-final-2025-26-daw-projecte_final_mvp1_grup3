<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Acceptació d'una invitació al clan.
 */
class AcceptClanInvitationAction
{
    /**
     * @return array<string, mixed>
     */
    public function executar(int $userId, int $invitationId): array
    {
        $invitation = DB::table('clan_requests')->find($invitationId);
        if ($invitation === null || $invitation->usuari_id !== $userId) {
            return ['success' => false, 'error' => 'Invitació no trobada', 'status' => 404];
        }

        $clan = Clan::find($invitation->clan_id);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        if (!$clan->es_public) {
            $pendingRequest = DB::table('clan_requests')
                ->where('clan_id', $invitation->clan_id)
                ->where('usuari_id', $userId)
                ->where('tipus', 'solicitud')
                ->where('estat', 'pendent')
                ->exists();

            if (!$pendingRequest) {
                DB::table('clan_requests')->insert([
                    'clan_id' => $invitation->clan_id,
                    'usuari_id' => $userId,
                    'tipus' => 'solicitud',
                    'estat' => 'pendent',
                    'invitador_id' => $invitation->invitador_id,
                    'created_at' => now(),
                ]);
            }

            DB::table('clan_requests')
                ->where('id', $invitationId)
                ->update(['estat' => 'acceptat']);

            return ['success' => true, 'message' => 'Sol·licitud-enviada'];
        }

        return ['success' => true];
    }
}
