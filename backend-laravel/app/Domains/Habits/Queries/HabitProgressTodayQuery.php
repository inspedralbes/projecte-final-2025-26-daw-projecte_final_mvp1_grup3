<?php

declare(strict_types=1);

namespace App\Domains\Habits\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\RegistreActivitat;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Consulta el progrés diari dels hàbits actius d'un usuari.
 */
class HabitProgressTodayQuery
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function executar(int $usuariId): array
    {
        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $diaIndex = (int) now()->dayOfWeekIso;
        $habits = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->where(function ($q) use ($diaIndex) {
                $q->whereNull('dies_setmana')
                    ->orWhereRaw('dies_setmana[' . $diaIndex . '] = true');
            })
            ->get(['id', 'objectiu_vegades', 'titol', 'unitat', 'dificultat', 'icona', 'color']);

        $habitIds = $habits->pluck('id')->toArray();
        $avui = Carbon::today();

        if (empty($habitIds)) {
            return [];
        }

        $progres = DB::table('registre_activitat')
            ->select('habit_id', DB::raw('COALESCE(SUM(valor), 0) as progress'))
            ->whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->groupBy('habit_id')
            ->get()
            ->keyBy('habit_id');

        $completats = RegistreActivitat::whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->where('acabado', true)
            ->pluck('habit_id')
            ->toArray();

        $resultat = [];
        foreach ($habits as $habit) {
            $progress = 0;
            if (isset($progres[$habit->id])) {
                $progress = (int) $progres[$habit->id]->progress;
            }
            $resultat[] = [
                'habit_id' => $habit->id,
                'progress' => $progress,
                'completed_today' => in_array($habit->id, $completats, true),
                'objectiu_vegades' => (int) $habit->objectiu_vegades,
                'titol' => $habit->titol,
                'unitat' => $habit->unitat,
                'icona' => $habit->icona,
                'color' => $habit->color,
            ];
        }

        return $resultat;
    }
}
