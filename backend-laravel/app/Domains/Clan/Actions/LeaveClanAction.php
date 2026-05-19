<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Sortida d'un membre; si és líder, transfereix o elimina el clan.
 */
class LeaveClanAction
{
    /**
     * @return array<string, mixed>
     */
    public function executar(int $userId, int $clanId): array
    {
        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        if ($clan->lider_id === $userId) {
            $nextMember = DB::table('clan_members')
                ->where('clan_id', $clanId)
                ->where('usuari_id', '!=', $userId)
                ->orderBy('data_unio', 'asc')
                ->first();

            if ($nextMember !== null) {
                DB::table('clan_members')
                    ->where('clan_id', $clanId)
                    ->where('usuari_id', $nextMember->usuari_id)
                    ->update(['rol' => 'lider']);

                $clan->lider_id = $nextMember->usuari_id;
                $clan->save();
            } else {
                $clan->delete();

                return ['success' => true];
            }
        }

        DB::table('clan_members')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $userId)
            ->delete();

        return ['success' => true];
    }
}
