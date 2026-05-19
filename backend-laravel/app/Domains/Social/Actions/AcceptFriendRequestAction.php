<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Friendship;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Accepta una sol·licitud d'amistat pendent.
 */
class AcceptFriendRequestAction
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
                'error' => 'No tens permís per acceptar aquesta sol·licitud',
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

        $friendship->update(['status' => 'accepted']);
        $this->comprovarLogroAmistats($userId);

        return [
            'success' => true,
            'friendship' => $friendship,
        ];
    }

    //================================ RUTES / LOGICA PRIVADA ========

    private function comprovarLogroAmistats(int $userId): void
    {
        $friendCount = Friendship::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                ->orWhere('addressee_id', $userId);
        })
            ->where('status', 'accepted')
            ->count();

        if ($friendCount === 5) {
            DB::table('usuaris_logros')->insertOrIgnore([
                'usuari_id' => $userId,
                'logro_id' => 1,
                'data_obtencio' => now()->toDateString(),
            ]);
        }
    }
}
