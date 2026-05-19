<?php

declare(strict_types=1);

namespace App\Domains\Habits\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Domains\Shared\Services\RedisFeedbackService;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Munta i publica el payload de feedback per accions d'hàbits a la cua.
 */
class HabitQueueFeedbackAssembler
{
    private RedisFeedbackService $feedbackService;

    public function __construct(RedisFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $estat
     */
    public function publicar(array $estat): void
    {
        $accio = (string) $estat['accio'];
        $usuariId = (int) $estat['usuari_id'];

        $payload = [
            'action' => $accio,
            'user_id' => $usuariId,
            'success' => (bool) $estat['success'],
        ];

        if ($accio === 'EXPORT_HABITS' && isset($estat['exported_habits'])) {
            $payload['exported_habits'] = $estat['exported_habits'];
        } elseif (isset($estat['habit_model']) && $estat['habit_model'] instanceof Habit) {
            $payload['habit'] = $estat['habit_model']->toArray();
        }

        if (isset($estat['xp_update'])) {
            $payload['xp_update'] = $estat['xp_update'];
        }
        if (isset($estat['progress'])) {
            $payload['progress'] = $estat['progress'];
        }
        if (isset($estat['completed_today'])) {
            $payload['completed_today'] = $estat['completed_today'];
        }
        if (isset($estat['message'])) {
            $payload['message'] = $estat['message'];
        }
        if (isset($estat['mission_completed'])) {
            $missionPayload = $estat['mission_completed'];
            if (isset($estat['xp_update'])) {
                $missionPayload['xp_update'] = $estat['xp_update'];
            }
            $payload['mission_completed'] = $missionPayload;
        }
        if (isset($estat['level_up'])) {
            $payload['level_up'] = $estat['level_up'];
        }

        $this->feedbackService->publicarPayload($payload);
    }
}

