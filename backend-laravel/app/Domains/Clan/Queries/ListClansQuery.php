<?php

declare(strict_types=1);

namespace App\Domains\Clan\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Llistat paginat de clans (requereix nivell 5).
 */
class ListClansQuery
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
    public function executar(int $userId): array
    {
        $nivell = $this->guard->validarNivellMinim($userId);
        if ($nivell !== null) {
            return ['success' => false, 'error' => $nivell['error'], 'status' => $nivell['status']];
        }

        $clans = Clan::withCount('members')
            ->orderBy('nom')
            ->paginate(8);

        return ['success' => true, 'clans' => $clans];
    }
}
