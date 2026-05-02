<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar les dades de rankings admin a JSON.
 */
class AdminRankingResource extends JsonResource
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
            'periodo' => $data['periodo'] ?? 'total',
            'ranking_plantilles' => $data['ranking_plantilles'] ?? [],
            'ranking_habits' => $data['ranking_habits'] ?? [],
        ];
    }
}
