<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Clan;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Creació d'un clan nou; l'usuari queda com a líder.
 */
class CreateClanAction
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
    public function executar(int $userId, array $dades): array
    {
        $nivell = $this->guard->validarNivellMinim($userId);
        if ($nivell !== null) {
            return ['success' => false, 'error' => $nivell['error'], 'status' => $nivell['status']];
        }

        $clan = Clan::create([
            'nom' => $dades['nom'],
            'categoria_id' => $dades['categoria_id'] ?? null,
            'max_membres' => $dades['max_membres'],
            'es_public' => $dades['es_public'] ?? true,
            'lider_id' => $userId,
        ]);

        DB::table('clan_members')->insert([
            'clan_id' => $clan->id,
            'usuari_id' => $userId,
            'rol' => 'lider',
            'data_unio' => now(),
        ]);

        return ['success' => true, 'clan' => $clan];
    }
}
