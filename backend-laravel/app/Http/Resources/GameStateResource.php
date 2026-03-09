<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar l'estat de gamificació a JSON.
 */
class GameStateResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = (array) $this->resource;

        return [
            'usuari_id' => $data['usuari_id'] ?? 0,
            'xp_total' => (int) ($data['xp_total'] ?? 0),
            'nivell' => (int) ($data['nivell'] ?? 1),
            'xp_actual_nivel' => (int) ($data['xp_actual_nivel'] ?? 0),
            'xp_objetivo_nivel' => (int) ($data['xp_objetivo_nivel'] ?? 1000),
            'ratxa_actual' => (int) ($data['ratxa_actual'] ?? 0),
            'ratxa_maxima' => (int) ($data['ratxa_maxima'] ?? 0),
            'monedes' => (int) ($data['monedes'] ?? 0),
            'can_spin_roulette' => (bool) ($data['can_spin_roulette'] ?? true),
            'ruleta_ultima_tirada' => $data['ruleta_ultima_tirada'] ?? null,
            'missio_diaria' => $data['missio_diaria'] ?? null,
            'missio_completada' => (bool) ($data['missio_completada'] ?? false),
            'missio_progres' => (int) ($data['missio_progres'] ?? 0),
            'missio_objectiu' => (int) ($data['missio_objectiu'] ?? 1),
        ];
    }
}
