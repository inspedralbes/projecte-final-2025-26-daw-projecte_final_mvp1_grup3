<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurso JSON per transformar el model Habit.
 * Format net per consum del frontend (Nuxt 3).
 */
class HabitResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma l'hàbit a un array associatiu per la resposta JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuari_id' => $this->usuari_id,
            'plantilla_id' => $this->plantilla_id,
            'titol' => $this->titol,
            'dificultat' => $this->dificultat,
            'frequencia_tipus' => $this->frequencia_tipus,
            'dies_setmana' => $this->dies_setmana,
            'objectiu_vegades' => $this->objectiu_vegades,
        ];
    }
}
