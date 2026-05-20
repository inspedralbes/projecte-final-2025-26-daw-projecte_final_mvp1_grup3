<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Friendship;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Elimina una amistat acceptada entre dos usuaris.
 */
class RemoveFriendAction
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{success: bool, friendship?: Friendship, error?: string, status?: int}
     */
    public function executar(int $userId, int $friendshipId): array
    {
        $friendship = Friendship::findOrFail($friendshipId);

        $esParticipant = $friendship->requester_id === $userId || $friendship->addressee_id === $userId;
        if (!$esParticipant) {
            return [
                'success' => false,
                'error' => 'No tens permís per eliminar aquesta amistat',
                'status' => 403,
            ];
        }

        if ($friendship->status !== 'accepted') {
            return [
                'success' => false,
                'error' => 'Aquesta relació no és una amistat acceptada',
                'status' => 400,
            ];
        }

        $friendship->delete();

        return [
            'success' => true,
            'friendship' => $friendship,
        ];
    }
}
