<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Plantilla;
use App\Models\User;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comparteix una plantilla pròpia al xat del clan.
 */
class SharePlantillaInClanAction
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
    public function executar(int $userId, int $clanId, int $plantillaId): array
    {
        $membre = $this->guard->validarMembre($clanId, $userId);
        if ($membre !== null) {
            return ['success' => false, 'error' => $membre['error'], 'status' => $membre['status']];
        }

        $user = User::find($userId);
        if ($user === null) {
            return ['success' => false, 'error' => 'Usuari no trobat', 'status' => 404];
        }

        $tePlantilla = $user->plantilles()->where('id', $plantillaId)->exists();
        if (!$tePlantilla) {
            return ['success' => false, 'error' => 'Plantilla no trobada', 'status' => 404];
        }

        $plantilla = Plantilla::find($plantillaId);
        if ($plantilla === null) {
            return ['success' => false, 'error' => 'Plantilla no trobada', 'status' => 404];
        }

        $messageId = DB::table('clan_messages')->insertGetId([
            'clan_id' => $clanId,
            'usuari_id' => $userId,
            'contingut' => 'Plantilla compartida: ' . $plantilla->nom,
            'plantilla_id' => $plantillaId,
            'created_at' => now(),
        ]);

        return ['success' => true, 'id' => $messageId];
    }
}
