<?php

declare(strict_types=1);

namespace App\Domains\Clan\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Clan actiu de l'usuari autenticat.
 */
class GetMyClanQuery
{
    /**
     * @return array{clan: mixed}
     */
    public function executar(int $userId): array
    {
        $member = DB::table('clan_members')
            ->where('usuari_id', $userId)
            ->first();

        if ($member === null) {
            return ['clan' => null];
        }

        $clan = Clan::with(['members.usuari', 'lider'])->find($member->clan_id);

        return ['clan' => $clan];
    }
}
