<?php

declare(strict_types=1);

namespace App\Domains\Social\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Friendship;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Llista amistats acceptades paginades.
 */
class ListFriendsQuery
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function executar(int $userId): LengthAwarePaginator
    {
        return Friendship::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                ->orWhere('addressee_id', $userId);
        })
            ->where('status', 'accepted')
            ->with(['requester:id,nom,nivell,xp_total,monstre_tipus', 'addressee:id,nom,nivell,xp_total,monstre_tipus'])
            ->orderBy('created_at', 'desc')
            ->paginate(8)
            ->through(function ($friendship) use ($userId) {
                $friend = $friendship->requester_id === $userId
                    ? $friendship->addressee
                    : $friendship->requester;

                return [
                    'id' => $friendship->id,
                    'friend' => $friend,
                    'created_at' => $friendship->created_at,
                ];
            });
    }
}
