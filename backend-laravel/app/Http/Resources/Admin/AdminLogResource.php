<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar un log d'administració a JSON.
 */
class AdminLogResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $adminNom = 'Admin';
        if ($this->administrador !== null && $this->administrador->nom !== null) {
            $adminNom = $this->administrador->nom;
        }

        return [
            'id' => $this->id,
            'created_at' => $this->created_at?->toIso8601String(),
            'administrador_nom' => $adminNom,
            'accio' => $this->accio,
            'detall' => $this->detall,
            'abans' => $this->abans,
            'despres' => $this->despres,
            'ip' => $this->ip,
        ];
    }
}
