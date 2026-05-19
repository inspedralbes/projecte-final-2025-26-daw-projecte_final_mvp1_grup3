<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Expulsió d'un membre del clan (només líder).
 */
class RemoveClanMemberAction
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
    public function executar(int $userId, int $clanId, int $memberId): array
    {
        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        $lider = $this->guard->validarLider($clan, $userId, 'Només el líder pot expulsar');
        if ($lider !== null) {
            return ['success' => false, 'error' => $lider['error'], 'status' => $lider['status']];
        }

        if ($clan->lider_id === $memberId) {
            return ['success' => false, 'error' => 'No pots expulsar el líder', 'status' => 400];
        }

        DB::table('clan_members')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $memberId)
            ->delete();

        return ['success' => true];
    }
}
