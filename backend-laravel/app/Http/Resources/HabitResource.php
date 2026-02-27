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
        // A. Obtenir 'completat' mirant si hi ha registre d'avui a REGISTRE_ACTIVITAT
        $completat = false;
        $usuariId = $request->user_id ?? null;
        if ($usuariId) {
            $completat = \App\Models\RegistreActivitat::where('habit_id', $this->id)
                ->whereHas('habit', function ($q) use ($usuariId) {
                    $q->where('usuari_id', $usuariId);
                })
                ->whereDate('data', \Carbon\Carbon::today())
                ->where('acabado', true)
                ->exists();
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
