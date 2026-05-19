<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Friendship;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Rebutja una sol·licitud d'amistat pendent.
 */
class RejectFriendRequestAction
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{success: bool, friendship?: Friendship, error?: string, status?: int}
     */
    public function executar(int $userId, int $friendshipId): array
    {
        $friendship = Friendship::findOrFail($friendshipId);

        if ($friendship->addressee_id !== $userId) {
            return [
                'success' => false,
                'error' => 'No tens permís per rebutjar aquesta sol·licitud',
                'status' => 403,
            ];
        }

        if ($friendship->status !== 'pending') {
            return [
                'success' => false,
                'error' => 'La sol·licitud no està pendent',
                'status' => 400,
            ];
        }

        $friendship->update(['status' => 'rejected']);

        return [
            'success' => true,
            'friendship' => $friendship,
        ];
    }
}
