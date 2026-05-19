<?php

declare(strict_types=1);

namespace App\Domains\Clan\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Membres d'un clan amb dades d'usuari.
 */
class GetClanMembersQuery
{
    /**
     * @return array<string, mixed>
     */
    public function executar(int $clanId): array
    {
        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        $members = DB::table('clan_members')
            ->join('usuaris', 'clan_members.usuari_id', '=', 'usuaris.id')
            ->where('clan_members.clan_id', $clanId)
            ->select(
                'clan_members.rol',
                'clan_members.data_unio',
                'usuaris.id as usuari_id',
                'usuaris.nom',
                'usuaris.nivell',
                'usuaris.monstre_tipus'
            )
            ->get();

        return ['success' => true, 'members' => $members];
    }
}
