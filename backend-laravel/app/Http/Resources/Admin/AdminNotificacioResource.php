<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar una notificació admin a JSON.
 */
class AdminNotificacioResource extends JsonResource
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
            'administrador_id' => $this->administrador_id,
            'tipus' => $this->tipus,
            'titol' => $this->titol,
            'descripcio' => $this->descripcio,
            'data' => $this->data?->toIso8601String(),
            'llegida' => (bool) $this->llegida,
            'metadata' => $this->metadata ?? [],
        ];
    }
}
