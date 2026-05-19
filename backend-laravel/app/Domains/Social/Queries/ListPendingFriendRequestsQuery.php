<?php

declare(strict_types=1);

namespace App\Domains\Social\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Friendship;
use Illuminate\Support\Collection;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Sol·licituds d'amistat pendents rebudes per l'usuari.
 */
class ListPendingFriendRequestsQuery
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function executar(int $userId): Collection
    {
        return Friendship::where('addressee_id', $userId)
            ->where('status', 'pending')
            ->with('requester:id,nom,nivell,xp_total,monstre_tipus')
            ->get()
            ->map(function ($friendship) {
                return [
                    'id' => $friendship->id,
                    'requester' => $friendship->requester,
                    'created_at' => $friendship->created_at,
                ];
            });
    }
}
