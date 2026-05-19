<?php

declare(strict_types=1);

namespace App\Http\Resources\Shop;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Resource per a la resposta GET /api/shop.
 */
class ShopIndexResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dades = is_array($this->resource) ? $this->resource : [];

        $items = $dades['items'] ?? [];
        $inventari = $dades['inventari'] ?? [];

        return [
            'items' => BotigaItemResource::collection($items)->resolve($request),
            'inventari' => UsuariItemResource::collection($inventari)->resolve($request),
            'monedes' => (int) ($dades['monedes'] ?? 0),
        ];
    }
}
