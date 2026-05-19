<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Unió directa a un clan públic.
 */
class JoinPublicClanAction
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
    public function executar(int $userId, int $clanId): array
    {
        $nivell = $this->guard->validarNivellMinim($userId);
        if ($nivell !== null) {
            return ['success' => false, 'error' => $nivell['error'], 'status' => $nivell['status']];
        }

        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        if (!$clan->es_public) {
            return ['success' => false, 'error' => 'Clan privat, has de sol·licitar entrada', 'status' => 400];
        }

        $memberCount = DB::table('clan_members')->where('clan_id', $clanId)->count();
        if ($memberCount >= $clan->max_membres) {
            return ['success' => false, 'error' => 'Clan ple', 'status' => 400];
        }

        $exists = DB::table('clan_members')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $userId)
            ->exists();

        if ($exists) {
            return ['success' => false, 'error' => 'Ja eres membre', 'status' => 400];
        }

        DB::table('clan_members')->insert([
            'clan_id' => $clanId,
            'usuari_id' => $userId,
            'rol' => 'miembro',
            'data_unio' => now(),
        ]);

        return ['success' => true];
    }
}
