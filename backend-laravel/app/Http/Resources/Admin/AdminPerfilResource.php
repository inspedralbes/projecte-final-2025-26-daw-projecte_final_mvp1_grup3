<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar el perfil de l'administrador a JSON.
 */
class AdminPerfilResource extends JsonResource
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
            'nom' => $this->nom ?? '',
            'email' => $this->email ?? '',
            'created_at' => $this->data_creacio?->toIso8601String(),
        ];
    }
}
