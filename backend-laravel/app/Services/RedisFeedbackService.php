<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei d'emissió de feedback a Redis.
 * Publica el resultat d'una acció d'hàbits al canal 'feedback_channel'
 * perquè el backend Node.js el rebi i l'emeti al frontend.
 */
class RedisFeedbackService
{
    /**
     * Nom del canal Redis per al feedback.
     */
    private const CANAL_FEEDBACK = 'feedback_channel';

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Publica un payload genèric de feedback al canal Redis.
     * El payload es serialitza en JSON per ser consumit pel subscriptor Node.js.
     *
     * @param  array<string, mixed>  $payload
     */
    public function publicarPayload(array $payload): void
    {
        Redis::publish(self::CANAL_FEEDBACK, json_encode($payload));
    }
}
