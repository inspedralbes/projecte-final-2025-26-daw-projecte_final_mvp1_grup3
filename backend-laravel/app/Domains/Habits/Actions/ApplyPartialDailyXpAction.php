<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Support\HabitRewardCalculator;
use App\Domains\Habits\Support\UserLevelCalculator;
use App\Models\Habit;
use App\Models\RegistreActivitat;
use App\Models\User;
use App\Domains\Shared\Services\RedisFeedbackService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Aplica XP proporcional per hàbits incomplets del dia anterior.
 */
class ApplyPartialDailyXpAction
{
    private HabitRewardCalculator $rewardCalculator;

    private UserLevelCalculator $levelCalculator;

    private RedisFeedbackService $feedbackService;

    public function __construct(
        HabitRewardCalculator $rewardCalculator,
        UserLevelCalculator $levelCalculator,
        RedisFeedbackService $feedbackService
    ) {
        $this->rewardCalculator = $rewardCalculator;
        $this->levelCalculator = $levelCalculator;
        $this->feedbackService = $feedbackService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function executar(?Carbon $dataActual = null): int
    {
        if ($dataActual !== null) {
            $avui = $dataActual->copy();
        } else {
            $avui = Carbon::now('Europe/Madrid');
        }
        $avui = $avui->setTimezone('Europe/Madrid')->startOfDay();
        $diaObjectiu = $avui->copy()->subDay();

        $habits = Habit::all();
        $processats = 0;

        foreach ($habits as $habit) {
            $habitId = (int) $habit->id;
            $usuariId = (int) $habit->usuari_id;
            $objectiu = (int) ($habit->objectiu_vegades ?? 0);
            if ($objectiu <= 0 || $usuariId <= 0) {
                continue;
            }

            $jaCompletat = RegistreActivitat::where('habit_id', $habitId)
                ->whereDate('data', $diaObjectiu)
                ->where('acabado', true)
                ->exists();
            if ($jaCompletat) {
                continue;
            }

            $jaXpParcial = RegistreActivitat::where('habit_id', $habitId)
                ->whereDate('data', $diaObjectiu)
                ->where('xp_guanyada', '>', 0)
                ->exists();
            if ($jaXpParcial) {
                continue;
            }

            $progres = RegistreActivitat::where('habit_id', $habitId)
                ->whereBetween('data', [$diaObjectiu->copy()->startOfDay(), $diaObjectiu->copy()->endOfDay()])
                ->sum('valor');
            $progres = (int) $progres;
            if ($progres <= 0) {
                continue;
            }

            $percentatge = min($progres / $objectiu, 1);
            $xpBase = $this->rewardCalculator->calcularXPSegonsDificultat($habit->dificultat);
            $xpGuanyada = (int) floor($xpBase * $percentatge);
            if ($xpGuanyada <= 0) {
                continue;
            }

            DB::transaction(function () use ($usuariId, $habit, $diaObjectiu, $xpGuanyada, &$processats) {
                $usuari = User::where('id', $usuariId)->lockForUpdate()->first();
                if ($usuari === null) {
                    return;
                }

                $nivellData = $this->levelCalculator->aplicarXpINivell($usuari, $xpGuanyada);
                $monedesTotals = (int) $usuari->monedes + $nivellData['bonus_monedes'];

                $usuari->update([
                    'xp_total' => $nivellData['xp_total'],
                    'nivell' => $nivellData['nivell'],
                    'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                    'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                    'monedes' => $monedesTotals,
                ]);

                $habit->registresActivitat()->create([
                    'data' => $diaObjectiu->copy()->endOfDay(),
                    'valor' => 0,
                    'acabado' => false,
                    'xp_guanyada' => $xpGuanyada,
                ]);

                $payload = [
                    'action' => 'PARTIAL_XP',
                    'user_id' => $usuariId,
                    'success' => true,
                    'xp_update' => [
                        'xp_total' => $nivellData['xp_total'],
                        'nivell' => $nivellData['nivell'],
                        'xp_actual_nivel' => $nivellData['xp_actual_nivel'],
                        'xp_objetivo_nivel' => $nivellData['xp_objetivo_nivel'],
                        'monedes' => $monedesTotals,
                    ],
                ];

                if ($nivellData['level_up'] === true) {
                    $payload['level_up'] = [
                        'nivell' => $nivellData['nivell'],
                        'bonus_monedes' => UserLevelCalculator::BONUS_MONEDES_NIVELL,
                        'xp_total' => $nivellData['xp_total'],
                        'monedes' => $monedesTotals,
                    ];
                }

                $this->feedbackService->publicarPayload($payload);
                $processats++;
            });
        }

        return $processats;
    }
}

