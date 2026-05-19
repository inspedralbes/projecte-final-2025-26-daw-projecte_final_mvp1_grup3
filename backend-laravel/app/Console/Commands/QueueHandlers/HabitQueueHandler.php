<?php

namespace App\Console\Commands\QueueHandlers;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Services\HabitService;
use App\Domains\Shared\Services\RedisFeedbackService;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    /**
     * @var RedisFeedbackService
     */
    private RedisFeedbackService $feedbackService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(HabitService $habitService, RedisFeedbackService $feedbackService)
    {
        $this->habitService = $habitService;
        $this->feedbackService = $feedbackService;
    }

    /**
     * Processa les dades rebudes de la cua.
     *
     * @param  array<string, mixed>  $dades
     */
    public function handle(array $dades): void
    {
        try {
            $this->habitService->processarAccioHabit($dades);
        } catch (Throwable $e) {
            Log::error('HabitQueueHandler: error processant cua habits_queue', [
                'error' => $e->getMessage(),
                'class' => $e::class,
            ]);
            $userId = isset($dades['user_id']) ? (int) $dades['user_id'] : 0;
            if ($userId < 1) {
                return;
            }
            $action = isset($dades['action']) ? strtoupper((string) $dades['action']) : 'UNKNOWN';
            $missatgeUsuari = config('app.debug')
                ? $e->getMessage()
                : 'No s\'ha pogut processar l\'acció de l\'hàbit.';
            $this->feedbackService->publicarPayload([
                'action' => $action,
                'user_id' => $userId,
                'success' => false,
                'message' => $missatgeUsuari,
            ]);
        }
    }
}

