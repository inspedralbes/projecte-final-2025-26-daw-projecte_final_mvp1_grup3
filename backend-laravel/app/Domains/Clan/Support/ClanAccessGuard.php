<?php

declare(strict_types=1);

namespace App\Domains\Clan\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Clan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Validacions de nivell, pertinença i rol de líder als clans.
 */
class ClanAccessGuard
{
    public const NIVELL_MINIM = 5;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{error: string, status: int}|null
     */
    public function validarNivellMinim(int $userId): ?array
    {
        $user = User::find($userId);
        if ($user === null || $user->nivell < self::NIVELL_MINIM) {
            return ['error' => 'Nivell 5 requerit', 'status' => 403];
        }

        return null;
    }

    public function esMembre(int $clanId, int $userId): bool
    {
        return DB::table('clan_members')
            ->where('clan_id', $clanId)
            ->where('usuari_id', $userId)
            ->exists();
    }

    /**
     * @return array{error: string, status: int}|null
     */
    public function validarMembre(int $clanId, int $userId): ?array
    {
        if (!$this->esMembre($clanId, $userId)) {
            return ['error' => 'No eres membre', 'status' => 403];
        }

        return null;
    }

    /**
     * @return array{error: string, status: int}|null
     */
    public function validarLider(Clan $clan, int $userId, string $missatgeError): ?array
    {
        if ($clan->lider_id !== $userId) {
            return ['error' => $missatgeError, 'status' => 403];
        }

        return null;
    }
}
