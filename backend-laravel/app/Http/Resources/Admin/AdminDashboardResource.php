<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar les dades del dashboard admin a JSON.
 */
class AdminDashboardResource extends JsonResource
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
            'success' => true,
            'data' => [
                'totalUsuaris' => (int) ($data['totalUsuaris'] ?? 0),
                'connectats' => (int) ($data['connectats'] ?? 0),
                'prohibits' => (int) ($data['prohibits'] ?? 0),
                'logrosActius' => (int) ($data['logrosActius'] ?? 0),
                'totalHabits' => (int) ($data['totalHabits'] ?? 0),
                'top_5_plantilles' => $data['top_5_plantilles'] ?? [],
                'top_5_habits' => $data['top_5_habits'] ?? [],
                'ultims_10_logs' => $data['ultims_10_logs'] ?? [],
            ],
        ];
    }
}
