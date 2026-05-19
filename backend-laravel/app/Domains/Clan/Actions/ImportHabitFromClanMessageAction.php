<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Habit;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Importa un hàbit compartit des d'un missatge del clan.
 */
class ImportHabitFromClanMessageAction
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
            ->whereNotNull('habit_id')
            ->first();

        if ($message === null) {
            return ['success' => false, 'error' => 'Missatge no trobat', 'status' => 404];
        }

        $originalHabit = Habit::find($message->habit_id);
        if ($originalHabit === null) {
            return ['success' => false, 'error' => 'Hàbit original no trobat', 'status' => 404];
        }

        $newHabit = $originalHabit->replicate();
        $newHabit->usuari_id = $userId;
        $newHabit->save();

        return ['success' => true, 'habit_id' => $newHabit->id];
    }
}
