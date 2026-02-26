<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use App\Models\UsuariHabit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs JSON per transformar el model Habit.
 * Format net per consum del frontend (Nuxt 3).
 * El camp 'completat' prové de USUARIS_HABITS.actiu per l'usuari autenticat.
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
        // A. Obtenir 'completat' des de usuaris_habits.actiu per l'usuari autenticat
        $completat = false;
        $usuariId = $request->user_id ?? null;
        if ($usuariId) {
            $pivot = UsuariHabit::where('habit_id', $this->id)
                ->where('usuari_id', $usuariId)
                ->first();
            if ($pivot !== null && isset($pivot->actiu)) {
                $completat = (bool) $pivot->actiu;
            }
        }

        // B. Mapatge dels camps del model
        return [
            'id' => $this->id,
            'usuari_id' => $this->usuari_id,
            'titol' => $this->titol,
            'dificultat' => $this->dificultat,
            'frequencia_tipus' => $this->frequencia_tipus,
            'dies_setmana' => $this->dies_setmana,
            'objectiu_vegades' => $this->objectiu_vegades,
            'completat' => $completat,
        ];
    }
}
