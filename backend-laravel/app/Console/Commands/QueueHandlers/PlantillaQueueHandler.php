<?php

namespace App\Console\Commands\QueueHandlers;

//================================ NAMESPACES / IMPORTS ============

use App\Services\PlantillaService;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Handler per processar missatges de la cua plantilles_queue.
 * Delega a PlantillaService::processarAccioPlantilla.
 */
class PlantillaQueueHandler
{
    /**
     * @var PlantillaService
     */
    private PlantillaService $plantillaService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(PlantillaService $plantillaService)
    {
        $this->plantillaService = $plantillaService;
    }

    /**
     * Processa les dades rebudes de la cua.
     *
     * @param  array<string, mixed>  $dades
     */
    public function handle(array $dades): void
    {
        $this->plantillaService->processarAccioPlantilla($dades);
    }
}
