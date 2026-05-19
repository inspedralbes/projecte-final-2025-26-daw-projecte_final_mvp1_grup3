<?php

declare(strict_types=1);

namespace App\Domains\Habits\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\UsuariHabit;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comprova si un usuari pot accedir a un hàbit.
 */
class HabitAccessGuard
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna true si l'usuari té accés a l'hàbit (propietari o assignat).
     */
    public function usuariTeAccesHabit(int $habitId, int $usuariId): bool
    {
        $habit = Habit::find($habitId);
        if ($habit && (int) $habit->usuari_id === $usuariId) {
            return true;
        }

        return UsuariHabit::where('habit_id', $habitId)
            ->where('usuari_id', $usuariId)
            ->exists();
    }
}
