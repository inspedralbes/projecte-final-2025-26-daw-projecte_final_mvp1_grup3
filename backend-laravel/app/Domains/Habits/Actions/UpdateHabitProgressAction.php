<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Support\HabitAccessGuard;
use App\Domains\Habits\Support\HabitProgressReader;
use App\Domains\Habits\Support\HabitRewardCalculator;
use App\Domains\Habits\Support\UserLevelCalculator;
use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\RegistreActivitat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Incrementa o decrementa el progrés diari d'un hàbit.
 */
class UpdateHabitProgressAction
{
    private HabitAccessGuard $accessGuard;

    private HabitProgressReader $progressReader;

    private HabitRewardCalculator $rewardCalculator;

    private UserLevelCalculator $levelCalculator;

    public function __construct(
        HabitAccessGuard $accessGuard,
        HabitProgressReader $progressReader,
        HabitRewardCalculator $rewardCalculator,
        UserLevelCalculator $levelCalculator
    ) {
        $this->accessGuard = $accessGuard;
        $this->progressReader = $progressReader;
        $this->rewardCalculator = $rewardCalculator;
        $this->levelCalculator = $levelCalculator;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>|null
     */
    public function processarProgresHabit(int $habitId, int $usuariId, int $delta): ?array
    {
        $habit = Habit::find($habitId);
        if (!$habit || !$this->accessGuard->usuariTeAccesHabit($habitId, $usuariId)) {
            return null;
        }

        $ara = Carbon::now();
        $progresActual = $this->progressReader->obtenirProgresDiari($habitId, $ara);
        $objectiu = (int) ($habit->objectiu_vegades ?? 1);
        if ($objectiu <= 0) {
            $objectiu = 1;
        }

        if ($delta < 0 && $progresActual <= 0) {
            return [
                'progress' => 0,
                'completed_today' => $this->progressReader->habitCompletatAvui($habitId, $ara),
            ];
        }

        if ($delta < 0 && ($progresActual + $delta) < 0) {
            $delta = -$progresActual;
        }

        $desferCompletacio = false;
        if ($delta < 0 && $this->progressReader->habitCompletatAvui($habitId, $ara) && ($progresActual + $delta) < $objectiu) {
            $desferCompletacio = true;
        }

        if ($desferCompletacio) {
            return $this->desferCompletacioIRestarProgres($habit, $usuariId, $ara, $progresActual, $delta);
        }

        RegistreActivitat::create([
            'habit_id' => $habitId,
            'data' => $ara,
            'valor' => $delta,
            'acabado' => false,
            'xp_guanyada' => 0,
        ]);

        return [
            'progress' => (int) ($progresActual + $delta),
            'completed_today' => $this->progressReader->habitCompletatAvui($habitId, $ara),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function desferCompletacioIRestarProgres(
        Habit $habit,
        int $usuariId,
        Carbon $ara,
        int $progresActual,
        int $delta
    ): array {
        $habitId = (int) $habit->id;
        $xpARestar = $this->rewardCalculator->calcularXPSegonsDificultat($habit->dificultat);
        $monedesARestar = $this->rewardCalculator->calcularMonedesSegonsDificultat($habit->dificultat);

        DB::transaction(function () use ($habitId, $usuariId, $ara, $delta, &$xpARestar, $monedesARestar) {
            $registreCompletat = RegistreActivitat::where('habit_id', $habitId)
                ->whereDate('data', $ara)->where('acabado', true)->first();

            if ($registreCompletat !== null) {
                $xpReal = (int) ($registreCompletat->xp_guanyada ?? 0);
                if ($xpReal > 0) {
                    $xpARestar = $xpReal;
                }
                $registreCompletat->delete();
            }

            $usuari = User::where('id', $usuariId)->lockForUpdate()->first();
            if ($usuari !== null) {
                $nouXpTotal = max(0, (int) $usuari->xp_total - $xpARestar);
                $novesMonedes = (int) $usuari->monedes - $monedesARestar;
                $nivellData = $this->levelCalculator->recalcularNivellDesDeXpTotal($nouXpTotal);
                $usuari->update([
                    'xp_total' => $nouXpTotal,
                    'nivell' => $nivellData['nivell'],
                    'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                    'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                    'monedes' => $novesMonedes,
                ]);
            }

            RegistreActivitat::create([
                'habit_id' => $habitId,
                'data' => $ara,
                'valor' => $delta,
                'acabado' => false,
                'xp_guanyada' => 0,
            ]);
        });

        $nouProgres = $progresActual + $delta;
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        $ratxaActual = $ratxa ? (int) $ratxa->ratxa_actual : 0;
        $ratxaMaxima = $ratxa ? (int) $ratxa->ratxa_maxima : 0;
        $usuari = User::find($usuariId);
        $xpTotal = $usuari ? (int) $usuari->xp_total : 0;
        $nivellData = $this->levelCalculator->recalcularNivellDesDeXpTotal($xpTotal);

        return [
            'progress' => (int) $nouProgres,
            'completed_today' => false,
            'xp_update' => array_merge(
                $nivellData,
                [
                    'xp_total' => $xpTotal,
                    'ratxa_actual' => $ratxaActual,
                    'ratxa_maxima' => $ratxaMaxima,
                    'monedes' => $usuari ? (int) $usuari->monedes : 0,
                ]
            ),
        ];
    }
}
