<?php

namespace App\Console\Commands\QueueHandlers;

//================================ NAMESPACES / IMPORTS ============

use App\Services\HabitService;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Handler per processar missatges de la cua habits_queue.
 * Delega a HabitService::processarAccioHabit.
 */
class HabitQueueHandler
{
    /**
     * @var HabitService
     */
    private HabitService $habitService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(HabitService $habitService)
    {
        $this->habitService = $habitService;
    }

    /**
     * Processa les dades rebudes de la cua.
     *
     * @param  array<string, mixed>  $dades
     */
    public function handle(array $dades): void
    {
        $this->habitService->processarAccioHabit($dades);
    }
}
