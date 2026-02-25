<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs JSON per transformar el model Habit.
 * Format net per consum del frontend (Nuxt 3).
 */
class HabitResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma l'hàbit a un array associatiu per la resposta JSON.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // A. Mapatge directe dels camps del model a l'estructura de resposta
        return [
            'id' => $this->id,
            'usuari_id' => $this->usuari_id,
            'titol' => $this->titol,
            'dificultat' => $this->dificultat,
            'frequencia_tipus' => $this->frequencia_tipus,
            'dies_setmana' => $this->dies_setmana,
            'objectiu_vegades' => $this->objectiu_vegades,
        ];
    }
}
