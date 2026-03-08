<?php

namespace App\Http\Resources;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar el progrés diari d'hàbits a JSON.
 */
class HabitProgressTodayResource extends JsonResource
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
                'habit_id' => $item['habit_id'] ?? $item->habit_id ?? 0,
                'progress' => (int) ($item['progress'] ?? $item->progress ?? 0),
                'completed_today' => (bool) ($item['completed_today'] ?? $item->completed_today ?? false),
                'objectiu_vegades' => (int) ($item['objectiu_vegades'] ?? $item->objectiu_vegades ?? 0),
                'titol' => $item['titol'] ?? $item->titol ?? '',
                'unitat' => $item['unitat'] ?? $item->unitat ?? '',
                'icona' => $item['icona'] ?? $item->icona ?? null,
                'color' => $item['color'] ?? $item->color ?? null,
            ];
        }

        return $resultat;
    }
}
