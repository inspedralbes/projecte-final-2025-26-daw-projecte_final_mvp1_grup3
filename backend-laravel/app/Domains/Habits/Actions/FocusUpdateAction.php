<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Support\HabitAccessGuard;
use App\Domains\Habits\Support\HabitProgressReader;
use App\Models\Habit;
use App\Models\RegistreActivitat;
use Carbon\Carbon;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Processa una sessió de focus i completa l'hàbit si s'arriba al llindar.
 */
class FocusUpdateAction
{
    private HabitAccessGuard $accessGuard;

    private HabitProgressReader $progressReader;

    private CompleteHabitAction $completeHabitAction;

    public function __construct(
        HabitAccessGuard $accessGuard,
        HabitProgressReader $progressReader,
        CompleteHabitAction $completeHabitAction
    ) {
        $this->accessGuard = $accessGuard;
        $this->progressReader = $progressReader;
        $this->completeHabitAction = $completeHabitAction;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>
     */
    public function executar(array $dades): array
    {
        $habitId = isset($dades['habit_id']) ? (int) $dades['habit_id'] : 0;
        $usuariId = isset($dades['user_id']) ? (int) $dades['user_id'] : 0;
        $focusMode = isset($dades['mode']) ? strtolower((string) $dades['mode']) : null;
        $minutes = isset($dades['minutes']) ? (int) $dades['minutes'] : 0;
        $event = isset($dades['event']) ? strtolower((string) $dades['event']) : 'update';
        if (isset($dades['data']) && $dades['data'] !== null) {
            $marcaTemps = Carbon::parse((string) $dades['data']);
        } else {
            $marcaTemps = Carbon::now();
        }

        if ($habitId <= 0 || $usuariId <= 0) {
            return ['success' => false, 'message' => 'Dades de focus invàlides.'];
        }

        $habit = Habit::find($habitId);
        if (!$habit || !$this->accessGuard->usuariTeAccesHabit($habitId, $usuariId)) {
            return ['success' => false, 'message' => 'No autoritzat per aquest hàbit.'];
        }

        if (!in_array($focusMode, ['25_5', '50_10'], true)) {
            $focusMode = null;
        }

        if ($minutes < 0) {
            $minutes = 0;
        }

        $registreFocus = RegistreActivitat::create([
            'habit_id' => $habitId,
            'data' => $marcaTemps,
            'valor' => 0,
            'acabado' => false,
            'xp_guanyada' => 0,
            'focus_minutes' => $minutes,
            'focus_mode' => $focusMode,
            'focus_session' => true,
        ]);

        $avui = $marcaTemps->copy()->startOfDay();
        $totalFocusMinutes = (int) RegistreActivitat::where('habit_id', $habitId)
            ->whereDate('data', $avui)
            ->sum('focus_minutes');

        $completedToday = $this->progressReader->habitCompletatAvui($habitId, $avui);
        $objectiu = (int) ($habit->objectiu_vegades ?? 1);
        $unitat = strtolower((string) ($habit->unitat ?? 'vegades'));

        $llindarMinutes = $objectiu;
        if ($unitat !== 'minuts') {
            $llindarMinutes = $objectiu;
        }
        if ($llindarMinutes <= 0) {
            $llindarMinutes = 1;
        }

        $xpUpdate = null;
        $levelUp = null;
        if ($completedToday === false && $totalFocusMinutes >= $llindarMinutes) {
            $resultatComplete = $this->completeHabitAction->executar([
                'habit_id' => $habitId,
                'user_id' => $usuariId,
                'data' => $marcaTemps->toDateTimeString(),
            ]);
            if (($resultatComplete['success'] ?? false) === true) {
                $completedToday = true;
                $registreFocus->acabado = true;
                $registreFocus->save();
                if (isset($resultatComplete['xp_update']) && is_array($resultatComplete['xp_update'])) {
                    $xpUpdate = $resultatComplete['xp_update'];
                }
                if (isset($resultatComplete['level_up'])) {
                    $levelUp = $resultatComplete['level_up'];
                }
            }
        }

        $resultat = [
            'success' => true,
            'event' => $event,
            'progress' => $totalFocusMinutes,
            'completed_today' => $completedToday,
            'xp_update' => $xpUpdate,
        ];

        if ($levelUp !== null) {
            $resultat['level_up'] = $levelUp;
        }

        return $resultat;
    }
}
