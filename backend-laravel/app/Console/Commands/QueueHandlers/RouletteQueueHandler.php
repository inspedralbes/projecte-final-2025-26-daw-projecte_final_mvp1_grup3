<?php

namespace App\Console\Commands\QueueHandlers;

//================================ NAMESPACES / IMPORTS ============

use App\Services\RouletteService;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Handler per processar missatges de la cua roulette_queue.
 * Delega a RouletteService::processarTirada.
 */
class RouletteQueueHandler
{
    /**
     * @var RouletteService
     */
    private RouletteService $rouletteService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(RouletteService $rouletteService)
    {
        $this->rouletteService = $rouletteService;
    }

    /**
     * Processa les dades rebudes de la cua.
     *
     * @param  array<string, mixed>  $dades
     */
    public function handle(array $dades): void
    {
        $this->rouletteService->processarTirada($dades);
    }
}
