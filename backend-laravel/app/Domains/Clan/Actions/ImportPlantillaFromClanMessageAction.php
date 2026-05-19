<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Plantilla;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Importa una plantilla compartida des d'un missatge del clan.
 */
class ImportPlantillaFromClanMessageAction
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
    public function executar(int $userId, int $clanId, int $messageId): array
    {
        $membre = $this->guard->validarMembre($clanId, $userId);
        if ($membre !== null) {
            return ['success' => false, 'error' => $membre['error'], 'status' => $membre['status']];
        }

        $message = DB::table('clan_messages')
            ->where('clan_id', $clanId)
            ->where('id', $messageId)
            ->whereNotNull('plantilla_id')
            ->first();

        if ($message === null) {
            return ['success' => false, 'error' => 'Missatge no trobat', 'status' => 404];
        }

        $originalPlantilla = Plantilla::find($message->plantilla_id);
        if ($originalPlantilla === null) {
            return ['success' => false, 'error' => 'Plantilla original no trobada', 'status' => 404];
        }

        $newPlantilla = $originalPlantilla->replicate();
        $newPlantilla->save();

        return ['success' => true, 'plantilla_id' => $newPlantilla->id];
    }
}
