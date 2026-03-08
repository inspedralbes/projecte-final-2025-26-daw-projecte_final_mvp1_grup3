<?php

namespace App\Console\Commands\QueueHandlers;

//================================ NAMESPACES / IMPORTS ============

use App\Services\AdminActionService;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Handler per processar missatges de la cua admin_queue.
 * Delega a AdminActionService::processarAccio.
 */
class AdminQueueHandler
{
    /**
     * @var AdminActionService
     */
    private AdminActionService $adminActionService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(AdminActionService $adminActionService)
    {
        $this->adminActionService = $adminActionService;
    }

    /**
     * Processa les dades rebudes de la cua.
     *
     * @param  array<string, mixed>  $dades
     */
    public function handle(array $dades): void
    {
        $this->adminActionService->processarAccio($dades);
    }
}
