<?php

namespace App\Http\Resources\Admin;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Recurs per transformar una plantilla (vista admin) a JSON.
 */
class AdminPlantillaResource extends JsonResource
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Transforma el recurs en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $creador = $this->whenLoaded('creador');

        return [
            'id' => $this->id,
            'titol' => $this->titol,
            'categoria' => $this->categoria,
            'descripcio' => $this->descripcio,
            'es_publica' => (bool) $this->es_publica,
            'creador_id' => $this->creador_id,
            'creador' => $creador ? [
                'id' => $creador->id,
                'nom' => $creador->nom ?? '',
                'email' => $creador->email ?? '',
            ] : null,
        ];
    }
}
