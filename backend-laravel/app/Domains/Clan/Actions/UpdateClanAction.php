<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Actualització de dades del clan (només líder).
 */
class UpdateClanAction
{
    private ClanAccessGuard $guard;

    public function __construct(ClanAccessGuard $guard)
    {
        $this->guard = $guard;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param array<string, mixed> $dades
     * @return array<string, mixed>
     */
    public function executar(int $userId, int $clanId, array $dades): array
    {
        $clan = Clan::find($clanId);
        if ($clan === null) {
            return ['success' => false, 'error' => 'Clan no trobat', 'status' => 404];
        }

        $lider = $this->guard->validarLider($clan, $userId, 'Només el líder pot modificar');
        if ($lider !== null) {
            return ['success' => false, 'error' => $lider['error'], 'status' => $lider['status']];
        }

        $clan->update($dades);

        return ['success' => true, 'clan' => $clan];
    }
}
