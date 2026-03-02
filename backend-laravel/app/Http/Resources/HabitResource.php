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
            'dies_setmana' => $this->parseDiesSetmana($this->dies_setmana),
            'objectiu_vegades' => $this->objectiu_vegades,
            'unitat' => $this->unitat,
            'completat' => $completat,
        ];
    }

    /**
     * Parseja dies_setmana (Postgres boolean array) a array de booleans.
     *
     * @param mixed $diesSetmana
     * @return array<int, bool>
     */
    private function parseDiesSetmana($diesSetmana): array
    {
        if (is_array($diesSetmana)) {
            return array_map(function ($val) {
                return (bool) $val;
            }, $diesSetmana);
        }
        if (! is_string($diesSetmana) || $diesSetmana === '') {
            return [];
        }
        $neteja = trim($diesSetmana, '{}');
        if ($neteja === '') {
            return [];
        }
        $parts = explode(',', $neteja);
        $resultat = [];
        foreach ($parts as $part) {
            $valor = strtolower(trim($part));
            $resultat[] = in_array($valor, ['t', 'true', '1', 'y', 'yes'], true);
        }
        return $resultat;
    }
}
