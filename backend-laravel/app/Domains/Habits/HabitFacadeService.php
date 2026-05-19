<?php

declare(strict_types=1);

namespace App\Domains\Habits;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Actions\ApplyPartialDailyXpAction;
use App\Domains\Habits\Actions\CompleteHabitAction;
use App\Domains\Habits\Actions\ExportHabitsFromPlantillaAction;
use App\Domains\Habits\Actions\ProcessHabitQueueAction;
use App\Domains\Habits\Actions\ResetDailyStreaksAction;
use Carbon\Carbon;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Fachada del domini Habits per al worker Redis i consultes programades.
 */
class HabitFacadeService
{
    private ProcessHabitQueueAction $queueAction;

    private CompleteHabitAction $completeAction;

    private ExportHabitsFromPlantillaAction $exportAction;

    private ResetDailyStreaksAction $resetStreaksAction;

    private ApplyPartialDailyXpAction $partialXpAction;

    public function __construct(
        ProcessHabitQueueAction $queueAction,
        CompleteHabitAction $completeAction,
        ExportHabitsFromPlantillaAction $exportAction,
        ResetDailyStreaksAction $resetStreaksAction,
        ApplyPartialDailyXpAction $partialXpAction
    ) {
        $this->queueAction = $queueAction;
        $this->completeAction = $completeAction;
        $this->exportAction = $exportAction;
        $this->resetStreaksAction = $resetStreaksAction;
        $this->partialXpAction = $partialXpAction;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $dades
     */
    public function processarAccioHabit(array $dades): void
    {
        $this->queueAction->executar($dades);
    }

    /**
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>
     */
    public function processarConfirmacioHabit(array $dades): array
    {
        return $this->completeAction->executar($dades);
    }

    public function processarResetRatxesDiaries(?Carbon $dataActual = null): int
    {
        return $this->resetStreaksAction->executar($dataActual);
    }

    public function processarXpProporcionalDiari(?Carbon $dataActual = null): int
    {
        return $this->partialXpAction->executar($dataActual);
    }

    /**
     * @param  array<int>  $habitsSeleccionats
     * @return array<string, mixed>
     */
    public function exportarHabitsDePlantilla(int $usuariId, int $plantillaId, array $habitsSeleccionats): array
    {
        return $this->exportAction->executar($usuariId, $plantillaId, $habitsSeleccionats);
    }
}
