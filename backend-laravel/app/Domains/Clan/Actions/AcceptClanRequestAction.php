<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Acceptació d'una sol·licitud d'entrada (només líder).
 */
class AcceptClanRequestAction
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

        $lider = $this->guard->validarLider($clan, $userId, 'Només el líder pot acceptar');
        if ($lider !== null) {
            return ['success' => false, 'error' => $lider['error'], 'status' => $lider['status']];
        }

        if ($clanRequest->estat !== 'pendent') {
            return ['success' => false, 'error' => 'Sol·licitud ja processada', 'status' => 400];
        }

        $memberCount = DB::table('clan_members')->where('clan_id', $clanRequest->clan_id)->count();
        if ($memberCount >= $clan->max_membres) {
            return ['success' => false, 'error' => 'Clan ple', 'status' => 400];
        }

        DB::table('clan_members')->insert([
            'clan_id' => $clanRequest->clan_id,
            'usuari_id' => $clanRequest->usuari_id,
            'rol' => 'miembro',
            'data_unio' => now(),
        ]);

        DB::table('clan_requests')
            ->where('id', $requestId)
            ->update(['estat' => 'acceptat']);

        return ['success' => true];
    }
}
