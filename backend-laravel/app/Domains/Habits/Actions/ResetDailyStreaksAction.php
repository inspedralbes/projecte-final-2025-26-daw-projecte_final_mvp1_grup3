<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Ratxa;
use App\Services\RedisFeedbackService;
use Carbon\Carbon;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Reseteja ratxes per inactivitat diària (timezone Europe/Madrid).
 */
class ResetDailyStreaksAction
{
    private RedisFeedbackService $feedbackService;

    public function __construct(RedisFeedbackService $feedbackService)
    {
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
        $ahir = $avui->copy()->subDay();

        $ratxes = Ratxa::where('ratxa_actual', '>', 0)->get();
        $resetejades = 0;

        foreach ($ratxes as $ratxa) {
            if ($ratxa->ultima_data === null) {
                continue;
            }

            $ultimaData = Carbon::parse($ratxa->ultima_data, 'Europe/Madrid')->startOfDay();

            if ($ultimaData->lt($ahir)) {
                $ratxaAnterior = (int) $ratxa->ratxa_actual;
                $ratxa->update([
                    'ratxa_actual' => 0,
                    'ultima_data' => null,
                ]);

                $this->feedbackService->publicarPayload([
                    'event' => 'streak_broken',
                    'action' => 'STREAK_BROKEN',
                    'user_id' => (int) $ratxa->usuari_id,
                    'ratxa_anterior' => $ratxaAnterior,
                    'ratxa_actual' => 0,
                    'data' => $avui->toDateString(),
                    'message' => "Tu racha de {$ratxaAnterior} días se ha roto!",
                ]);

                $resetejades++;
            }
        }

        return $resetejades;
    }
}
