<?php

declare(strict_types=1);

namespace App\Http\Resources\Shop;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Resource per a un item del catàleg de la botiga.
 */
class BotigaItemResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'descripcio' => $this->descripcio,
            'preu' => (int) $this->preu,
            'tipus' => $this->tipus,
            'imatge' => $this->imatge,
            'metadata' => $this->metadata ?? [],
            'actiu' => (bool) $this->actiu,
        ];
    }
}
