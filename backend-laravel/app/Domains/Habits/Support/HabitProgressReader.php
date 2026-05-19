<?php

declare(strict_types=1);

namespace App\Domains\Habits\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\RegistreActivitat;
use Carbon\Carbon;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Llegeix el progrés diari i l'estat de completat d'un hàbit.
 */
class HabitProgressReader
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté el progrés diari d'un hàbit (sumatori de valor).
     */
    public function obtenirProgresDiari(int $habitId, Carbon $dataActivitat): int
    {
        $inici = $dataActivitat->copy()->startOfDay();
        $fi = $dataActivitat->copy()->endOfDay();

        $sum = RegistreActivitat::where('habit_id', $habitId)
            ->whereBetween('data', [$inici, $fi])
            ->sum('valor');

        return (int) $sum;
    }

    /**
     * Retorna si l'hàbit està completat avui.
     */
    public function habitCompletatAvui(int $habitId, Carbon $dataActivitat): bool
    {
        return RegistreActivitat::where('habit_id', $habitId)
            ->whereDate('data', $dataActivitat)
            ->where('acabado', true)
            ->exists();
    }
}
