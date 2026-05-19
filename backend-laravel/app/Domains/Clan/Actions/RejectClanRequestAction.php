<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Rebuig d'una sol·licitud d'entrada (només líder).
 */
class RejectClanRequestAction
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
    public function executar(int $userId, int $requestId): array
    {
        $clanRequest = DB::table('clan_requests')->find($requestId);
        if ($clanRequest === null) {
            return ['success' => false, 'error' => 'Sol·licitud no trobada', 'status' => 404];
        }

        $clan = Clan::find($clanRequest->clan_id);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        $lider = $this->guard->validarLider($clan, $userId, 'Només el líder pot rebutjar');
        if ($lider !== null) {
            return ['success' => false, 'error' => $lider['error'], 'status' => $lider['status']];
        }

        DB::table('clan_requests')
            ->where('id', $requestId)
            ->update(['estat' => 'rebutjat']);

        return ['success' => true];
    }
}
