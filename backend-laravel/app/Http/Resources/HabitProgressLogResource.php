<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar els logs històrics d'hàbits a JSON.
 */
class HabitProgressLogResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        $items = $this->resource;
        if (!is_array($items) && !($items instanceof Collection)) {
            return [];
        }
        $collection = is_array($items) ? collect($items) : $items;
        $resultat = [];
        foreach ($collection as $item) {
            $resultat[] = [
                'dia' => $item['dia'] ?? $item->dia ?? null,
                'habit_id' => (int) ($item['habit_id'] ?? $item->habit_id ?? 0),
                'titol' => $item['titol'] ?? $item->titol ?? '',
                'unitat' => $item['unitat'] ?? $item->unitat ?? '',
                'icona' => $item['icona'] ?? $item->icona ?? null,
                'color' => $item['color'] ?? $item->color ?? null,
                'objectiu_vegades' => (int) ($item['objectiu_vegades'] ?? $item->objectiu_vegades ?? 0),
                'progreso_diario' => (int) ($item['progreso_diario'] ?? $item->progreso_diario ?? 0),
                'completado' => (bool) ($item['completado'] ?? $item->completado ?? false),
                'xp_ganada' => (int) ($item['xp_ganada'] ?? $item->xp_ganada ?? 0),
                'monedes_ganadas' => (int) ($item['monedes_ganadas'] ?? $item->monedes_ganadas ?? 0),
            ];
        }

        return $resultat;
    }
}
