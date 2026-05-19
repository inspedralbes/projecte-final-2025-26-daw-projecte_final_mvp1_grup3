<?php

declare(strict_types=1);

namespace App\Http\Resources\Shop;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Resource per a un item de l'inventari de l'usuari.
 */
class UsuariItemResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = null;
        if ($this->relationLoaded('item') && $this->item !== null) {
            $item = (new BotigaItemResource($this->item))->resolve();
        }

        return [
            'id' => $this->id,
            'usuari_id' => $this->usuari_id,
            'item_id' => $this->item_id,
            'comprat_at' => $this->comprat_at,
            'equipat' => (bool) $this->equipat,
            'consumit_at' => $this->consumit_at,
            'item' => $item,
        ];
    }
}
