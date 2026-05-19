<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Sol·licitud d'entrada a un clan privat.
 */
class RequestJoinClanAction
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

        if ($clan->es_public) {
            return ['success' => false, 'error' => 'Clan públic, uneix-te directament', 'status' => 400];
        }

        $exists = DB::table('clan_members')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $userId)
            ->exists();

        if ($exists) {
            return ['success' => false, 'error' => 'Ja eres membre', 'status' => 400];
        }

        $pendingRequest = DB::table('clan_requests')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $userId)
            ->where('estat', 'pendent')
            ->exists();

        if ($pendingRequest) {
            return ['success' => false, 'error' => 'Ja has enviat una sol·licitud', 'status' => 400];
        }

        DB::table('clan_requests')->insert([
            'clan_id' => $clanId,
            'usuari_id' => $userId,
            'tipus' => 'solicitud',
            'estat' => 'pendent',
            'created_at' => now(),
        ]);

        return ['success' => true];
    }
}
