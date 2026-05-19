<?php

declare(strict_types=1);

namespace App\Domains\Clan\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Missatges del xat de clan (només membres).
 */
class GetClanMessagesQuery
{
    private ClanAccessGuard $guard;

    public function __construct(ClanAccessGuard $guard)
    {
        $this->guard = $guard;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>
     */
    public function executar(int $clanId, int $userId): array
    {
        $membre = $this->guard->validarMembre($clanId, $userId);
        if ($membre !== null) {
            return ['success' => false, 'error' => $membre['error'], 'status' => $membre['status']];
        }

        $messages = DB::table('clan_messages')
            ->join('usuaris', 'clan_messages.usuari_id', '=', 'usuaris.id')
            ->where('clan_messages.clan_id', $clanId)
            ->select(
                'clan_messages.*',
                'usuaris.nom as usuari_nom',
                'usuaris.id as usuari_id',
                'usuaris.monstre_tipus',
                'usuaris.nivell'
            )
            ->orderBy('clan_messages.created_at', 'desc')
            ->paginate(50);

        return ['success' => true, 'messages' => $messages];
    }
}
