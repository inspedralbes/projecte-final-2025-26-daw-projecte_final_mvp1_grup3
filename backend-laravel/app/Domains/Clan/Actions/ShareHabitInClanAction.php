<?php

declare(strict_types=1);

namespace App\Domains\Clan\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Clan\Support\ClanAccessGuard;
use App\Models\Habit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comparteix un hàbit propi al xat del clan.
 */
class ShareHabitInClanAction
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
    public function executar(int $userId, int $clanId, int $habitId): array
    {
        $membre = $this->guard->validarMembre($clanId, $userId);
        if ($membre !== null) {
            return ['success' => false, 'error' => $membre['error'], 'status' => $membre['status']];
        }

        $user = User::find($userId);
        if ($user === null) {
            return ['success' => false, 'error' => 'Usuari no trobat', 'status' => 404];
        }

        $teHabit = $user->habits()->where('id', $habitId)->exists();
        if (!$teHabit) {
            return ['success' => false, 'error' => 'Hàbit no trobat', 'status' => 404];
        }

        $habit = Habit::find($habitId);
        if ($habit === null) {
            return ['success' => false, 'error' => 'Hàbit no trobat', 'status' => 404];
        }

        $messageId = DB::table('clan_messages')->insertGetId([
            'clan_id' => $clanId,
            'usuari_id' => $userId,
            'contingut' => 'Hàbit compartit: ' . $habit->titol,
            'habit_id' => $habitId,
            'created_at' => now(),
        ]);

        return ['success' => true, 'id' => $messageId];
    }
}
