<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar les dades de la home de l'usuari a JSON.
 * Wrapper que usa GameStateResource, HabitResource, HabitProgressTodayResource, LogroResource.
 */
class UserHomeResource extends JsonResource
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

        $gameState = $data['game_state'] ?? [];
        $habitProgress = $data['habit_progress'] ?? [];

        return [
            'game_state' => (new GameStateResource($gameState))->toArray($request),
            'habits' => $data['habits'] ?? [],
            'habit_progress' => (new HabitProgressTodayResource($habitProgress))->toArray($request),
            'logros' => $data['logros'] ?? [],
        ];
    }
}
