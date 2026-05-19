<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar un hàbit (vista admin) a JSON.
 */
class AdminHabitResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $usuari = $this->whenLoaded('usuari');
        $plantilla = $this->whenLoaded('plantilla');

        return [
            'id' => $this->id,
            'usuari_id' => $this->usuari_id,
            'plantilla_id' => $this->plantilla_id,
            'categoria_id' => $this->categoria_id !== null ? (int) $this->categoria_id : null,
            'titol' => $this->titol,
            'dificultat' => $this->dificultat,
            'frequencia_tipus' => $this->frequencia_tipus,
            'dies_setmana' => Habit::diesSetmanaPgACsv($this->dies_setmana),
            'moment_dia' => $this->moment_dia,
            'icona' => $this->icona,
            'color' => $this->color,
            'objectiu_vegades' => (int) $this->objectiu_vegades,
            'unitat' => $this->unitat,
            'usuari' => $usuari ? [
                'id' => $usuari->id,
                'nom' => $usuari->nom ?? '',
                'email' => $usuari->email ?? '',
            ] : null,
            'plantilla' => $plantilla ? [
                'id' => $plantilla->id,
                'titol' => $plantilla->titol ?? '',
                'categoria' => $plantilla->categoria ?? '',
            ] : null,
        ];
    }
}
