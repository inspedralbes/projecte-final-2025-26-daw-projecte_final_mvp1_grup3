<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Invitació d'un usuari al clan (membre existent convida).
 */
class InviteToClanAction
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
    public function executar(int $userId, int $clanId, int $targetUserId): array
    {
        $nivell = $this->guard->validarNivellMinim($userId);
        if ($nivell !== null) {
            return ['success' => false, 'error' => $nivell['error'], 'status' => $nivell['status']];
        }

        $membre = $this->guard->validarMembre($clanId, $userId);
        if ($membre !== null) {
            return ['success' => false, 'error' => $membre['error'], 'status' => $membre['status']];
        }

        $targetUser = User::find($targetUserId);
        if ($targetUser === null || $targetUser->nivell < ClanAccessGuard::NIVELL_MINIM) {
            return ['success' => false, 'error' => 'Usuari necessita nivell 5', 'status' => 400];
        }

        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        $alreadyMember = DB::table('clan_members')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $targetUserId)
            ->exists();

        if ($alreadyMember) {
            return ['success' => false, 'error' => 'Ja es membre', 'status' => 400];
        }

        DB::table('clan_requests')->insert([
            'clan_id' => $clanId,
            'usuari_id' => $targetUserId,
            'tipus' => 'invitacion',
            'estat' => 'pendent',
            'invitador_id' => $userId,
            'created_at' => now(),
        ]);

        if ($clan->es_public) {
            DB::table('clan_members')->insert([
                'clan_id' => $clanId,
                'usuari_id' => $targetUserId,
                'rol' => 'miembro',
                'data_unio' => now(),
            ]);
        }

        return ['success' => true];
    }
}
