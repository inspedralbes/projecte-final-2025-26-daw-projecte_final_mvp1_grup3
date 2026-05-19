<?php

declare(strict_types=1);

namespace App\Domains\Clan\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Sol·licituds pendents d'un clan (només líder).
 */
class GetPendingClanRequestsQuery
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
        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        $lider = $this->guard->validarLider($clan, $userId, 'Només el líder pot veure');
        if ($lider !== null) {
            return ['success' => false, 'error' => $lider['error'], 'status' => $lider['status']];
        }

        $requests = DB::table('clan_requests')
            ->join('usuaris', 'clan_requests.usuari_id', '=', 'usuaris.id')
            ->where('clan_requests.clan_id', $clanId)
            ->where('clan_requests.estat', 'pendent')
            ->select('clan_requests.*', 'usuaris.nom as usuari_nom', 'usuaris.nivell')
            ->get();

        return ['success' => true, 'requests' => $requests];
    }
}
