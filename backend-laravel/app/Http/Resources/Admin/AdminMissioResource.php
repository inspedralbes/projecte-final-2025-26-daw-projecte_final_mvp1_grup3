<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar una missió diària (vista admin) a JSON.
 */
class AdminMissioResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titol' => $this->titol ?? '',
            'tipus_comprovacio' => $this->tipus_comprovacio ?? null,
            'parametres' => $this->parametres ?? [],
        ];
    }
}
