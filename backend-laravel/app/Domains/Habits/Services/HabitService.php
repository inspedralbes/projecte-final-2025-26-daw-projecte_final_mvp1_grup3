<?php

namespace App\Domains\Habits\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\HabitFacadeService;
use App\Domains\Habits\Queries\HabitLogsQuery;
use App\Domains\Habits\Queries\HabitProgressTodayQuery;
use Carbon\Carbon;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Fachada de compatibilitat per al domini Habits.
 * Delega la lògica a HabitFacadeService i les consultes de lectura.
 */
class HabitService
{
    private HabitFacadeService $facade;

    private HabitProgressTodayQuery $progressTodayQuery;

    private HabitLogsQuery $logsQuery;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(
        HabitFacadeService $facade,
        HabitProgressTodayQuery $progressTodayQuery,
        HabitLogsQuery $logsQuery
    ) {
        $this->facade = $facade;
        $this->progressTodayQuery = $progressTodayQuery;
        $this->logsQuery = $logsQuery;
    }

    /**
     * @param  array<string, mixed>  $dades
     */
    public function processarAccioHabit(array $dades): void
    {
        $this->facade->processarAccioHabit($dades);
    }

    /**
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>
     */
    public function processarConfirmacioHabit(array $dades): array
    {
        return $this->facade->processarConfirmacioHabit($dades);
    }

    public function processarResetRatxesDiaries(?Carbon $dataActual = null): int
    {
        return $this->facade->processarResetRatxesDiaries($dataActual);
    }

    public function processarXpProporcionalDiari(?Carbon $dataActual = null): int
    {
        return $this->facade->processarXpProporcionalDiari($dataActual);
    }

    /**
     * @param  array<int>  $hàbitsSeleccionats
     * @return array<string, mixed>
     */
    public function exportarHabitsDePlantilla(int $usuariId, int $plantillaId, array $hàbitsSeleccionats): array
    {
        return $this->facade->exportarHabitsDePlantilla($usuariId, $plantillaId, $hàbitsSeleccionats);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenirProgresAvui(int $usuariId): array
    {
        return $this->progressTodayQuery->executar($usuariId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenirLogsHistorics(int $usuariId): array
    {
        return $this->logsQuery->executar($usuariId);
    }
}

