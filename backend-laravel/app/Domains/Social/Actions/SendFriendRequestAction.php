<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Friendship;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Envia una sol·licitud d'amistat.
 */
class SendFriendRequestAction
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{success: bool, friendship?: Friendship, error?: string, status?: int}
     */
    public function executar(int $requesterId, int $addresseeId): array
    {
        if ($requesterId === $addresseeId) {
            return [
                'success' => false,
                'error' => "No pots enviar-te sol·licitud d'amistat a tu mateix",
                'status' => 400,
            ];
        }

        $existing = Friendship::where(function ($query) use ($requesterId, $addresseeId) {
            $query->where(function ($q) use ($requesterId, $addresseeId) {
                $q->where('requester_id', $requesterId)->where('addressee_id', $addresseeId);
            })->orWhere(function ($q) use ($requesterId, $addresseeId) {
                $q->where('requester_id', $addresseeId)->where('addressee_id', $requesterId);
            });
        })->first();

        if ($existing !== null) {
            return [
                'success' => false,
                'error' => "Ja existeix una relació d'amistat",
                'status' => 409,
            ];
        }

        $friendship = Friendship::create([
            'requester_id' => $requesterId,
            'addressee_id' => $addresseeId,
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'friendship' => $friendship,
        ];
    }
}
