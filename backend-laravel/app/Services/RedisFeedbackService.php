<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei d'emissió de feedback a Redis.
 * Publica els valors actualitzats (XP, ratxa actual, ratxa màxima) al canal
 * 'feedback_channel' perquè el backend Node.js els rebi i els emeti al frontend.
 */
class RedisFeedbackService
{
    /**
     * Nom del canal Redis per al feedback.
     */
    private const CANAL_FEEDBACK = 'feedback_channel';

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Publica el feedback amb els nous valors de gamificació al canal Redis.
     * El payload es serialitza en JSON per ser consumit pel subscriptor Node.js.
     *
     * @param  int  $usuariId  Identificador de l'usuari
     * @param  int  $xpTotal  XP total acumulat
     * @param  int  $ratxaActual  Ratxa actual (dies consecutius)
     * @param  int  $ratxaMaxima  Rècord de ratxa
     */
    public function publicarFeedback(
        int $usuariId,
        int $xpTotal,
        int $ratxaActual,
        int $ratxaMaxima
    ): void {
        $payload = [
            'usuari_id' => $usuariId,
            'xp_total' => $xpTotal,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
        ];

        Redis::publish(self::CANAL_FEEDBACK, json_encode($payload));
    }
}
